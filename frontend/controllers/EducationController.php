<?php


namespace frontend\controllers;

use common\components\Config;
use common\models\archive\EGraduateQualifyingWork;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use frontend\models\academic\StudentDiploma;
use frontend\models\archive\AcademicRecord;
use frontend\models\curriculum\StudentFinalExam;
use frontend\models\curriculum\SubjectTaskActivity;
use frontend\models\curriculum\SubjectTaskStudent;
use frontend\models\system\StudentMeta;
use Yii;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectResource;
use common\models\system\classifier\TrainingType;
use frontend\models\curriculum\StudentAttendance;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\StudentExam;
use frontend\models\curriculum\StudentPerformance;
use frontend\models\curriculum\StudentSchedule;
use frontend\models\curriculum\SubjectResource;
use frontend\models\curriculum\SubjectTask;
use frontend\models\curriculum\SubjectTopic;
use yii\helpers\Url;
use yii\web\Response;

class EducationController extends FrontendController
{
    public $activeMenu = 'education';

    public function actionTimeTable()
    {
        $searchModel = new StudentSchedule();

        return $this->renderView([
            'searchModel' => $searchModel,
        ]);
    }


    public function actionExamTable()
    {
        $searchModel = new StudentExam();

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester()),
        ]);
    }

    public function actionAttendance()
    {
        $searchModel = new StudentAttendance();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester(), $this->getFilterParams()),
        ]);
    }

    public function actionCurriculum()
    {
        if ($subject = $this->get('subject')) {
            if ($model = ECurriculumSubject::findOne(['id' => $subject, '_curriculum' => $this->_user()->meta->_curriculum])) {
                return $this->renderPartial('curriculum_subject', [
                    'model' => $model,
                ]);
            }
        }

        return $this->renderView([
        ]);
    }


    public function actionPerformance()
    {
        $searchModel = new StudentPerformance();

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getFilterParams()),
        ]);
    }

    public function actionSubjects()
    {
        return $this->renderView([
        ]);
    }

    public function actionResources()
    {
        $searchModel = new SubjectTask();
        $dataProvider = null;
        $lesson_dates = null;
        $check = array();
        $trainings = array();

        if ($subject = $this->get('subject')) {
            if ($download = $this->get('download')) {
                $file = $this->get('file', -1);
                if ($resource = ESubjectResource::findOne($download)) {
                    if (is_array($resource->filename)) {
                        if ($file = @$resource->filename[$file]) {
                            $filePath = Yii::getAlias('@static/uploads/') . $file['path'];
                            if (file_exists($filePath)) {
                                return Yii::$app->response->sendFile($filePath, $file['name']);
                            }
                        }
                    }
                }
                //return $this->redirect(['education/resources', 'subject' => $subject]);
            }

            if ($subject = ECurriculumSubject::getByCurriculumSemesterSubject($this->_user()->meta->_curriculum, $this->getSelectedSemester()->code, $subject)) {
                $trainings = ECurriculumSubjectDetail::getTrainingByCurriculumSemesterSubject($subject->_curriculum, $subject->_semester, $subject->_subject);
                $searchModel = new SubjectTopic();
                $dataProvider = $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester(), $subject);

                $teachers = ESubjectSchedule::find()
                    ->select(['_employee'])
                    ->where([
                        'active' => ESubjectSchedule::STATUS_ENABLE,
                        '_group' => $this->_user()->meta->_group,
                        '_curriculum' => $subject->_curriculum,
                        '_subject' => $subject->_subject,
                        //'_training_type' => TrainingType::TRAINING_TYPE_LECTURE,
                    ])
                    ->column();

                $lesson_dates = ESubjectSchedule::find()
                    ->where([
                        '_education_year' => Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester)->educationYear->code,
                        '_group' => $this->_user()->meta->_group,
                        '_semester' => $subject->_semester,
                        '_subject' => $subject->_subject,
                        //'_training_type' => TrainingType::TRAINING_TYPE_LECTURE,
                        '_employee' => $teachers,
                        'active' => ESubjectSchedule::STATUS_ENABLE,
                    ])
                    ->andFilterWhere(['not', ['_subject_topic' => null]])
                    ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
                    ->all();

                foreach ($dataProvider->getModels() as $item) {
                    foreach ($lesson_dates as $schedule) {
                        if ($item->id == $schedule->_subject_topic)
                            $check[$item->id] = Yii::$app->formatter->asDate($schedule->lesson_date, 'php:Y-m-d');
                    }
                }
            }
        }

        return $this->renderView([
            'subject' => $subject,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lesson_dates' => $lesson_dates,
            'check' => $check,
            'trainings' => $trainings,
        ]);
    }

    public function actionTasks()
    {
        /**
         * @var $task SubjectTaskStudent
         */
        $download = $this->get('download', -1);
        $file = $this->get('file');
        if ($download !== -1) {
            if ($task = SubjectTaskStudent::findOne($this->get('task'))) {
                if (is_array($task->subjectTask->filename)) {
                    $files = $task->subjectTask->filename;
                    if (isset($files[$download])) {
                        $file = Yii::getAlias('@static/uploads/') . $files[$download]['path'];

                        if (file_exists($file)) {
                            return Yii::$app->response->sendFile($file, $files[$download]['name']);
                        }
                    }
                }
            }
            return $this->goHome();
        }
        $searchModel = new SubjectTask();
        $dataProvider = null;
        $task_type = $this->get('task_type');
        $prev_url = null;

        if ($subject = $this->get('subject')) {
            if ($subject = ECurriculumSubject::getByCurriculumSemesterSubject($this->_user()->meta->_curriculum, $this->getSelectedSemester()->code, $subject)) {
                $training_type = $this->get('training_type');
                // $task_type = $this->get('task_type');

                //$dataProvider = $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester(), $subject, $training_type, $task_type);
            }
        }
        if ($task = $this->get('task')) {
            if ($task = SubjectTaskStudent::findOne(['_subject_task' => $task, '_curriculum' => $this->_user()->meta->_curriculum, '_student' => $this->_user()->meta->_student, 'final_active' => 1])) {

                if ($file = $this->get('file')) {
                    if ($studentSubmit = SubjectTaskActivity::findOne(['id' => $file, '_student' => $this->_user()->id])) {
                        if (is_array($studentSubmit->filename)) {
                            $files = $studentSubmit->filename;
                            $file = Yii::getAlias('@static/uploads/') . $files['path'];

                            if (file_exists($file)) {
                                return Yii::$app->response->sendFile($file, $files['name']);
                            }
                        }
                    }
                    return $this->goHome();
                }

                $minimum_procent = $task->curriculum->markingSystem->minimum_limit;

                $min_border = $minimum_procent * $task->subjectTask->max_ball/100;
                @$marked = EStudentTaskActivity::getMarkBySubjectTaskStudent($task->_subject_task, $task->_student)->mark;

                $attempt = $task->attempt_count;
                $model = new EStudentTaskActivity();
                $model->scenario = EStudentTaskActivity::SCENARIO_CREATE_FOR_STUDENT;
                //$model->comment = htmlentities($model->comment);
                if ($attempt == $task->subjectTask->attempt_count) {
                    $this->addError(__('The limit of the attempt counts for this topics has been exceeded'));
                } elseif ($marked > $min_border) {
                    $this->addError(__('The student for this topics has been marked'));
                } elseif (!$task->canSubmitTask()) {
                    $this->addError(__('The deadline for the task is over'));
                } else {
                    if ($model->load(Yii::$app->request->post())) {
                        $model->_subject_task_student = $task->id;
                        $model->comment = strip_tags($model->comment);
                        $model->_subject_task = $task->_subject_task;
                        $model->_curriculum = $task->_curriculum;
                        $model->_subject = $task->_subject;
                        $model->_training_type = $task->_training_type;
                        $model->_education_year = $task->_education_year;
                        $model->_semester = $task->_semester;
                        $model->_student = $this->_user()->id;
                        $model->active = EStudentTaskActivity::STATUS_DISABLE;
                        $model->_final_exam_type = $task->_final_exam_type;
                        $model->send_date = date('Y-m-d H:i:s', time());
                        $model->attempt_count = 1;
                        if ($model->save()) {
                            $task->_task_status = SubjectTask::TASK_STATUS_PASSED;
                            $task->attempt_count = $attempt + 1;
                            $task->save(false);
                            $this->addSuccess(__('Item [{code}] added to Subject Task', ['code' => $task->_subject_task]));
                            $this->redirect(['education/tasks', 'subject' => $task->_subject]);
                        }
                    }
                }
                $searchModelActivity = new SubjectTaskActivity();
                $dataProviderActivity = $searchModelActivity->searchForStudentData($this->_user(), $this->getSelectedSemester(), $task);

                return $this->render('send-answer', [
                    'model' => $model,
                    'task' => $task,
                    'searchModelActivity' => $searchModelActivity,
                    'dataProviderActivity' => $dataProviderActivity,
                    'min_border' => $min_border,
                    'marked' => @$marked,
                ]);
            }
        }

        return $this->renderView([
            'subject' => $subject,
            'semester' => $this->getSelectedSemester(),
            'task_type' => $task_type,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTrack($id)
    {
        $studentTopic = StudentTopic::findOne(new ObjectId($id));
        $studentTopic->setScenario('track');
        if ($this->getIsPost()) {
            if ($studentTopic->load($this->post(), '') && $studentTopic->save()) {
                return $this->asJson(['success' => true]);
            }
            return $this->asJson(['success' => false]);
        }
        if ($studentTopic !== null) {
            $studentTopic->updateCounters(['read_time' => 1]);
            $studentTopic->touch('updated_at');
            return $this->asJson(['success' => true]);
        }
        throw new NotFoundHttpException('Not found');
    }

    public function actionAcademicData()
    {

        $records = [];
        $totalAcload = 0;
        $totalPoint = 0;
        $totalRating = 0;
        $totalCredit = 0;
        $totalGpa = 0;
        $totalGrade = 0;
        $isFiveRating = false;
        $isCreditRating = false;

        $theme = EGraduateQualifyingWork::findOne(['_student' => $this->_user()->id]);

        /**
         * @var int $k
         * @var ECurriculumSubject $curriculumSubject
         * @var AcademicRecord $record
         */

        foreach (
            StudentMeta::getStudentSubjects($this->_user()->meta)->andWhere(
                ['<>', 'e_curriculum_subject._rating_grade', RatingGrade::RATING_GRADE_GRADUATE]
            )->orderBy('e_curriculum_subject.position')->all() as $k => $curriculumSubject
        ) {
            $record = $curriculumSubject->getStudentSubjectRecord($this->_user()->id);

                /*if (!$record) {
                    throw new \Exception(sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name));
                }*/
                $point = $record ? sprintf(
                    "%.2f / %.2f",
                    round(((double)$record->total_point * $record->total_acload) / 100),
                    $record->total_point
                ) : '';

                if (@$record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $point = $record ? round($record->total_point) : '';
                } elseif (@$record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT) {
                    $point = $record ? sprintf(
                        "%.2f / %s",
                        round(((double)$record->total_point * $record->total_acload) / 100),
                        round($record->total_point)
                    ) : '';
                }
                $records[] = [
                    'semester' => $curriculumSubject->_semester,
                    'id' => $k + 1,
                    'name' => sprintf(
                        '%s',
                        $curriculumSubject->subject->name
                    ),
                    'subject_type' => sprintf(
                        '%s',
                        $curriculumSubject->subjectType->name
                    ),
                    'acload' => sprintf(
                        "%s",
                        $record ? round($record->total_acload) : round($curriculumSubject->total_acload)
                    ),
                    'credit' => sprintf(
                        "%.1f",
                        $record ? round($record->credit,1) : round($curriculumSubject->credit,1)
                    ),
                    'point' => sprintf(
                        "%s",
                        $point
                    ),

                    'grade' => sprintf(
                        "%s",
                        $record ? round($record->grade) : ''
                    ),

                ];
        }



        $graduateRecords = StudentMeta::getStudentSubjects($this->_user()->meta)->andWhere(
            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE]
        )->orderBy('e_curriculum_subject.position')->all();
        if (count($graduateRecords) > 0) {

            $curriculumSubject = $graduateRecords[0];
            $record = $curriculumSubject->getStudentSubjectRecord($this->_user()->id);
            /*if (!$record) {
                throw new \Exception(sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name));
            }*/
            $mark = sprintf(
                "%.2f",
                $record? round(((double)$record->total_point * $record->total_acload) / 100) : ''
            );
            $grade = sprintf(
                "%.2f",
                $record ? $record->grade : ''
            );
            if ($curriculumSubject->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                $mark = $record ? round($record->total_point) : '';
                $grade = $record ? round($record->total_point) : '';
            }

            $records[] = [
                'semester' => $curriculumSubject? $curriculumSubject->_semester : '',
                'id' => '',
                'name' => sprintf(
                    '%s: <br>%s',
                    __('Graduation qualification work (master\'s dissertation)'),
                    $theme->work_name
                ),
                'subject_type' => sprintf(
                    '%s',
                    $curriculumSubject->subjectType->name
                ),
                'acload' => sprintf(
                    "%s",
                    $record ? round($record->total_acload) : $curriculumSubject->total_acload
                ),
                'credit' => '',
                'point' => $mark,
                'grade' => $grade
            ];
        }
        return $this->renderView([
            'records' => $records
        ]);
    }

}