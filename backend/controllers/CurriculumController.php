<?php

namespace backend\controllers;

use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\archive\EAcademicRecord;
use common\models\attendance\EAttendanceSettingBorder;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\GradeType;
use common\models\curriculum\LessonPair;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\SubjectGroup;
use common\models\employee\EEmployeeMeta;
use common\models\infrastructure\EAuditorium;
use common\models\performance\EPerformance;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\curriculum\ESubject;
use common\models\curriculum\ECurriculumSubjectBlock;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\MarkingSystem;
use common\models\student\EGroup;

use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\ExamFinish;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\TrainingType;
use common\models\system\job\ContractListFileGenerateJob;
use common\models\system\job\StudentContingentFileGenerateJob;
use common\models\system\job\SubjectListFileGenerateJob;
use common\models\system\job\SubjectScheduleListFileGenerateJob;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Yii;
use yii\base\ErrorException;
use yii\queue\redis\Queue;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\base\Exception;
use yii\helpers\BaseFileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


class CurriculumController extends BackendController
{
    public $activeMenu = 'curriculum';

    public function actionCurriculum()
    {
        $searchModel = new ECurriculum();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['_department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if ($attribute = $this->get('attribute')) {
            if ($model = ECurriculum::findOne(['id' => $this->get('id')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save()) {
                    if ($model->id) {
                        $this->addSuccess(__('Item [{id}] of [{specialty}] is enabled', ['id' => $model->id]), true, false);
                    } else {
                        $this->addSuccess(__('Item [{id}] of [{specialty}] is disabled', ['id' => $model->id]), true, false);
                    }
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [];
                }
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    public function actionCurriculumEdit($id = false)
    {
        if ($id) {
            $model = $this->findCurriculumModel($id);
            $model->autumn_start_date = Yii::$app->formatter->asDate($model->autumn_start_date, 'php:Y-m-d');
            $model->autumn_end_date = Yii::$app->formatter->asDate($model->autumn_end_date, 'php:Y-m-d');
            $model->spring_start_date = Yii::$app->formatter->asDate($model->spring_start_date, 'php:Y-m-d');
            $model->spring_end_date = Yii::$app->formatter->asDate($model->spring_end_date, 'php:Y-m-d');

            if ($this->get('delete')) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(
                            __('Curriculum `{name}` deleted successfully.', [
                                'name' => $model->id
                            ])
                        );
                        /*if(count(Semester::getSemesterByCurriculum($model->id)) == 0) {
                            ECurriculumWeek::deleteAll(
                                ['AND',
                                    ['_curriculum' => $model->id]
                                ]
                            );
                        }*/
                        return $this->redirect(['curriculum']);
                    }
                } catch (Exception $e) {
                    if ($e->getCode() == 23503) {
                        $this->addError(__('Could not delete related data'));
                    } else {
                        $this->addError($e->getMessage());
                    }

                    //$this->addError($e->getMessage());
                }
                return $this->redirect(['curriculum-edit', 'id' => $model->id]);
            }
        } else {
            $model = new ECurriculum();
        }

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $model->scenario = ECurriculum::SCENARIO_DEAN_CREATE;
                $model->_department = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } else {
            $model->scenario = ECurriculum::SCENARIO_CREATE;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $specialty = ESpecialty::findOne(['id' => $model->_specialty_id]);
            $model->_education_type = $specialty->_education_type;
            $education_year1 = "";
            $education_year2 = "";
            $education_year3 = "";
            $education_year4 = "";
            if ($model->isNewRecord) {
                if ($model->save()) {
                    $code = Semester::SEMESTER_FIRST;
                    $level = Course::COURSE_FIRST;
                    $education_year = $model->_education_year;
                    $autumn_start_date = Yii::$app->formatter->asDate($model->autumn_start_date, 'php:m-d');
                    $autumn_end_date = Yii::$app->formatter->asDate($model->autumn_end_date, 'php:m-d');
                    $spring_start_date = Yii::$app->formatter->asDate($model->spring_start_date, 'php:m-d');
                    $spring_end_date = Yii::$app->formatter->asDate($model->spring_end_date, 'php:m-d');

                    $education_year1 = Yii::$app->formatter->asDate($model->autumn_start_date, 'php:Y');
                    $education_year2 = Yii::$app->formatter->asDate($model->autumn_end_date, 'php:Y');
                    $education_year3 = Yii::$app->formatter->asDate($model->spring_start_date, 'php:Y');
                    $education_year4 = Yii::$app->formatter->asDate($model->spring_end_date, 'php:Y');

                    for ($i = 1; $i <= $model->semester_count; $i++) {
                        $semester = new Semester();
                        $semester->_curriculum = $model->id;
                        $semester->name = $i . '-semestr';
                        $semester->code = $code;
                        $semester->_level = $level;
                        $_education_year = EducationYear::findOne($education_year);
                        if ($_education_year === null) {
                            $_education_year = new EducationYear();
                            $_education_year->code = $education_year;
                            $_education_year->name = $education_year . '-' . ($education_year + 1);
                            $_education_year->save(false);
                        }
                        $semester->_education_year = $_education_year->code;
                        if ($semester->code % 2 == 1) {
                            $semester->start_date = date("Y-m-d", strtotime("$education_year1-$autumn_start_date"));
                            //            $education_year++;
                            $semester->end_date = date("Y-m-d", strtotime("$education_year2-$autumn_end_date"));
                            $education_year1++;
                            $education_year2++;
                        } else {
                            $semester->start_date = date("Y-m-d", strtotime("$education_year3-$spring_start_date"));
                            $semester->end_date = date("Y-m-d", strtotime("$education_year4-$spring_end_date"));
                            $education_year3++;
                            $education_year4++;
                            $education_year++;
                        }
                        $semester->position = $i;
                        $semester->save(false);
                        $code++;
                        if ($i % 2 == 0) {
                            $level++;
                        }
                    }
                }
            } else {
                $model->save();
            }
            if ($id) {
                $this->addSuccess(
                    __('Curriculum `{name}` updated successfully.', [
                        'name' => $model->id
                    ]));
            } else {
                $this->addSuccess(
                    __('Curriculum `{name}` created successfully.', [
                        'name' => $model->id
                    ]));
            }

            return $this->redirect(['curriculum']);

        }

        return $this->render('/curriculum/curriculum-edit', [
            'model' => $model,
        ]);
    }

    public function actionEducationYear()
    {
        $model = new EducationYear();
        $model->scenario = EducationYear::SCENARIO_CREATE;
        $searchModel = new EducationYear();

        if ($code = $this->get('code')) {
            if ($model = EducationYear::findOne(['code' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Education year [{code}] is deleted successfully', ['code' => $model->code]));

                            return $this->redirect(['curriculum/education-year']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['curriculum/education-year', 'code' => $model->code]);
                }
            } else {
                return $this->redirect(['curriculum/education-year']);
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($code) {
                $this->addSuccess(__('Education year [{code}] updated successfully', ['code' => $model->code]));
            } else {
                $this->addSuccess(__('Education year [{code}] created successfully', ['code' => $model->code]));
            }
            // $model = new EducationYear();
        }
        return $this->render('education-year', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionSemester($id = false)
    {
        $searchModel = new Semester();

        $dataProvider = $searchModel->search($this->getFilterParams());
        //$dataProvider->query->andFilterWhere(['_education_type' => $model->_education_type]);

        if ($id) {
            $curriculum = $this->findCurriculumModel($id);
            //	$dataProvider = $searchModel->search(Yii::$app->request->get());

            //	$searchModel->_curriculum = $curriculum->id;

            $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum->id]);
        }
        /*else{
            $dataProvider = $searchModel->search($this->getFilterParams());

        }*/
        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = ECurriculum::find()
                    ->select(['id'])
                    ->where(['active' => ECurriculum::STATUS_ENABLE, '_department' => $faculty])
                    ->column();
                $dataProvider->query->andFilterWhere(['_curriculum' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if ($attribute = $this->get('attribute')) {
            if ($model = Semester::findOne(['id' => $this->get('semester')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save()) {
                    if ($model->id) {
                        $this->addSuccess(__('Item [{id}] of [{semester}] is enabled', ['id' => $model->id]), true, false);
                    } else {
                        $this->addSuccess(__('Item [{id}] of [{semester}] is disabled', ['id' => $model->id]), true, false);
                    }
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [];
                }
            }
        }
        return $this->render('semester', [
            // 'dataProvider' => $searchModel->search($this->getFilterParams()),
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'curriculum' => @$curriculum,
            'faculty' => @$faculty,
        ]);
    }

    public function actionSemesterEdit($id = false)
    {
        $faculty = "";
        if ($id) {
            $model = $this->findSemesterModel($id);
            $model->scenario = Semester::SCENARIO_CREATE;
            if ($model->accepted) {
                $this->addInfo(
                    __('Semester `{name}` accepted. You can`t update and delete information', [
                        'name' => $model->name
                    ])
                );
                return $this->redirect(['semester', 'id' => $model->_curriculum]);
            }
            if ($model->curriculum->accepted) {
                $this->addInfo(
                    __('Curriculum `{name}` accepted. You can`t update and delete information', [
                        'name' => $model->_curriculum,
                    ])
                );
                return $this->redirect(['semester', 'id' => $model->_curriculum]);
            }
            $model->start_date = Yii::$app->formatter->asDate($model->start_date, 'php:Y-m-d');
            $model->end_date = Yii::$app->formatter->asDate($model->end_date, 'php:Y-m-d');

            if ($this->get('delete')) {

                try {
                    if (count(ECurriculumSubject::getSubjectByCurriculumSemester($model->_curriculum, $model->code)) > 0) {
                        $this->addError(__('Could not delete related data'));
                    } else {
                        if ($model->delete()) {
                            $this->addSuccess(
                                __('Semester `{name}` deleted successfully.', [
                                    'name' => $model->name
                                ])
                            );
                        }
                    }
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                }
                return $this->redirect(['semester', 'id' => $model->_curriculum]);
            }
        } else {
            $model = new Semester();
            $model->scenario = Semester::SCENARIO_CREATE;


        }
        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            //if ($model->isNewRecord)
                $model->name = \common\models\system\classifier\Semester::findOne(['code' => $model->code])->name;
            $model->save();
            if ($id) {
                $this->addSuccess(
                    __('Semester `{name}` updated successfully.', [
                        'name' => $model->name
                    ]));

                $student_meta = EStudentMeta::findOne(['_semestr' => $model->code, '_curriculum' => $model->_curriculum]);
                if (($student_meta = EStudentMeta::find()->where(['_semestr' => $model->code, '_curriculum' => $model->_curriculum])->one()) !== null) {
                    EStudentMeta::updateAll([
                        '_curriculum' => $model->_curriculum,
                        '_education_year' => $model->_education_year,
                        '_specialty_id' => $model->curriculum->_specialty_id,
                    ],
                        [
                            '_semestr' => $model->code,
                            '_curriculum' => $model->_curriculum
                        ]);

                    $studentIds = EStudentMeta::find()
                        ->select(['_student'])
                        ->where([
                            '_curriculum' => $model->_curriculum,
                            '_education_year' => $model->_education_year,
                            '_specialty_id' => $model->curriculum->_specialty_id,
                        ])
                        ->column();

                    if (count($studentIds)) {
                        EStudent::setAllShouldBeSynced($studentIds);
                    }
                }
            } else {
                $this->addSuccess(
                    __('Semester `{name}` created successfully.', [
                        'name' => $model->name
                    ]));
            }


            //return $this->redirect(Yii::$app->request->referrer);
            return $this->redirect(['semester', 'id' => $model->_curriculum]);

        }

        return $this->renderView([
            'model' => $model,
            'faculty' => $faculty,
        ]);
    }

    public function actionSubject()
    {
        $this->activeMenu = 'subjects';
        $model = new ESubject();
        $model->scenario = ESubject::SCENARIO_CREATE;

        $searchModel = new ESubject();

        if ($attribute = $this->get('attribute')) {
            if ($model = ESubject::findOne(['id' => $this->get('code')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save()) {
                    if ($model->id) {
                        $this->addSuccess(__('Item [{code}] of subject is enabled', ['code' => $model->id]), true, false);
                    } else {
                        $this->addSuccess(__('Item [{code}] of subject is disabled', ['code' => $model->id]), true, false);
                    }
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [];
                }
            }
        }
        if ($code = $this->get('code')) {
            if ($model = ESubject::findOne(['id' => $code])) {

                if ($this->get('delete')) {
                    if ($issue = $model->anyIssueWithDelete()) {
                        $this->addError($issue);
                        return $this->redirect(['curriculum/subject', 'id' => $model->code]);
                    } else {
                        $this->addSuccess(__('Item [{code}] of subject is deleted successfully', ['code' => $model->name]));
                        return $this->redirect(['curriculum/subject']);
                    }
                }
            } else {
                return $this->redirect(['curriculum/subject', 'code' => $model->id]);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($code) {
                $this->addSuccess(__('Subject  [{code}] updated successfully', ['code' => $model->name]));
            } else {
                $this->addSuccess(__('Subject  [{code}] created successfully', ['code' => $model->name]));
            }

            return $this->redirect(['curriculum/subject']);
        }
        if ($this->get('download')) {
            $query = $searchModel->search($this->getFilterParams(), false);

            $countQuery = clone $query;
            $limit = 2000;
            if ($countQuery->count() <= $limit) {
                $fileName = ESubject::generateDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {
                /**
                 * @var $queue Queue
                 * @var $queue1 Queue
                 */
                $queue1 = Yii::$app->queue;
                $queue = Yii::$app->queueFile;
                $prefix = $queue1->channel;
                if ($queue1->redis->llen("$prefix.waiting") == 0) {
                    $queue = $queue1;
                }

                if ($queue
                    ->ttr(900)
                    ->push(
                        new SubjectListFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['curriculum/subject', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Fanlar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar sahifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['curriculum/subject']);
            }
        }
        $dataProvider = $searchModel->search($this->getFilterParams());

        return $this->render('subject-list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionCurriculumBlock($id = false)
    {
        $model = new ECurriculumSubjectBlock();
        $model->scenario = ECurriculumSubjectBlock::SCENARIO_CREATE;
        $searchModel = new ECurriculumSubjectBlock();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($id) {
            $curriculum = $this->findCurriculumModel($id);
            $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum->id]);
        }

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = ECurriculum::find()
                    ->select(['id'])
                    ->where(['active' => ECurriculum::STATUS_ENABLE, '_department' => $faculty])
                    ->column();
                $dataProvider->query->andFilterWhere(['_curriculum' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $subject_block = array();

        if ($code = $this->get('code')) {
            if ($model = ECurriculumSubjectBlock::findOne(['id' => $code])) {
                $_curriculum = $model->_curriculum;
                $dataProvider->query->andFilterWhere(['_curriculum' => $_curriculum]);
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of curriculum block is deleted successfully', ['code' => $model->id]));
                            return $this->redirect(['curriculum/curriculum-block', 'id' => $_curriculum]);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['curriculum/curriculum-block', 'code' => $model->code]);
                }

            } else {
                return $this->redirect(['curriculum/curriculum-block']);
            }
        }
        $model->scenario = ECurriculumSubjectBlock::SCENARIO_CREATE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($code) {
                $this->addSuccess(__('Item [{code}] of CurriculumBlock updated', ['code' => $model->id]));
            } else {
                $this->addSuccess(__('Item [{code}] added to CurriculumBlock', ['code' => $model->id]));
            }
            return $this->redirect(['curriculum/curriculum-block']);
        }

        return $this->render('curriculum-block', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'faculty' => $faculty,
            //'subject_block' => $subject_block,
        ]);
    }

    public function actionRatingGrade()
    {
        $model = new RatingGrade();
        $model->scenario = RatingGrade::SCENARIO_CREATE;
        $searchModel = new RatingGrade();
        $this->saveSearchParams();
        if ($attribute = $this->get('attribute')) {
            if ($model = RatingGrade::findOne(['code' => $this->get('id')])) {
                $model->$attribute = !$model->$attribute;

                if ($model->save()) {
                    if ($model->code) {
                        $this->addSuccess(__('Item [{code}] of rating grade is enabled', ['code' => $model->code]), true, false);
                    } else {
                        $this->addSuccess(__('Item [{code}] of rating grade is disabled', ['code' => $model->code]), true, false);
                    }
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [];
                }
            }
        }
        if ($code = $this->get('code')) {
            if ($model = RatingGrade::findOne(['code' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of rating grade is deleted successfully', ['code' => $model->code]));
                        }
                    } catch (\Exception $e) {
                        if ($e->getCode() == 23503) {
                            $this->addError(__('Could not delete related data'));
                        } else {
                            $this->addError($e->getMessage());
                        }
                    }
                    return $this->redirect(['curriculum/rating-grade']);
                }
            } else {
                return $this->redirect(['curriculum/rating-grade', 'code' => $model->code]);
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->addSuccess(__('Item [{code}] added to rating grade', ['code' => $model->code]));
            return $this->redirect(['curriculum/rating-grade']);
        }
        return $this->render('rating-grade', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            //'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionMarkingSystem()
    {
        $model = new MarkingSystem();
        $model->scenario = MarkingSystem::SCENARIO_CREATE;
        $searchModel = new MarkingSystem();
        if ($code = $this->get('code')) {
            if ($model = MarkingSystem::findOne(['code' => $code])) {
                $name_old = $model->name;
                $code_old = $model->code;
                $count_final_exams_old = $model->count_final_exams;
                if ($this->get('delete')) {
                    try {
                        if ($model->code == MarkingSystem::MARKING_SYSTEM_RATING || $model->code == MarkingSystem::MARKING_SYSTEM_FIVE || $model->code == MarkingSystem::MARKING_SYSTEM_CREDIT) {
                            $this->addInfo(__('The default rating system settings cannot be removed'));
                        } else {
                            if ($model->delete()) {
                                $this->addSuccess(__('Item [{code}] of marking system is deleted successfully', ['code' => $model->code]));
                            }
                        }
                    } catch (\Exception $e) {
                        if ($e->getCode() == 23503) {
                            $this->addError(__('Could not delete related data'));
                        } else {
                            $this->addError($e->getMessage());
                        }
                    }
                    return $this->redirect(['curriculum/marking-system']);
                }
            } else {
                return $this->redirect(['curriculum/marking-system', 'code' => $model->code]);
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->code == MarkingSystem::MARKING_SYSTEM_RATING || $model->code == MarkingSystem::MARKING_SYSTEM_FIVE || $model->code == MarkingSystem::MARKING_SYSTEM_CREDIT) {
                $model->name = $name_old;
                $model->code = $code_old;
                $model->count_final_exams = $count_final_exams_old;
            }
            if ($model->save()) {
                $this->addSuccess(__('Item [{code}] added to marking system', ['code' => $model->code]));
                return $this->redirect(['curriculum/marking-system', 'code' => $model->code]);
            }
        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionGradeType()
    {
        $model = new GradeType();
        $model->scenario = GradeType::SCENARIO_CREATE;
        $searchModel = new GradeType();

        if ($code = $this->get('code')) {
            if ($model = GradeType::findOne(['id' => $code])) {
                $marking_system_old = $model->_marking_system;
                $code_old = $model->code;
                $name_old = $model->name;

                if ($this->get('delete')) {
                    try {
                        if ($model->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT) {
                            $this->addInfo(__('The default rating system settings cannot be removed'));
                        } else {
                            if ($model->delete()) {
                                $this->addSuccess(__('Item [{code}] of grade type is deleted successfully', ['id' => $model->id]));
                            }
                        }
                    } catch (\Exception $e) {
                        if ($e->getCode() == 23503) {
                            $this->addError(__('Could not delete related data'));
                        } else {
                            $this->addError($e->getMessage());
                        }
                    }
                    return $this->redirect(['curriculum/grade-type']);
                }
            } else {
                return $this->redirect(['curriculum/grade-type', 'code' => $model->id]);
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT) {
                $model->_marking_system = $marking_system_old;
                $model->code = $code_old;
                $model->name = $name_old;
            }
            if ($model->save()) {
                $this->addSuccess(__('Item [{code}] added to grade type', ['code' => $model->code]));
                return $this->redirect(['curriculum/grade-type']);
            }
        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionFormation($id = "")
    {
        $model = $this->findCurriculumModel($id);
        $semesters = Semester::find()->where(['_curriculum' => $model->id, 'active' => true])->orderBy(['code' => SORT_ASC])->all();
        $searchModel = new ESubject();
        //  $dataProvider = $searchModel->search(Yii::$app->request->get());
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_education_type' => $model->_education_type]);
        //     $dataProvider->query->select('COUNT(e_subject_schedule.id) as count_lesson,_group,e_subject_schedule._education_year as _education_year,_semester,_curriculum');

        $curriculum_subjects = ECurriculumSubject::find()
            //->select('_semester, in_group, position')
            ->where(['_curriculum' => $model->id, /*'active' => ECurriculumSubject::STATUS_ENABLE,*/ /*'in_group' => null*/])
            ->orderBy(['position' => SORT_ASC, 'in_group' => SORT_ASC])
            //->groupBy(['_semester', 'in_group'])
            ->all();
        $curriculum_subjects_selective = ECurriculumSubject::find()
            ->select('in_group, total_acload, credit, _semester, at_semester')
            ->where(['_curriculum' => $model->id, /*'active' => ECurriculumSubject::STATUS_ENABLE*/])
            ->andFilterWhere(['>=', 'in_group', 0])
            ->andFilterWhere(['not', ['in_group' => null]])
            //->andWhere(['not', 'in_group', null])
            ->groupBy(['in_group', 'total_acload', 'credit', '_semester', 'at_semester'])
            ->all();

        //$selectives = array();
        $colors = array('bg-aqua', 'bg-green', 'bg-info', 'bg-olive', 'bg-teal', 'bg-orange', 'bg-purple', 'bg-lime', 'bg-fuchsia', 'bg-maroon', 'bg-yellow');
        $i = 0;
        //print_r($colors);
        $subjects = array();
        $semester_subjects = array();
        $additional_subjects = array();
        $info = "";
        $class = "";
        $disabled = false;

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($this->get('download')) {
            $searchModelSubject = new ECurriculumSubject();
            $query = $searchModelSubject->search_subjects($this->getFilterParams(), $model->id, false);

            $countQuery = clone $query;
            $limit = 250;
            if ($countQuery->count() <= $limit) {
                $fileName = ECurriculumSubject::generateDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {

            }
        }
        foreach ($semesters as $item) {

            foreach ($curriculum_subjects_selective as $key => $color) {
                if ($item->code == $color->_semester) {
                    if ($color->at_semester == ECurriculumSubject::STATUS_ENABLE) {
                        //if ($color->in_group === null || $color->in_group === "") {
                        @$semester_subjects[$item->code]['total_acload'] += @$color->total_acload;
                        @$semester_subjects[$item->code]['credit'] += @$color->credit;
                        // }
                    } else {
                        @$additional_subjects['total_acload'] += @$color->total_acload;
                        @$additional_subjects['credit'] += @$color->credit;
                    }
                }
            }
            foreach ($curriculum_subjects as $keys => $item2) {

                if ($item->code == $item2->_semester) {
                    if ($item2->active == ECurriculumSubject::STATUS_ENABLE) {
                        if ($item2->at_semester == ECurriculumSubject::STATUS_ENABLE) {
                            if ($item2->in_group === null || $item2->in_group === "") {
                                @$semester_subjects[$item2->_semester]['total_acload'] += @$item2->total_acload;
                                @$semester_subjects[$item2->_semester]['credit'] += @$item2->credit;
                            } else {
                                /*foreach ($curriculum_subjects_selective as $key => $color) {
                                    if (@$item2->in_group !== @$curriculum_subjects[$keys-1]->in_group) {
                                        if (@$item2->in_group == @$color->in_group) {
                                            @$semester_subjects[$item2->_semester]['total_acload'] += @$item2->total_acload;
                                            @$semester_subjects[$item2->_semester]['credit'] += @$item2->credit;
                                        }
                                    }
                                }*/
                            }
                        } else {
                            if ($item2->in_group === null || $item2->in_group === "") {
                                @$additional_subjects['total_acload'] += @$item2->total_acload;
                                @$additional_subjects['credit'] += @$item2->credit;
                            }
                        }
                    }
                    if (@$item2->total_acload > 0) {
                        $info = '<span class="badge bg-yellow pull pull-right">' . @$item2->total_acload . ' ' . __('soat') . ' / ' . @$item2->credit . ' ' . __('kredit') . ' / ' . substr(@$item2->subjectType->name, 0, 1) . ' / ' . ECurriculumSubject::getSemesterPositions()[@$item2->at_semester] . ' / ' . RatingGrade::getShortOptions()[@$item2->_rating_grade] . '</span>';
                    } else {
                        $info = "";
                    }
                    if (@$item2->reorder) {
                        $disabled = true;
                    } else {
                        $disabled = false;
                    }
                    if ($item2->_subject_type == SubjectType::SUBJECT_TYPE_REQUIRED) {
                        $class = "bg-light-blue";
                    } elseif ($item2->_subject_type == SubjectType::SUBJECT_TYPE_SELECTION) {
                        $class = "bg-olive";
                        foreach ($curriculum_subjects_selective as $key => $color) {
                            if ($item2->in_group === $color->in_group) {
                                $class = @$colors[@$key];
                            }
                        }
                    } else
                        $class = "bg-gray";

                    if ($item2->active == ECurriculumSubject::STATUS_DISABLE)
                        $class = "bg-gray";
                    $curriculum_weeks = ECurriculumWeek::find()->where(['_curriculum' => $model->id, '_semester' => $item->code, '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL, 'active' => ECurriculumWeek::STATUS_ENABLE])->count();

                    $subjects[$item->code][$item2->_subject] = [
                        'content' =>
                        Html::button($item2->subject->getFullName() . $info,
                            [
                                'class' => 'btn btn-flat '.$class,
                                'data-pjax'=>0,
                                'style' => 'width: 96%; text-align: left;',
                                'onclick'=>"window.location.href = '" . Url::to(['curriculum/curriculum-subject-edit', 'id' => $item2->id]) . "';",
                           //    Url::to(['curriculum/curriculum-subject-edit', 'id' => $item2->id]),

                            ]
                            ),
                        /*    $item2->subject->getFullName() . $info,
                           'options' => ['data' => ['id' => $item2->_subject],
                            'value' => Url::to(['curriculum/curriculum-subject-edit', 'id' => $item2->id]),
                            'class' => 'showModalButton loadMainContent ' . $class,
                            'title' => __('Input Sillabus Information') . ': ' . $item2->subject->name . ' [' . $item2->semester->name . ', ' . __('Count of weeks') . ': ' . $curriculum_weeks . ']',
                        ],*/
                        'disabled' => ($model->accepted || $disabled),
                        // 'title' =>  __('Input Sillabus Information').': '.$item2->subject->name .' ['.$item2->semester->name. ']'
                    ];
                }

            }
        }

        if ($code = $this->get('code')) {
            if ($model = ESubject::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of [{subject}] is deleted successfully', ['code' => $model->id]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['curriculum/subject', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['curriculum/subject', 'code' => $model->id]);
            }
        }
        return $this->render('formation', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'semesters' => $semesters,
            'subjects' => $subjects,
            'semester_subjects' => $semester_subjects,
            'additional_subjects' => $additional_subjects,
        ]);
    }

    public function actionCurriculumSubjectEdit($id = false, $detail = false, $exam = false, $edit = false, $delete = false)
    {
        $model = $this->findCurriculumSubjectModel($id);
        $model->scenario = ECurriculumSubject::SCENARIO_CREATE;
        $groups = array();
        $list_group = "";
        $old_group = "";
        if ($model->in_group !== null && $model->in_group != "") {
            if ($model->_subject_type == SubjectType::SUBJECT_TYPE_SELECTION) {
                $list_group = ECurriculumSubject::find()
                    ->where(['in_group' => $model->in_group])
                    ->andFilterWhere(['not', ['in_group' => null]])
                    ->all();
                foreach ($list_group as $item) {
                    $groups [] = $item->_subject;
                }
                $old_group = $model->in_group;
            }
            // print_r($groups);
            //$model->in_group = $groups;
        }
        $trainings = TrainingType::find()->where(['active' => TrainingType::STATUS_ENABLE])->all();
        $exam_types = ExamType::find()->where(['active' => ExamType::STATUS_ENABLE])->orderBy(['position' => SORT_ASC])->all();
        $curriculum_weeks = ECurriculumWeek::find()->where(['_curriculum' => $model->_curriculum, '_semester' => $model->_semester, '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL, 'active' => ECurriculumWeek::STATUS_ENABLE])->count();
        $curriculum_subjects_details = ECurriculumSubjectDetail::find()->where(['_curriculum' => $model->_curriculum, '_subject' => $model->_subject, '_semester' => $model->_semester, 'active' => true])->all();
        $curriculum_subjects_exams = ECurriculumSubjectExamType::find()
            //->leftJoin('h_exam_type', 'h_exam_type.code=_exam_type')
            ->where(['_curriculum' => $model->_curriculum, '_subject' => $model->_subject, '_semester' => $model->_semester, 'active' => true])->all();

        $load = array();
        foreach ($curriculum_subjects_details as $training) {
            $load[$training->_training_type] = $training->academic_load;
        }

        /*$exam = array();
        foreach ($curriculum_subjects_exams as $training) {
            $exam[$training->_exam_type] = $training->max_ball;
        }*/

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if (!$detail && !$exam && $this->get('delete')) {
            try {
                //$model->scenario = ECurriculumSubject::SCENARIO_DELETE;
                if (count(EStudentSubject::getGroupsByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject)) > 0 || count(ESubjectSchedule::getTeacherByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject)) > 0 || ESubjectExamSchedule::getExamByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject) > 0 || count(ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject)) > 0 || ESubjectResource::getResourceBySemesterSubject($model->_curriculum, $model->_semester, $model->_subject) > 0 || ESubjectTask::getTaskByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject) > 0) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    if ($model->delete()) {
                        ECurriculumSubjectDetail::deleteAll(
                            ['AND',
                                ['_curriculum' => $model->_curriculum, '_semester' => $model->_semester, '_subject' => $model->_subject,]
                            ]
                        );
                        ECurriculumSubjectExamType::deleteAll(
                            ['AND',
                                ['_curriculum' => $model->_curriculum, '_semester' => $model->_semester, '_subject' => $model->_subject,]
                            ]
                        );
                        $this->addSuccess(
                            __('Curriculum  Subject `{name}` deleted successfully.', [
                                'name' => $model->id
                            ])
                        );
                    }
                }
            } catch (Exception $e) {
                $this->addError($e->getMessage());
            }
            return $this->redirect(['formation', 'id' => $model->_curriculum]);
        }

        if ($model->credit == null) {
            $model->_exam_finish = ExamFinish::EXAM_FINISH_EXAM;
            $model->_subject_type = SubjectType::SUBJECT_TYPE_REQUIRED;
            $model->_rating_grade = RatingGrade::RATING_GRADE_SUBJECT;
            $model->reorder = ECurriculumSubject::STATUS_DISABLE;
        }
        /*if (count($curriculum_subjects_details) === 0 && count($curriculum_subjects_exams) === 0) {
            $model->_exam_finish = ExamFinish::EXAM_FINISH_EXAM;
            $model->_subject_type = SubjectType::SUBJECT_TYPE_REQUIRED;
            $model->_rating_grade = RatingGrade::RATING_GRADE_SUBJECT;
            $model->reorder = ECurriculumSubject::STATUS_DISABLE;
        }*/
        $searchModelDetail = new ECurriculumSubjectDetail();
        $dataProviderDetail = $searchModelDetail->search($this->getFilterParams());
        $dataProviderDetail->query->andFilterWhere([
            '_curriculum' => $model->_curriculum,
            '_subject' => $model->_subject,
            '_semester' => $model->_semester,
            'active' => ECurriculumSubjectDetail::STATUS_ENABLE,
        ]);

        $searchModelExam = new ECurriculumSubjectExamType();
        $dataProviderExam = $searchModelExam->search($this->getFilterParams());
        $dataProviderExam->query->andFilterWhere([
            '_curriculum' => $model->_curriculum,
            '_subject' => $model->_subject,
            '_semester' => $model->_semester,
            'e_curriculum_subject_exam_type.active' => ECurriculumSubjectExamType::STATUS_ENABLE,
        ]);

        if ($edit) {
            if ($detail) {
                $modelDetail = null;
                if($detail > 0){
                    $modelDetail = ECurriculumSubjectDetail::findOne(['id' => $detail]);
                    $old_training = $modelDetail->_training_type;
                }

                if($modelDetail == null)
                    $modelDetail = new ECurriculumSubjectDetail();

                $modelDetail->scenario = ECurriculumSubjectDetail::SCENARIO_CREATE;
                if($modelDetail->isNewRecord){
                    $modelDetail->_curriculum = $model->_curriculum;
                    $modelDetail->_subject = $model->_subject;
                    $modelDetail->_semester = $model->_semester;
                }

                if (Yii::$app->request->isAjax && $modelDetail->load(Yii::$app->request->post())) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($modelDetail);
                }


                if ($modelDetail->load(Yii::$app->request->post()) && $modelDetail->validate()) {
                    try {
                        if(!$modelDetail->isNewRecord){
                            if (count(ESubjectSchedule::getTeachersByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $old_training)) > 0 || count(ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $old_training)) > 0) {
                                $modelDetail->_training_type = $old_training;
                            }
                        }

                        if ($modelDetail->save()) {
                            $model->total_acload = $model->total_acload == 0 ? $modelDetail->academic_load : ECurriculumSubject::getTotal($model->subjectDetails, 'academic_load');
                            $model->save();
                            $this->addSuccess(__(
                                $modelDetail->isNewRecord ?
                                    'The subject detail for curriculum subject {name} has been created' :
                                    'The subject detail for curriculum subject {name} has been updated',
                                [
                                    'name' => $model->_subject
                                ]));
                            //return 1;
                            return $this->redirect(['curriculum-subject-edit', 'id' => $model->id]);
//                            Yii::$app->response->format = Response::FORMAT_JSON;
//                            return [];

                        } else {
                            $this->addError($modelDetail->getOneError());
                        }
                    } catch (\Exception $exception) {
                        $this->addError($exception->getMessage());
                        return $this->refresh();
                    }
                }

                return $this->renderAjax('_subject_detail_form', [
                    'model' => $modelDetail,
                    'curriculum_subject' => $model,
                ]);
            }

            if ($exam) {
                $modelExam = null;
                if($exam > 0){
                    $modelExam = ECurriculumSubjectExamType::findOne(['id' => $exam]);
                    $old_exam = $modelExam->_exam_type;
                }
                if($modelExam === null)
                    $modelExam = new ECurriculumSubjectExamType();

                $modelExam->scenario = ECurriculumSubjectExamType::SCENARIO_CREATE;
                if($modelExam->isNewRecord){
                    $modelExam->_curriculum = $model->_curriculum;
                    $modelExam->_subject = $model->_subject;
                    $modelExam->_semester = $model->_semester;
                }
                $exam_type_list = [];
                $child_c_exams = $child_m_exams = 0;
                $p_c_exams = $p_m_exams = $over_exams = 0;
                if($model->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT){
                    if(count($model->subjectExamType)>0){
                        foreach ($model->subjectExamType as $exam_type){
                            if($exam_type->_exam_type == ExamType::EXAM_TYPE_CURRENT)
                                $p_c_exams++;
                            elseif($exam_type->_exam_type == ExamType::EXAM_TYPE_MIDTERM)
                                $p_m_exams++;
                            elseif($exam_type->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST)
                                $child_c_exams++;
                            elseif($exam_type->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND)
                                $child_c_exams++;
                            elseif($exam_type->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST)
                                $child_m_exams++;
                            elseif($exam_type->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND)
                                $child_m_exams++;
                            elseif($exam_type->_exam_type == ExamType::EXAM_TYPE_OVERALL)
                                $over_exams++;
                        }
                        if($p_c_exams > 0 && $p_m_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_CURRENT_FIRST, ExamType::EXAM_TYPE_CURRENT_SECOND, ExamType::EXAM_TYPE_MIDTERM_FIRST, ExamType::EXAM_TYPE_MIDTERM_SECOND]);
                        } else if ($p_m_exams > 0 && $child_c_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_MIDTERM_FIRST, ExamType::EXAM_TYPE_MIDTERM_SECOND, ExamType::EXAM_TYPE_CURRENT]);
                        } else if($p_m_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_MIDTERM_FIRST, ExamType::EXAM_TYPE_MIDTERM_SECOND]);
                        } else if($p_c_exams > 0 && $child_m_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_CURRENT_FIRST, ExamType::EXAM_TYPE_CURRENT_SECOND, ExamType::EXAM_TYPE_MIDTERM]);
                        } else if($p_c_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_CURRENT_FIRST, ExamType::EXAM_TYPE_CURRENT_SECOND]);
                        } else if($child_c_exams > 0 && $child_m_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_CURRENT, ExamType::EXAM_TYPE_MIDTERM]);
                        } elseif($child_c_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_CURRENT]);
                        } elseif($child_m_exams > 0){
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_MIDTERM]);
                        }
                        elseif($over_exams > 0){
                            $exam_type_list = ExamType::getClassifierSpecialOptions(ExamType::EXAM_TYPE_OVERALL);
                        } else
                            $exam_type_list = ExamType::getClassifierDefinedOptions([ExamType::EXAM_TYPE_CURRENT, ExamType::EXAM_TYPE_MIDTERM]);
                    }
                    else{
                        $exam_type_list = ExamType::getClassifierSpecialOptions(ExamType::EXAM_TYPE_OVERALL);
                    }
                }
                else{
                    $exam_type_list = [ExamType::EXAM_TYPE_OVERALL=>ExamType::findOne(ExamType::EXAM_TYPE_OVERALL)->name];
                }

                if (Yii::$app->request->isAjax && $modelExam->load(Yii::$app->request->post())) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($modelExam);
                }
                if ($modelExam->load(Yii::$app->request->post()) && $modelExam->validate()) {
                    try {
                        if(!$modelExam->isNewRecord){
                            if (count(ESubjectExamSchedule::getTeachersByCurriculumSemesterSubjectExam($model->_curriculum, $model->_semester, $model->_subject, $old_exam)) > 0) {
                                $modelExam->_exam_type = $old_exam;
                            }
                        }

                        if ($modelExam->save()) {
                            if($modelExam->_exam_type != ExamType::EXAM_TYPE_OVERALL){
                                $modelExamOverall = ECurriculumSubjectExamType::findOne(
                                    [
                                        '_curriculum' => $model->_curriculum,
                                        '_subject' => $model->_subject,
                                        '_semester' => $model->_semester,
                                        '_exam_type' => ExamType::EXAM_TYPE_OVERALL
                                    ]);
                                if($modelExamOverall === null)
                                    $modelExamOverall = new ECurriculumSubjectExamType();
                                if($modelExamOverall->isNewRecord){
                                    $modelExamOverall->_curriculum = $model->_curriculum;
                                    $modelExamOverall->_subject = $model->_subject;
                                    $modelExamOverall->_semester = $model->_semester;
                                    $modelExamOverall->_exam_type = ExamType::EXAM_TYPE_OVERALL;
                                    $modelExamOverall->max_ball = $modelExam->max_ball;
                                }
                                else{
                                    if($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
                                        $modelExamOverall->max_ball = round(ECurriculumSubject::getTotal($model->subjectExamTypeOther, 'max_ball')/count($model->subjectExamTypeOther),0);
                                    }
                                    else{
                                        $modelExamOverall->max_ball = ECurriculumSubject::getTotal($model->subjectExamTypeOther, 'max_ball');
                                    }
                                }
                                $modelExamOverall->save();
                            }


                            $this->addSuccess(__(
                                $modelExam->isNewRecord ?
                                    'The subject exam for curriculum subject {name} has been created' :
                                    'The subject exam for curriculum subject {name} has been updated',
                                [
                                    'name' => $model->_subject
                                ]));
                            return $this->redirect(['curriculum-subject-edit', 'id' => $model->id]);
                        } else {
                            $this->addError($modelExam->getOneError());
                        }
                    } catch (\Exception $exception) {
                        $this->addError($exception->getMessage());
                        return $this->refresh();
                    }
                }

                return $this->renderAjax('_subject_exam_form', [
                    'model' => $modelExam,
                    'curriculum_subject' => $model,
                    'exam_type_list' => $exam_type_list,
                ]);
            }

        }

        if ($detail) {
            $modelDetail = ECurriculumSubjectDetail::findOne(['id' => $detail]);
            if ($delete) {
                if (count(ESubjectSchedule::getTeachersByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $modelDetail->_training_type)) > 0 || count(ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $modelDetail->_training_type)) > 0) {
                    $this->addError(__('Could not delete related data'));
                }
                else{
                    try {
                        if ($modelDetail->delete()) {
                            $model->total_acload = ECurriculumSubject::getTotal($model->subjectDetails, 'academic_load');
                            $model->save();
                            $this->addSuccess(__('{number} subject detail has been deleted ', ['number' => $modelDetail->id]));
                            return $this->redirect(['curriculum-subject-edit', 'id'=>$model->id]);
                        }
                    } catch (\Exception $exception) {
                        $this->addError($exception->getMessage());
                        return $this->redirect(['curriculum-subject-edit', 'detail' => $modelDetail->id]);
                    }
                }

                return $this->redirect(['curriculum-subject-edit', 'id'=>$model->id]);
            }
        }
        if ($exam) {
            $modelExam = ECurriculumSubjectExamType::findOne(['id' => $exam]);
            if ($delete) {
                if (count(ESubjectExamSchedule::getTeachersByCurriculumSemesterSubjectExam($model->_curriculum, $model->_semester, $model->_subject, $modelExam->_exam_type)) > 0) {
                    $this->addError(__('Could not delete related data'));
                }
                else{
                    try {
                        if ($modelExam->delete()) {
                            if($modelExam->_exam_type != ExamType::EXAM_TYPE_OVERALL) {

                                $modelExamOverall = ECurriculumSubjectExamType::findOne(
                                    [
                                        '_curriculum' => $model->_curriculum,
                                        '_subject' => $model->_subject,
                                        '_semester' => $model->_semester,
                                        '_exam_type' => ExamType::EXAM_TYPE_OVERALL
                                    ]);
                                    if (count(ECurriculumSubjectExamType::getExamTypeOtherByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject)) > 0) {
                                       // $modelExamOverall->max_ball = ECurriculumSubject::getTotal($model->subjectExamTypeOther, 'max_ball');
                                        if($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
                                            $modelExamOverall->max_ball = round(ECurriculumSubject::getTotal($model->subjectExamTypeOther, 'max_ball')/count($model->subjectExamTypeOther),0);
                                        }
                                        else{
                                            $modelExamOverall->max_ball = ECurriculumSubject::getTotal($model->subjectExamTypeOther, 'max_ball');
                                        }
                                        $modelExamOverall->save();
                                    }
                                    else{
                                        if (count(ESubjectExamSchedule::getTeachersByCurriculumSemesterSubjectExam($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_OVERALL)) > 0) {
                                            $this->addError(__('Could not delete related data'));
                                        }
                                        else
                                         $modelExamOverall->delete();
                                    }
                            }
                            $this->addSuccess(__('{number} subject exam has been deleted ', ['number' => $modelExam->id]));
                            return $this->redirect(['curriculum-subject-edit', 'id'=>$model->id]);
                        }
                    } catch (\Exception $exception) {
                        $this->addError($exception->getMessage());
                        return $this->redirect(['curriculum-subject-edit', 'exam' => $modelExam->id]);
                    }
                }

                return $this->redirect(['curriculum-subject-edit', 'id'=>$model->id]);
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ActiveForm::validate($model);
            }
            if ($model->in_group != null) {
                foreach ($model->in_group as $name) {
                    if (!in_array($name, $groups)) {
                        $subject = ECurriculumSubject::findOne(['_curriculum' => $model->_curriculum, '_semester' => $model->_semester, '_subject' => $name]);
                        //$subject->isNewRecord = false;
                        $subject_id = $subject->_subject;
                        $subject->attributes = $model->attributes;
                        $subject->_subject = $subject_id;
                        $subject->in_group = $model->id;
                        $subject->save(false);
                        ECurriculumSubjectDetail::deleteAll(
                            ['AND',
                                ['_curriculum' => $model->_curriculum, '_semester' => $model->_semester, '_subject' => $name]
                            ]
                        );
                        ECurriculumSubjectExamType::deleteAll(
                            ['AND',
                                ['_curriculum' => $model->_curriculum, '_semester' => $model->_semester, '_subject' => $name]
                            ]
                        );
                        foreach ($curriculum_subjects_details as $training) {
                            $subjects_detail = ECurriculumSubjectDetail::find()->where(['_curriculum' => $model->_curriculum, '_subject' => $name, '_semester' => $model->_semester, '_training_type' => $training->_training_type])->one();
                            if ($subjects_detail === null) {
                                $subjects_detail = new ECurriculumSubjectDetail();
                            }
                            $subjects_detail->attributes = $training->attributes;
                            $subjects_detail->_subject = $name;
                            $subjects_detail->save();
                        }

                        foreach ($curriculum_subjects_exams as $training) {
                            $subjects_exam = ECurriculumSubjectExamType::find()->where(['_curriculum' => $model->_curriculum, '_subject' => $name, '_semester' => $model->_semester, '_exam_type' => $training->_exam_type])->one();
                            if ($subjects_exam === null) {
                                $subjects_exam = new ECurriculumSubjectExamType();
                            }
                            $subjects_exam->attributes = $training->attributes;
                            $subjects_exam->_subject = $name;
                            $subjects_exam->save();
                        }
                    }
                }
                $model->in_group = $model->id;
            }
            if ($model->_subject_type == SubjectType::SUBJECT_TYPE_SELECTION) {
                if ($model->in_group == "") {
                    $model->in_group = $old_group;
                } else
                    $model->in_group = $model->id;
            }
            //$model->in_group = $model->id;
            if ($model->_subject_type == SubjectType::SUBJECT_TYPE_REQUIRED) {
                $model->in_group = null;
            }
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __('Curriculum  Subject `{name}` updated successfully.', [
                            'name' => $model->id
                        ]));
                } else {
                    $this->addSuccess(
                        __('Curriculum Subject `{name}` created successfully.', [
                            'name' => $model->id
                        ]));
                }
                return $this->redirect(['curriculum-subject-edit', 'id' => $model->id]);
               // return $this->redirect(['formation', 'id' => $model->_curriculum]);
            }

        } /*elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('curriculum-subject-edit', [
                'model' => $model,
                'trainings' => $trainings,
                'exam_types' => $exam_types,
                'load' => $load,
                'exam' => $exam,
                'list_group' => $list_group,
                'curriculum_weeks' => $curriculum_weeks,
                'searchModelDetail' => $searchModelDetail,
                'dataProviderDetail' => $dataProviderDetail,
                'searchModelExam' => $searchModelExam,
                'dataProviderExam' => $dataProviderExam,
            ]);
        }*/ else {
            return $this->render('curriculum-subject-edit', [
                'model' => $model,
                'trainings' => $trainings,
                'exam_types' => $exam_types,
                'load' => $load,
                'exam' => $exam,
                'list_group' => $list_group,
                'curriculum_weeks' => $curriculum_weeks,
                'searchModelDetail' => $searchModelDetail,
                'dataProviderDetail' => $dataProviderDetail,
                'searchModelExam' => $searchModelExam,
                'dataProviderExam' => $dataProviderExam,
            ]);
        }
    }

    public function actionWeek($id = "false", $code = "false")
    {
        $model = $this->findCurriculumModel($id);
        if (!$model->semesterStatus($model->id)) {
            $this->addInfo(
                __('All semester not accepted for this curriculum `{name}`.', [
                    'name' => $model->id
                ])
            );
            return $this->redirect(['curriculum']);
        }
        if ($this->get('delete')) {
            try {
                if (count(Semester::getSemesterByCurriculum($model->id)) == 0) {
                    ECurriculumWeek::deleteAll(
                        ['AND',
                            ['_curriculum' => $model->id]
                        ]
                    );
                    $this->addSuccess(
                        __('Curriculum week  deleted successfully.')
                    );
                }
            } catch (Exception $e) {
                $this->addError($e->getMessage());
            }
            return $this->redirect(['curriculum']);
        }

        $searchModel = new ECurriculumWeek();
        //$dataProvider = $searchModel->search(Yii::$app->request->get());
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $model->id]);

        $weekModel = new ECurriculumWeek();
        $weekModel->scenario = ECurriculumWeek::SCENARIO_CREATE;

        $exist_weeks = ECurriculumWeek::find()->where(['_curriculum' => $model->id])->all();

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ((count($exist_weeks) === 0 && count(Semester::getSemesterByCurriculum($model->id)) != 0) || ($this->get('refresh') && count(Semester::getSemesterByCurriculum($model->id)) != 0)) {
            
            $semesters = Semester::find()->where(['_curriculum' => $model->id, 'active' => true])->orderBy(['position' => SORT_ASC])->all();
            $weeks = array();
            $education_year = $model->_education_year;
            $education_period = $model->education_period;
            $level = Course::COURSE_FIRST;
            $start_date = date("Y-m-d", strtotime("first saturday $education_year-09"));
            //$end_date =  date("Y-m-d", strtotime("last saturday 2024-09"));
            $j = 0;
            $k = 0;
            for ($i = 1; $i <= (52 * $model->education_period); $i++) {
                $j -= 5;
                $weeks ['start_date'][$i] = date('Y-m-d', strtotime($start_date . ($j + $k) . " day"));
                $weeks ['end_date'][$i] = date('Y-m-d', strtotime($start_date . ($j + $k + 5) . " day"));
                $k += 12;
            }
            $start_b = "";
            $start_e = "";
            //      echo date('d-m-Y',strtotime("last Monday of October 2016")); //31-10-2016
            
            foreach ($weeks['start_date'] as $key => $item) {
                $oldModel = ECurriculumWeek::find()->where(['_curriculum'=>$model->id])->andWhere(['>=', 'end_date', $weeks ['end_date'][$key]])->orderBy(['end_date' => SORT_DESC])->one();
                if($oldModel === null) {
                    $newModel = new ECurriculumWeek();
                    $newModel->start_date = $item;
                    $newModel->end_date = $weeks ['end_date'][$key];
                    $newModel->_curriculum = $model->id;
                    $newModel->_semester = "null";
                    $newModel->_education_week_type = EducationWeekType::EDUCATION_WEEK_TYPE_HOLIDAY;
                    $start = Yii::$app->formatter->asDate($item, 'php:Y-m-d');
                    foreach ($semesters as $semester) {
                        $start_b = Yii::$app->formatter->asDate($semester->start_date, 'php:Y-m-d');
                        $start_e = Yii::$app->formatter->asDate($semester->end_date, 'php:Y-m-d');
                        if (($start >= $start_b) && ($start <= $start_e)) {
                            $newModel->_semester = $semester->code;
                            $newModel->_education_week_type = EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL;
                        }
                    }
                    $newModel->_level = $level;
                    $newModel->position = $key;
                    $newModel->save(false);
                }
            }

            for ($j = 0; $j < $education_period; $j++) {
                $condition = ['and',
                    ['>=', 'start_date', date('Y-m-d', strtotime("last saturday $education_year-09"))],
                    ['_curriculum' => $model->id],
                ];
                ECurriculumWeek::updateAll([
                    '_level' => $level
                ], $condition);
                $education_year++;
                $level++;
            }
            return $this->redirect(['week', 'id' => $model->id]);
        } else {
            if ($code = $this->get('code')) {
                $weekModel = ECurriculumWeek::findOne(['id' => $code]);
                $weekModel->scenario = ECurriculumWeek::SCENARIO_CREATE;
            }
            if ($weekModel->load(Yii::$app->request->post()) && $weekModel->save()) {
                $this->addSuccess(__('Item [{code}] edited to Curriculum Week', ['code' => $weekModel->id]));
            }
        }

        /*if ($this->get('refresh') && count(Semester::getSemesterByCurriculum($model->id)) != 0) {

            $semesters = Semester::find()->where(['_curriculum' => $model->id, 'active' => true])->orderBy(['position' => SORT_ASC])->all();
            $weeks = array();
            $education_year = $model->_education_year;
            $education_period = $model->education_period;
            $level = Course::COURSE_FIRST;
            $start_date = date("Y-m-d", strtotime("first saturday $education_year-09"));
            //$end_date =  date("Y-m-d", strtotime("last saturday 2024-09"));
            $j = 0;
            $k = 0;
            for ($i = 1; $i <= (52 * $model->education_period); $i++) {
                $j -= 5;
                $weeks ['start_date'][$i] = date('Y-m-d', strtotime($start_date . ($j + $k) . " day"));
                $weeks ['end_date'][$i] = date('Y-m-d', strtotime($start_date . ($j + $k + 5) . " day"));
                $k += 12;
            }
            $start_b = "";
            $start_e = "";
            //      echo date('d-m-Y',strtotime("last Monday of October 2016")); //31-10-2016

            foreach ($weeks['start_date'] as $key => $item) {
                $oldModel = ECurriculumWeek::findOne(['_level' => $level, '_curriculum'=>$model->id]);
                if($oldModel === null){
                    $newModel = new ECurriculumWeek();
                    $newModel->start_date = $item;
                    $newModel->end_date = $weeks ['end_date'][$key];
                    $newModel->_curriculum = $model->id;
                    $newModel->_semester = "null";
                    $newModel->_education_week_type = EducationWeekType::EDUCATION_WEEK_TYPE_HOLIDAY;
                    $start = Yii::$app->formatter->asDate($item, 'php:Y-m-d');
                    foreach ($semesters as $semester) {
                        $start_b = Yii::$app->formatter->asDate($semester->start_date, 'php:Y-m-d');
                        $start_e = Yii::$app->formatter->asDate($semester->end_date, 'php:Y-m-d');
                        if (($start >= $start_b) && ($start <= $start_e)) {
                            $newModel->_semester = $semester->code;
                            $newModel->_education_week_type = EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL;
                        }
                    }
                    $newModel->_level = $level;
                    $newModel->position = $key;
                    $newModel->save(false);
                }

            }

            for ($j = 0; $j < $education_period; $j++) {
                $condition = ['and',
                    ['>=', 'start_date', date('Y-m-d', strtotime("last saturday $education_year-09"))],
                    ['_curriculum' => $model->id],
                ];
                ECurriculumWeek::updateAll([
                    '_level' => $level
                ], $condition);
                $education_year++;
                $level++;
            }
            return $this->redirect(['week', 'id' => $model->id]);
        } else {
            if ($code = $this->get('code')) {
                $weekModel = ECurriculumWeek::findOne(['id' => $code]);
                $weekModel->scenario = ECurriculumWeek::SCENARIO_CREATE;
            }
            if ($weekModel->load(Yii::$app->request->post()) && $weekModel->save()) {
                $this->addSuccess(__('Item [{code}] edited to Curriculum Week', ['code' => $weekModel->id]));
            }
        }*/
        return $this->renderView([
            'model' => $model,
            'weekModel' => $weekModel,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionSubjectTopic($id = "false", $code = "false")
    {
        $curriculum = $this->findCurriculumModel($id);

        $searchModel = new ECurriculumSubjectTopic();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum->id]);

        $model = new ECurriculumSubjectTopic();
        $model->scenario = ECurriculumSubjectTopic::SCENARIO_CREATE;
        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($code = $this->get('code')) {
            if ($model = ECurriculumSubjectTopic::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of Curriculum Subject Topic is deleted successfully', ['code' => $model->id]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['curriculum/subject-topic', 'id' => $curriculum->id]);

                }
            } else {
                return $this->redirect(['curriculum/subject-topic', 'id' => $curriculum->id, 'code' => $model->id]);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->addSuccess(__('Item [{code}] added to Curriculum Subject Topic', ['code' => $model->id]));
            return $this->redirect(['curriculum/subject-topic', 'id' => $curriculum->id]);
        }

        return $this->render('subject-topic', [
            'model' => $model,
            'curriculum' => $curriculum,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @resource curriculum/lesson-pair-delete
     */
    public function actionLessonPair($id = false)
    {
        $model = $id ? $this->findLessonPairModel($id) : new LessonPair();
        $model->scenario = LessonPair::SCENARIO_CREATE;

        if ($this->get('delete') && $this->canAccessToResource('curriculum/lesson-pair-delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(__('LessonPair [{code}] deleted successfully', ['code' => $model->code]));
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 23503) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(['lesson-pair']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->code = (string)(LessonPair::FIRST_PAIR_CODE + (int)$model->name);
            //     print_r($model->attributes);

            if ($model->save()) {
                $this->addSuccess(__('LessonPair [{code}] updated successfully', ['code' => $model->code]));
                return $this->redirect(['lesson-pair']);
                //return $this->redirect(['lesson-pair', 'id' => $model->id]);
            }
        }
        $searchModel = new LessonPair();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if($searchModel->_education_year){
            $searchModel->_education_year = $searchModel->_education_year;
            $dataProvider->query->andFilterWhere(['_education_year' => $searchModel->_education_year]);
        }
        else{
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['_education_year' => EducationYear::getCurrentYear()->code]);
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionScheduleInfo()
    {
        $searchModel = new ESubjectSchedule();
        $dataProvider = $searchModel->search_info($this->getFilterParams());
        $dataProvider->query->select('COUNT(e_subject_schedule.id) as count_lesson,_group as _group,e_subject_schedule._education_year as _education_year,_semester,_curriculum');
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear() ? EducationYear::getCurrentYear()->code : date('Y');
            $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => $searchModel->_education_year]);
        }
        $faculty = "";
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
                $dataProvider->query->andFilterWhere(['e_curriculum._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $dataProvider->query->groupBy(['_group', 'e_subject_schedule._education_year', '_semester', '_curriculum']);
        if (isset($_POST['btn'])) {
            return $this->redirect(['curriculum/schedule',
                'FilterForm[_curriculum]' => $searchModel->_curriculum,
                'FilterForm[_education_year]' => $searchModel->_education_year,
                'FilterForm[_semester]' => $searchModel->_semester,
                'FilterForm[_group]' => $searchModel->_group]);
        }

        if ($fileName = $this->get('file')) {
            $dir = Yii::getAlias('@backend/runtime/export/');
            $baseName = basename($dir . $fileName);

            if (file_exists($dir . $baseName)) {
                return Yii::$app->response->sendFile($dir . $baseName, $baseName);
            } else {
                $this->addError(__('File {name} not found', ['name' => $fileName]));
            }
            return $this->goBack();
        }

        if ($this->get('download')) {
            $education_year = $this->get('education_year');
            $query = $searchModel->search_info($this->getFilterParams(), false);
            $query->select('COUNT(e_subject_schedule.id) as count_lesson,_group as _group,e_subject_schedule._education_year as _education_year,_semester,_curriculum');
            $query->andFilterWhere(['e_subject_schedule._education_year' => $education_year]);
            $query->groupBy(['_group', 'e_subject_schedule._education_year', '_semester', '_curriculum']);

            $countQuery = clone $query;
            $limit = 1000;
            if ($countQuery->count() <= $limit) {
                $fileName = ESubjectSchedule::generateDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {
                /**
                 * @var $queue Queue
                 * @var $queue1 Queue
                 */
                $queue1 = Yii::$app->queue;
                $queue = Yii::$app->queueFile;
                $prefix = $queue1->channel;
                if ($queue1->redis->llen("$prefix.waiting") == 0) {
                    $queue = $queue1;
                }

                if ($queue
                    ->ttr(900)
                    ->push(
                        new SubjectScheduleListFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'education_year' => $education_year,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['curriculum/schedule-info', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Dars jadvallari soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar sahifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['curriculum/schedule-info']);
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionSchedule()
    {
        $searchModel = new FilterForm();
        $tables = array();
        if ($searchModel->load(Yii::$app->request->get()) && $searchModel->_semester && $searchModel->_group) {
            $time_tables = ESubjectSchedule::find()
                ->where([
                    '_curriculum' => $searchModel->_curriculum,
                    '_education_year' => $searchModel->_education_year,
                    '_semester' => $searchModel->_semester,
                    '_group' => $searchModel->_group
                ])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                ->all();
            Url::remember(Yii::$app->request->url, 'schedule');
            //$draggable = "fc-draggable fc-resizable";
            foreach ($time_tables as $table) {
                $event = new \yii2fullcalendar\models\Event();
                $event->id = $table->id;
                $event->title = ($table->lessonPair ? $table->lessonPair->period : '') . ' | ' . $table->trainingType->name;
                if (@$table->additional != "")
                    $event->nonstandard = '<b>' . @$table->subject->name . '</b>' . '<br>' . @$table->employee->fullName . '<br>' . __('Room') . ': ' . @$table->auditorium->name . '<br>' . @$table->additional;
                else
                    $event->nonstandard = '<b>' . @$table->subject->name . '</b>' . '<br>' . @$table->employee->fullName . '<br>' . __('Room') . ': ' . @$table->auditorium->name;
                $event->start = Yii::$app->formatter->asDate($table->lesson_date, 'php:Y-m-d');
                //$event->backgroundColor = '#0d6aad';
                //$event->backgroundColor = '#0d6aad';
                //$event->borderColor = 'red';
                $tables[] = $event;
            }
        }
        return $this->renderView([
            //'dataProvider' => $searchModel->search($this->getFilterParams()),
            //'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'time_tables' => $tables,
            // 'model' => $model,
        ]);
    }

    public function actionScheduleCreate($date, $curriculum, $education_year, $semester, $group)
    {
        $model = new ESubjectSchedule();
        $model->scenario = ESubjectSchedule::SCENARIO_INSERT;

        $curriculum = $this->findCurriculumModel($curriculum)->id;
        $education_year = $this->findEducationYearModel($education_year)->code;
        $semester = $this->findCurriculumSemesterModel($curriculum, $semester)->code;
        $group = $this->findGroupModel($group)->id;

        $groups = EStudentMeta::getContingentByCurriculumSemester($curriculum, $education_year, $semester);
        //$subjects = ECurriculumSubject::getSubjectByCurriculumSemester($curriculum, $semester);
        $subjects = EStudentSubject::getSubjectByCurriculumSemesterGroup($curriculum, $education_year, $semester, $group);
        $pairs = LessonPair::getLessonPairByYear($education_year);
        $auditoriums = EAuditorium::getOptions();
        $teachers = EEmployeeMeta::getTeachers();

        $model->lesson_date = $date;
        $model->groups = $group;
        $url = Url::previous('schedule');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $week = ECurriculumWeek::getWeekByCurriculumDate($curriculum, Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d'));
            if ($week === null) {
                $this->addError(
                    __('This is date `{name}` is not period of semester', [
                        'name' => $model->lesson_date
                    ])
                );
                return $this->redirect($url);
            }
            $model->_week = $week->id;
            $old = $model->attributes;
            $valid = true;
            $arrItems = array();
            foreach ($model->groups as $name) {
                $model = new ESubjectSchedule();
                $model->attributes = $old;
                $model->active = ESubjectSchedule::STATUS_ENABLE;
                $model->lesson_date = $date;
                $model->_curriculum = $curriculum;
                $model->_education_year = $education_year;
                $model->_semester = $semester;
                $model->_group = $name;
                $valid = $model->validate() && $valid;
                $arrItems[] = $model;
                // $model->groups =  $name;
                //if($model->validate())
                //print_r($model->attributes);
                // $model->save(true);
            }
            if ($valid) {
                foreach ($arrItems as $objItemValidated) {
                    //print_r( $objItemValidated->attributes);
                    $objItemValidated->save();
                }
                $this->addSuccess(__('Schedule [{code}] created successfully', ['code' => $model->id]));
                return $this->redirect($url);
            }
            // $model->save(false);
            // return $this->redirect($url);
        }
        return $this->renderAjax('schedule-create', [
            'model' => $model,
            'groups' => $groups,
            'group' => $group,
            'subjects' => $subjects,
            'pairs' => $pairs,
            'auditoriums' => $auditoriums,
            'teachers' => $teachers,
            'curriculum' => $curriculum,
            'education_year' => $education_year,
            'semester' => $semester,
        ]);
    }

    public function actionScheduleEdit($id)
    {
        $model = $this->findSubjectScheduleModel($id);
        $model->scenario = ESubjectSchedule::SCENARIO_INSERT;

        $curriculum = $model->_curriculum;
        $education_year = $model->_education_year;
        $semester = $model->_semester;
        $group = $model->_group;

        $url = Url::previous('schedule');

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(__('Schedule [{code}] deleted successfully', ['code' => $model->id]));
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 23503) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    $this->addError($e->getMessage());
                }

            }
            return $this->redirect($url);
        }

        $groups = EStudentMeta::getContingentByCurriculumSemester($curriculum, $education_year, $semester);
        //$subjects = ECurriculumSubject::getSubjectByCurriculumSemester($curriculum, $semester);
        $subjects = EStudentSubject::getSubjectByCurriculumSemesterGroup($curriculum, $education_year, $semester, $group);
        $pairs = LessonPair::getLessonPairByYear($education_year);
        $auditoriums = EAuditorium::getOptions();
        $teachers = EEmployeeMeta::getTeachers();
        $model->groups = $group;
        if ($model->load(Yii::$app->request->post())) {
            $week = ECurriculumWeek::getWeekByCurriculumDate($curriculum, Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d'));
            if ($week === null) {
                $this->addError(
                    __('This is date `{name}` is not period of semester', [
                        'name' => $model->lesson_date
                    ])
                );
                return $this->redirect($url);
            }
            $model->_week = $week->id;
            if ($model->save()){
                $this->addSuccess(__('Schedule [{code}] edited successfully', ['code' => $model->id]));
                return $this->redirect($url);
            }

        }
        return $this->renderAjax('schedule-create', [
            'model' => $model,
            'groups' => $groups,
            'group' => $group,
            'subjects' => $subjects,
            'pairs' => $pairs,
            'auditoriums' => $auditoriums,
            'teachers' => $teachers,
            'curriculum' => $curriculum,
            'education_year' => $education_year,
            'semester' => $semester,
        ]);
    }

    public function actionScheduleGenerateWeekly($curriculum = "false", $semester = "false", $group = "false")
    {
        $curriculum = $this->findCurriculumModel($curriculum);
        $semester = $this->findCurriculumSemesterModel($curriculum->id, $semester);
        $group = $this->findGroupModel($group);
        $searchModel = new ECurriculumWeek();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum->id, '_semester' => $semester->code, '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL]);

        $model = new ESubjectSchedule();
        $url = Url::previous('schedule');
        if ($code = $this->get('id')) {
            if ($model = ECurriculumWeek::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    if(ESubjectSchedule::getWeeksByCurriculumGroup($model->id, $model->_curriculum, $model->_semester, $group->id)>0) {
                        if (Yii::$app->formatter->asDate($model->start_date, 'php:Y-m-d') > date("Y-m-d", time())) {

                            ESubjectSchedule::deleteAll(
                                ['AND',
                                    [
                                        '_week' => $model->id,
                                        '_curriculum' => $model->_curriculum,
                                        '_semester' => $model->_semester,

                                    ]
                                ]
                            );
                            $this->addSuccess(__('Item [{code}] of Curriculum Week Lesson is deleted successfully', ['code' => $model->id]));
                        }
                    }

                    return $this->redirect(['curriculum/schedule-generate-weekly', 'curriculum' => $model->_curriculum, 'semester' => $model->_semester, 'group' => $group->id]);
                }

                if ($this->get('download')) {
                    $mpdf = new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'tempDir' => Yii::getAlias('@runtime/mpdf'),
                    ]);
                    $mpdf->defaultCssFile = Yii::getAlias('@backend/assets/app/css/pdf-print.css');
                    $mpdf->shrink_tables_to_fit = 1;
                    $mpdf->keep_table_proportions = true;
                    $mpdf->SetDisplayMode('fullwidth');

                    $query = ESubjectSchedule::find()
                        ->andFilterWhere([
                            '_week' => $model->id,
                            '_semester' => $model->_semester,
                            '_group' => $group->id
                        ])
                        ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                        ->all();
                    $dates = array();
                    foreach ($query as $item) {
                        $dates[] = Yii::$app->formatter->asDate($item->lesson_date);
                    }
                    $dates = array_count_values($dates);
                    $mpdf->WriteHTML($this->renderPartial('schedule-weekly-pdf',
                        [
                            'model' => $model,
                            'group' => $group,
                            'query' => $query,
                            'dates' => $dates,
                            'semester' => $semester,

                        ]));

                    return $mpdf->Output('TimeTable-' . Yii::$app->formatter->asDate($model->start_date).' - '.Yii::$app->formatter->asDate($model->end_date) . '.pdf', Destination::DOWNLOAD);
                }
            } else {
                return $this->redirect(['curriculum/schedule-generate-weekly', 'curriculum' => $model->_curriculum, 'semester' => $model->_semester, 'group' => $group->id]);
            }
        }
        return $this->renderView([
            'model' => $model,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'curriculum' => $curriculum,
            'semester' => $semester,
            'group' => $group,
            'url' => $url,
        ]);
    }

    public function actionToScheduleGroups()
    {
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('week') && Yii::$app->request->post('curriculum') && Yii::$app->request->post('semester') && Yii::$app->request->post('group')) {
            $selection = (array)Yii::$app->request->post('selection');
            $_week = Yii::$app->request->post('week');
            $_curriculum = Yii::$app->request->post('curriculum');
            $_semester = Yii::$app->request->post('semester');
            $_group = Yii::$app->request->post('group');
            // $group = EGroup::findOne((int)$_group);
            $exist_schedule = ESubjectSchedule::getScheduleByCurriculumGroup($_week, $_curriculum, $_semester, $_group);
            $url = Url::previous('schedule');

            foreach ($selection as $id) {
                $all_schedule = ESubjectSchedule::getWeeksByCurriculumGroup($id, $_curriculum, $_semester, $_group);
                $selected_week = ECurriculumWeek::getByCurriculumWeekPeriod($id);
                if ($selected_week->end_date->format("N") - $selected_week->start_date->format("N") >= 4) {
                    if ($all_schedule == 0) {
                        foreach ($exist_schedule as $item) {
                            $new_schedule = new ESubjectSchedule();
                            $new_schedule->attributes = $item->attributes;
                            $new_schedule->_week = $id;
                            $new_schedule->active = true;
                            $day = $item->lesson_date->format("N");
                            // echo "<pre>";
                            //  print_r($day);
                            //if($day >= $selected_week->start_date->format("N") && $day <= $selected_week->end_date->format("N")) {
                            $new_schedule->lesson_date = ECurriculumWeek::getDateByCurriculumWeekPeriod($id, $day)->format('Y-m-d');
                            // }

                            //if($new_schedule->lesson_date >= $selected_week->start_date->format("Y-m-d'") && $new_schedule->lesson_date <= $selected_week->end_date->format("Y-m-d'"))
                            $new_schedule->save(true);
                            //    print_r($new_schedule->attributes);

                        }
                    }
                } else {
                    $this->addInfo(
                        __('Please insert schedule for this week. ')
                    );
                }
            }

            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionExamScheduleInfo()
    {
        $searchModel = new ESubjectExamSchedule();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->select('COUNT(e_subject_exam_schedule.id) as count_lesson,_group,e_subject_exam_schedule._education_year,_semester,_curriculum');
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear() ? EducationYear::getCurrentYear()->code : date('Y');
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => $searchModel->_education_year]);
        }
        /*
        if($searchModel->_education_year){
            $searchModel->_education_year = $searchModel->_education_year;
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => $searchModel->_education_year]);
        }
        else{
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => EducationYear::getCurrentYear()->code]);
        }*/

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
                $dataProvider->query->andFilterWhere(['e_curriculum._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $dataProvider->query->groupBy(['_group', 'e_subject_exam_schedule._education_year', '_semester', '_curriculum']);
        if (isset($_POST['btn'])) {
            return $this->redirect(['curriculum/exam-schedule',
                'FilterForm[_curriculum]' => $searchModel->_curriculum,
                'FilterForm[_education_year]' => $searchModel->_education_year,
                'FilterForm[_semester]' => $searchModel->_semester,
                'FilterForm[_group]' => $searchModel->_group]);
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionExamSchedule()
    {
        $searchModel = new FilterForm();
        $tables = array();
        if ($searchModel->load(Yii::$app->request->get()) && $searchModel->_semester && $searchModel->_group) {
            $time_tables = ESubjectExamSchedule::find()
                ->where([
                    '_curriculum' => $searchModel->_curriculum,
                    '_education_year' => $searchModel->_education_year,
                    '_semester' => $searchModel->_semester,
                    '_group' => $searchModel->_group
                ])
                ->orderBy(['exam_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                ->all();
            Url::remember(Yii::$app->request->url, 'exam-schedule');
            //$draggable = "fc-draggable fc-resizable";
            foreach ($time_tables as $table) {
                $event = new \yii2fullcalendar\models\Event();
                $event->id = $table->id;
                $event->title = $table->lessonPair->period . ' | ' . strtoupper($table->examType->name) . ' | ' . strtoupper($table->finalExamType->name);
                $event->nonstandard = '<b>' . @$table->subject->name . '</b><br>' . @$table->employee->fullName . '<br>' . __('Room') . ': ' . @$table->auditorium->name;
                $event->start = Yii::$app->formatter->asDate($table->exam_date, 'php:Y-m-d');
                // $event->backgroundColor = '#0d6aad';
                //$event->borderColor = 'red';
                $tables[] = $event;
            }
        }
        return $this->renderView([
            //'dataProvider' => $searchModel->search($this->getFilterParams()),
            //'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'time_tables' => $tables,
            // 'model' => $model,
        ]);
    }

    public function actionExamScheduleCreate($date, $curriculum, $education_year, $semester, $group)
    {
        $model = new ESubjectExamSchedule();
        $model->scenario = ESubjectExamSchedule::SCENARIO_INSERT;

        $curriculum = $this->findCurriculumModel($curriculum)->id;
        $education_year = $this->findEducationYearModel($education_year)->code;
        $semester = $this->findCurriculumSemesterModel($curriculum, $semester)->code;
        $group = $this->findGroupModel($group)->id;

        $groups = EStudentMeta::getContingentByCurriculumSemester($curriculum, $education_year, $semester);
        //$subjects = ECurriculumSubject::getSubjectByCurriculumSemester($curriculum, $semester);
        $subjects = EStudentSubject::getSubjectByCurriculumSemesterGroup($curriculum, $education_year, $semester, $group);
        $pairs = LessonPair::getLessonPairByYear($education_year);
        $auditoriums = EAuditorium::getOptions();
        //$teachers = EEmployeeMeta::getTeachers();

        $model->exam_date = $date;
        $model->_group = $group;
        $url = Url::previous('exam-schedule');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $week = ECurriculumWeek::getWeekByCurriculumDate($curriculum, Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d'));
            if ($week === null) {
                $this->addError(
                    __('This is date `{name}` is not period of semester', [
                        'name' => $model->exam_date
                    ])
                );
                return $this->redirect($url);
            }
            $model->_week = $week->id;
            $old = $model->attributes;
            $valid = true;
            $arrItems = array();
            foreach ($model->groups as $name) {
                $model = new ESubjectExamSchedule();
                $model->attributes = $old;
                $model->active = ESubjectExamSchedule::STATUS_ENABLE;
                $model->exam_date = $date;
                $model->_curriculum = $curriculum;
                $model->_education_year = $education_year;
                $model->_semester = $semester;
                $model->_group = $name;
                $valid = $model->validate() && $valid;
                $arrItems[] = $model;
            }
            if ($valid) {
                foreach ($arrItems as $objItemValidated) {
                    //print_r( $objItemValidated->attributes);
                    $objItemValidated->save();
                }
                $this->addSuccess(__('Exam Schedule [{code}] created successfully', ['code' => $model->id]));
                return $this->redirect($url);
            }
            //$model->save(true);
            //return $this->redirect($url);
        }
        return $this->renderAjax('exam-schedule-create', [
            'model' => $model,
            'groups' => $groups,
            'group' => $group,
            'subjects' => $subjects,
            'pairs' => $pairs,
            'auditoriums' => $auditoriums,
            //'teachers' => $teachers,
            'curriculum' => $curriculum,
            'education_year' => $education_year,
            'semester' => $semester,
        ]);
    }

    public function actionExamScheduleEdit($id)
    {
        $model = $this->findSubjectExamScheduleModel($id);
        $model->scenario = ESubjectExamSchedule::SCENARIO_INSERT;

        $curriculum = $model->_curriculum;
        $education_year = $model->_education_year;
        $semester = $model->_semester;
        $group = $model->_group;

        $url = Url::previous('exam-schedule');

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(__('Exam Schedule [{code}] deleted successfully', ['code' => $model->id]));
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 23503) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    $this->addError($e->getMessage());
                }
                //$this->addError($e->getMessage());
            }
            return $this->redirect($url);
        }

        $groups = EStudentMeta::getContingentByCurriculumSemester($curriculum, $education_year, $semester);
        //$subjects = ECurriculumSubject::getSubjectByCurriculumSemester($curriculum, $semester);
        $subjects = EStudentSubject::getSubjectByCurriculumSemesterGroup($curriculum, $education_year, $semester, $group);
        $pairs = LessonPair::getLessonPairByYear($education_year);
        $auditoriums = EAuditorium::getOptions();
        // $teachers = EEmployeeMeta::getTeachers();
        $model->groups = $group;
        if ($model->load(Yii::$app->request->post())) {
            $week = ECurriculumWeek::getWeekByCurriculumDate($curriculum, Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d'));
            if ($week === null) {
                $this->addError(
                    __('This is date `{name}` is not period of semester', [
                        'name' => $model->exam_date
                    ])
                );
                return $this->redirect($url);
            }
            $model->_week = $week->id;
            if ($model->save()){
                $this->addSuccess(__('Exam Schedule [{code}] edited successfully', ['code' => $model->id]));
                return $this->redirect($url);
            }

        }
        return $this->renderAjax('exam-schedule-create', [
            'model' => $model,
            'groups' => $groups,
            'group' => $group,
            'subjects' => $subjects,
            'pairs' => $pairs,
            'auditoriums' => $auditoriums,
            //  'teachers' => $teachers,
            'curriculum' => $curriculum,
            'education_year' => $education_year,
            'semester' => $semester,
        ]);
    }

    public function actionAttendanceSetting()
    {
        $model = new EAttendanceSettingBorder();
        $model->scenario = EAttendanceSettingBorder::SCENARIO_CREATE;
        $searchModel = new EAttendanceSettingBorder();

        if ($code = $this->get('code')) {
            if ($model = EAttendanceSettingBorder::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of attendance setting is deleted successfully', ['id' => $model->id]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['curriculum/attendance-setting', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['curriculum/attendance-setting', 'code' => $model->id]);
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->addSuccess(__('Item [{code}] added to grade type', ['code' => $model->id]));
        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionStudentRegister($id = false)
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }
        //if (/*empty($searchModel->_curriculum) && empty($searchModel->_education_year) &&*/ empty($searchModel->_group) /*&& empty($searchModel->_semester)*/) {
        if (empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
        }
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $dataProvider->query->andFilterWhere(['e_student_meta.active' => EStudentMeta::STATUS_ENABLE]);

        $students = [];
        foreach ($dataProvider->getModels() as $item){
            $students [$item->_student] = $item->_student;
        }

        $subject = new ECurriculumSubject();
        $subjectProvider = $subject->search($this->getFilterParams());
        $subjectProvider->query->andFilterWhere(['e_curriculum_subject._curriculum' => $searchModel->_curriculum, '_semester' => $searchModel->_semestr]);
        $subjectProvider->query->orderBy(['in_group' => SORT_DESC]);

        $studentSubject = new EStudentSubject();
        $studentSubjectProvider = $studentSubject->search($this->getFilterParams());
        $studentSubjectProvider->query->andFilterWhere(['_curriculum' => $searchModel->_curriculum, '_semester' => $searchModel->_semestr, '_education_year' => $searchModel->_education_year]);
        $studentSubjectProvider->query->andFilterWhere(['in', '_student' , $students]);

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if (empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
            $subjectProvider->query->andWhere('1 <> 1');
            $studentSubjectProvider->query->andWhere('1 <> 1');
        }
        if ($this->get('delete')) {
            if ($id) {
                if ($model = EStudentSubject::findOne($id)) {
                    $url = [
                        'curriculum/student-register',
                        'EStudentMeta[_curriculum]'=> $model->_curriculum,
                        'EStudentMeta[_education_year]'=> $model->_education_year,
                        'EStudentMeta[_semestr]'=> $model->_semester,
                        'EStudentMeta[_group]'=> $model->_group,
                    ];
                    if (count(EAcademicRecord::getAcadMarkByCurriculumSemesterSubject($model->_student, $model->_semester, $model->_subject))>0 || count(EPerformance::getMarkByCurriculumSemesterSubject($model->_student, $model->_semester, $model->_subject))>0 || count(EStudentTaskActivity::getMarkByCurriculumSemesterSubject($model->_student, $model->_semester, $model->_subject))>0) {
                        $this->addError(__('Could not delete related data'));
                    }
                    else if ($issue = $model->anyIssueWithDelete()) {
                        $this->addError($issue);
                    } else {
                        $this->addSuccess(__('Subject [{code}] deleted successfully', ['code' => $model->subject->name]));

                    }
                    return $this->redirect($url);
                }

            }


            //return $this->redirect(['curriculum/student-register']);
        }

        if ($this->get('edit')) {
            if ($id) {
                if ($model = EStudentSubject::findOne($id)) {
                    $model->scenario = EStudentSubject::SCENARIO_EDIT_GROUP;
                    $groups = EGroup::getOptions($model->_curriculum);

                    if($filter = EGroup::findOne($this->get('filter'))){
                        if ($model->load(Yii::$app->request->post())) {
                            if ($model->save()){
                                $this->addSuccess(__('Group [{code}] edited successfully', ['code' => $model->group->name]));
                                //Yii::$app->response->format = Response::FORMAT_JSON;

                                return $this->redirect(
                                    [
                                        'curriculum/student-register',
                                        'EStudentMeta[_curriculum]'=> $model->_curriculum,
                                        'EStudentMeta[_education_year]'=> $model->_education_year,
                                        'EStudentMeta[_semestr]'=> $model->_semester,
                                        'EStudentMeta[_group]'=> $filter->id,
                                    ]);
                            }
                        }
                    }

                    return $this->renderAjax('_edit-student-subject-group', [
                        'model' => $model,
                        'groups' => $groups
                    ]);
                }
            }

            return $this->redirect(['curriculum/student-register']);
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'subject' => $subject,
            'subjectProvider' => $subjectProvider,
            'studentSubject' => $studentSubject,
            'studentSubjectProvider' => $studentSubjectProvider,
            'faculty' => $faculty,
            'students' => $students,
        ]);

    }

    public function actionToRegister()
    {
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('subjects') && Yii::$app->request->post('_curriculum') && Yii::$app->request->post('_education_year') && Yii::$app->request->post('_semester') && Yii::$app->request->post('_group')) {
            $selection = (array)Yii::$app->request->post('selection');
            $selection2 = (array)Yii::$app->request->post('subjects');
            $curriculum = Yii::$app->request->post('_curriculum');
            $education_year = Yii::$app->request->post('_education_year');
            $semester = Yii::$app->request->post('_semester');
            $group = Yii::$app->request->post('_group');
            $students = array();
            foreach ($selection as $id) {
                $model = EStudentMeta::findOne((int)$id);
                $students [$model->_student] = $model->_student;
            }
            $subjects = array();
            $subject_selectives = array();
            foreach ($selection2 as $id) {
                $model = ECurriculumSubject::findOne((int)$id);
                $subjects [$model->_subject] = $model->_subject;
                if ($model->in_group > 0) {
                    $groups = ECurriculumSubject::find()
                        ->select(['_subject'])
                        ->where(['in_group' => $model->in_group, '_semester' => $model->_semester, '_curriculum' => $model->_curriculum])
                        ->column();

                    $subject_selectives [$model->_subject] = $groups;
                }
            }
            //print_r($subject_selectives[]);
            foreach ($students as $student) {
                foreach ($subjects as $subject) {
                    try {
                        if (isset($subject_selectives[$subject])) {
                            if (in_array(@$subject, @$subject_selectives[@$subject])) {
                                $old = EStudentSubject::find()
                                    ->select('_subject')
                                    ->where([
                                        '_curriculum' => $curriculum,
                                        '_education_year' => $education_year,
                                        '_semester' => $semester,
                                        '_student' => $student,
                                        '_subject' => $subject_selectives[$subject],
                                        'active' => EStudentSubject::STATUS_ENABLE,

                                    ])
                                    ->groupBy(['_subject'])
                                    ->count();
                                if ($old >= 1) {
                                    $this->addError(__('Student have been already registered for this selective subject'));
                                    return $this->redirect(Yii::$app->request->referrer);
                                }
                            }
                        }


                        $model = new EStudentSubject();
                        $model->scenario = EStudentSubject::SCENARIO_INSERT;
                        $model->_student = $student;
                        $model->_subject = $subject;
                        $model->_curriculum = $curriculum;
                        $model->_group = $group;
                        $model->_education_year = $education_year;
                        $model->_semester = $semester;
                        $model->position = 0;
                        if ($model->save()) {
                            $this->addSuccess(
                                __('Selected students for subject is successfully registered.')
                            );

                        } else {
                            $e2 = new Exception();
                            if ($e2->getCode() == 0) {
                                $this->addError(__('Student have been already registered for this subject'));
                            } else {
                                $this->addError($e2->getMessage());
                            }
                        }


                    } catch (Exception $e) {
                        $this->addError($e->getMessage());
                    }
                }
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @resource curriculum/subject-group-delete
     */
    public function actionSubjectGroup($id = false)
    {
        $this->activeMenu = 'subjects';
        $model = $id ? $this->findSubjectGroupModel($id) : new SubjectGroup();
        $model->scenario = SubjectGroup::SCENARIO_CREATE;

        if ($this->get('delete') && $this->canAccessToResource('curriculum/subject-group-delete')) {
            if ($issue = $model->anyIssueWithDelete()) {
                $this->addError($issue);
                return $this->redirect(['subject-group', 'id' => $model->code]);
            } else {
                $this->addSuccess(__('Subject Group [{code}] deleted successfully', ['code' => $model->code]));
                return $this->redirect(['subject-group']);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Subject Group [{code}] updated successfully', ['code' => $model->code]));
            } else {
                $this->addSuccess(__('Subject Group [{code}] created successfully', ['code' => $model->code]));
            }
            $model = new SubjectGroup();
            $model->scenario = SubjectGroup::SCENARIO_CREATE;
        }

        $searchModel = new SubjectGroup();

        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionScheduleInfoView()
    {
        $searchModel = new ESubjectSchedule();
        $dataProvider = $searchModel->search_info($this->getFilterParams());
        $dataProvider->query->select('COUNT(e_subject_schedule.id) as count_lesson,_group as _group,e_subject_schedule._education_year as _education_year,_semester,_curriculum');
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear() ? EducationYear::getCurrentYear()->code : date('Y');
            $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => $searchModel->_education_year]);
        }

        $faculty = "";
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
                $dataProvider->query->andFilterWhere(['e_curriculum._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $dataProvider->query->groupBy(['_group', 'e_subject_schedule._education_year', '_semester', '_curriculum']);
        if (isset($_POST['btn'])) {
            return $this->redirect(['curriculum/schedule',
                'FilterForm[_curriculum]' => $searchModel->_curriculum,
                'FilterForm[_education_year]' => $searchModel->_education_year,
                'FilterForm[_semester]' => $searchModel->_semester,
                'FilterForm[_group]' => $searchModel->_group]);
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionScheduleView()
    {
        $searchModel = new FilterForm();
        $tables = array();
        if ($searchModel->load(Yii::$app->request->get()) && $searchModel->_semester && $searchModel->_group) {
            $time_tables = ESubjectSchedule::find()
                ->where([
                    '_curriculum' => $searchModel->_curriculum,
                    '_education_year' => $searchModel->_education_year,
                    '_semester' => $searchModel->_semester,
                    '_group' => $searchModel->_group
                ])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                ->all();
            Url::remember(Yii::$app->request->url, 'schedule');
            //$draggable = "fc-draggable fc-resizable";
            foreach ($time_tables as $table) {
                $event = new \yii2fullcalendar\models\Event();
                $event->id = $table->id;
                $event->title = ($table->lessonPair ? $table->lessonPair->period : '') . ' | ' . $table->trainingType->name;
                if (@$table->additional != "")
                    $event->nonstandard = '<b>' . @$table->subject->name . '</b>' . '<br>' . @$table->employee->fullName . '<br>' . __('Room') . ': ' . @$table->auditorium->name . '<br>' . @$table->additional;
                else
                    $event->nonstandard = '<b>' . @$table->subject->name . '</b>' . '<br>' . @$table->employee->fullName . '<br>' . __('Room') . ': ' . @$table->auditorium->name;
                $event->start = Yii::$app->formatter->asDate($table->lesson_date, 'php:Y-m-d');
                //$event->backgroundColor = '#0d6aad';
                //$event->backgroundColor = '#0d6aad';
                //$event->borderColor = 'red';
                $tables[] = $event;
            }
        }
        return $this->renderView([
            //'dataProvider' => $searchModel->search($this->getFilterParams()),
            //'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'time_tables' => $tables,
            // 'model' => $model,
        ]);
    }

    public function actionExamScheduleInfoView()
    {
        $searchModel = new ESubjectExamSchedule();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->select('COUNT(e_subject_exam_schedule.id) as count_lesson,_group,e_subject_exam_schedule._education_year,_semester,_curriculum');
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear() ? EducationYear::getCurrentYear()->code : date('Y');
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => $searchModel->_education_year]);
        }

        $faculty = "";
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
                $dataProvider->query->andFilterWhere(['e_curriculum._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $dataProvider->query->groupBy(['_group', 'e_subject_exam_schedule._education_year', '_semester', '_curriculum']);
        if (isset($_POST['btn'])) {
            return $this->redirect(['curriculum/exam-schedule',
                'FilterForm[_curriculum]' => $searchModel->_curriculum,
                'FilterForm[_education_year]' => $searchModel->_education_year,
                'FilterForm[_semester]' => $searchModel->_semester,
                'FilterForm[_group]' => $searchModel->_group]);
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionExamScheduleView()
    {
        $searchModel = new FilterForm();
        $tables = array();
        if ($searchModel->load(Yii::$app->request->get()) && $searchModel->_semester && $searchModel->_group) {
            $time_tables = ESubjectExamSchedule::find()
                ->where([
                    '_curriculum' => $searchModel->_curriculum,
                    '_education_year' => $searchModel->_education_year,
                    '_semester' => $searchModel->_semester,
                    '_group' => $searchModel->_group
                ])
                ->orderBy(['exam_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                ->all();
            Url::remember(Yii::$app->request->url, 'exam-schedule');
            //$draggable = "fc-draggable fc-resizable";
            foreach ($time_tables as $table) {
                $event = new \yii2fullcalendar\models\Event();
                $event->id = $table->id;
                $event->title = $table->lessonPair->period . ' | ' . strtoupper($table->examType->name) . ' | ' . strtoupper($table->finalExamType->name);
                $event->nonstandard = '<b>' . @$table->subject->name . '</b><br>' . @$table->employee->fullName . '<br>' . __('Room') . ': ' . @$table->auditorium->name;
                $event->start = Yii::$app->formatter->asDate($table->exam_date, 'php:Y-m-d');
                // $event->backgroundColor = '#0d6aad';
                //$event->borderColor = 'red';
                $tables[] = $event;
            }
        }
        return $this->renderView([
            //'dataProvider' => $searchModel->search($this->getFilterParams()),
            //'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'time_tables' => $tables,
            // 'model' => $model,
        ]);
    }

    protected function findCurriculumModel($id)
    {
        if (($model = ECurriculum::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findEducationYearModel($id)
    {
        if (($model = EducationYear::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSemesterModel($id)
    {
        if (($model = Semester::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findCurriculumSemesterModel($curriculum, $code)
    {
        if (($model = Semester::findOne(['_curriculum' => $curriculum, 'code' => $code])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findCurriculumSubjectModel($id)
    {
        if (($model = ECurriculumSubject::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findLessonPairModel($id)
    {
        if (($model = LessonPair::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findGroupModel($id)
    {
        if (($model = EGroup::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSubjectScheduleModel($id)
    {
        if (($model = ESubjectSchedule::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSubjectExamScheduleModel($id)
    {
        if (($model = ESubjectExamSchedule::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSubjectGroupModel($id)
    {
        if (($model = SubjectGroup::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }


}
