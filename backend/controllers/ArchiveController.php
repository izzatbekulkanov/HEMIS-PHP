<?php

namespace backend\controllers;

use common\components\Config;
use common\components\hemis\HemisApiError;
use common\components\hemis\sync\DiplomaBlankUpdater;
use common\models\academic\EDecree;
use common\models\archive\EAcademicInformation;
use common\models\archive\EAcademicInformationData;
use common\models\archive\EAcademicRecord;
use common\models\archive\ECertificateCommittee;
use common\models\archive\ECertificateCommitteeMember;
use common\models\archive\EDiplomaBlank;
use common\models\archive\EGraduateQualifyingWork;
use common\models\archive\EStudentAcademicInformationDataMeta;
use common\models\archive\EStudentAcademicSheetMeta;
use common\models\archive\EStudentDiploma;
use common\models\archive\EStudentEmployment;
use common\models\archive\EStudentEmploymentMeta;
use common\models\archive\EStudentReference;
use common\models\archive\EStudentTranscriptMeta;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\finance\EStudentContract;
use common\models\performance\EPerformance;
use common\models\structure\EUniversity;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\ExpelReason;
use common\models\system\classifier\Gender;
use common\models\system\classifier\GraduateFieldsType;
use common\models\system\classifier\GraduateInactiveType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\StudentStatus;
use common\models\system\job\ContractListFileGenerateJob;
use common\models\system\job\EmloymentListFileGenerateJob;
use common\models\system\job\StudentDiplomaListFileGenerateJob;
use frontend\models\academic\StudentDiploma;
use kartik\mpdf\Pdf;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Yii;
use yii\base\BaseObject;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\queue\redis\Queue;
use yii\web\Response;
use yii\widgets\ActiveForm;


/**
 * HEducationFormController implements the CRUD actions for HEducationForm model.
 */
class ArchiveController extends BackendController
{
    public $activeMenu = 'archive';

    public function actionDiplomaList()
    {
        /**
         * @var $model EStudentContract
         */
        if ($id = $this->get('diploma')) {
            if ($model = StudentDiploma::findOne(['id' => $id])) {
                if (file_exists($model->getDiplomaFilePath())) {
                    return Yii::$app->response->sendFile(
                        $model->getDiplomaFilePath(),
                        "diplom-{$model->student->student_id_number}.pdf"
                    );
                } else {
                    $this->addError(__('Fayl mavjud emas'));
                }
            }
            return $this->redirect(['diploma-list']);
        }

        if ($id = $this->get('supplement')) {
            if ($model = StudentDiploma::findOne(['id' => $id])) {
                if (file_exists($model->getSupplementFilePath())) {
                    return Yii::$app->response->sendFile(
                        $model->getSupplementFilePath(),
                        "ilova-{$model->student->student_id_number}.pdf"
                    );
                } else {
                    $this->addError(__('Fayl mavjud emas'));
                }
            }
            return $this->redirect(['diploma-list']);
        }

        $searchModel = new EStudentDiploma();
        $dataProvider = $searchModel->searchDiplomaList($this->getFilterParams());

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

        if ($this->get('download', false)) {
            $query = $searchModel->searchDiplomaList($this->getFilterParams(), false);
            $limit = 200;
            $cloneQuery = clone $query;
            if ($cloneQuery->count() <= $limit) {
                $file = EStudentDiploma::generateDiplomaListFile($query);
                $content = file_get_contents($file);
                unlink($file);
                return Yii::$app->response->sendContentAsFile(
                    $content,
                    basename($file));
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
                        new StudentDiplomaListFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['archive/diploma-list', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Diplomlar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar saxifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['archive/diploma-list']);
            }
        }

        return $this->renderView(
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * @skipAccess
     */
    public function actionDiplomaAccept($id)
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var EStudentDiploma $model */
        $model = EStudentDiploma::findOne($id);
        if ($model !== null) {
            if ($model->accepted && !$model->published) { // disabling
                $model->updateAttributes(['accepted' => false]);
                $model->refresh();
                $this->addSuccess(__('Diploma [{number}] was accepted successfully', ['number' => $model->diploma_number]), true, false);
            } else { // enabling
                if ($model->canAccept()) {
                    $model->updateAttributes(['accepted' => true]);
                    $model->refresh();
                    $this->addSuccess(__('Diploma [{number}] was accepted successfully', ['number' => $model->diploma_number]), true, false);
                    return [];
                }
                $this->addSuccess(__('Cannot accept [{number}] diploma', ['number' => $model->diploma_number]), true, false);
            }
        }

        return [];
    }

    /**
     * @resource archive/academic-record
     */
    public function actionAcademicRecord($id = false)
    {
        $searchModel = new EPerformance();
        $dataProvider = $searchModel->search_student($this->getFilterParams());
        $dataProvider->query->select(
            'e_performance.id, e_performance._subject, e_performance._student,e_performance._education_year,e_performance._semester, e_performance.grade'
        );
        //$dataProvider->query->andFilterWhere(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST]);
        $dataProvider->query->andFilterWhere(['passed_status' => 1]);
        $dataProvider->query->andFilterWhere(['_exam_type' => ExamType::EXAM_TYPE_OVERALL]);

        $dataProvider->query->andFilterWhere(['send_record_status' => EPerformance::SEND_RECORD_PASSIVE]);
        $faculty = "";
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_meta._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        /*if (empty($searchModel->_education_year) || empty($searchModel->_curriculum) || empty($searchModel->_semester)) {
            $dataProvider->query->andWhere('1 <> 1');
        }*/
        $dataProvider->query->groupBy(
            [
                'e_performance.id',
                'e_performance._subject',
                'e_performance._student',
                'e_performance._education_year',
                'e_performance._semester',
                'e_performance.grade',
            ]
        );

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    /**
     * @resource archive/academic-record-view
     */
    public function actionAcademicRecordView()
    {
        $searchModel = new EAcademicRecord();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if (!$searchModel->_student) {
            $dataProvider->query->andWhere('1 <> 1');
        } else {
            $dataProvider->query->where(
                [
                    'e_academic_record._student' => $searchModel->_student,
                    'e_academic_record._curriculum' => $searchModel->student->meta->_curriculum
                ]
            );
        }
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                //$dataProvider->query->andFilterWhere(['e_student_meta._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $field = _BaseModel::getLanguageAttributeCode('name');

        $dataProvider->query->orderBy(new Expression("e_subject._translations->>'$field' ASC, e_subject.name ASC"));

        $dataProvider->query->groupBy(
            [
                'e_academic_record.id',
                'e_academic_record._subject',
                'e_academic_record._curriculum',
                'e_academic_record._student',
                'e_academic_record._education_year',
                'e_academic_record._semester',
                'e_academic_record.grade',
                'e_subject.name',
                'e_subject._translations'
            ]
        );

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionToRecord()
    {
        if (Yii::$app->request->post('selection')) {
            $selection = (array)Yii::$app->request->post('selection');
            foreach ($selection as $id) {
                $data = EPerformance::findOne((int)$id);
                $model = EAcademicRecord::findOne(
                    [
                        '_student' => $data->_student,
                        '_curriculum' => $data->examSchedule->_curriculum,
                        //'_education_year' => $data->_education_year,
                        '_subject' => $data->_subject,
                        '_semester' => $data->_semester,
                    ]
                );
                if ($model === null) {
                    $model = new EAcademicRecord();
                }
                $five = GradeType::getGradeByCode(
                    $data->examSchedule->curriculum->_marking_system,
                    GradeType::GRADE_TYPE_FIVE
                );
                $four = GradeType::getGradeByCode(
                    $data->examSchedule->curriculum->_marking_system,
                    GradeType::GRADE_TYPE_FOUR
                );
                $three = GradeType::getGradeByCode(
                    $data->examSchedule->curriculum->_marking_system,
                    GradeType::GRADE_TYPE_THREE
                );
                $subject = ECurriculumSubject::getByCurriculumSemesterSubject(
                    $data->examSchedule->_curriculum,
                    $data->_semester,
                    $data->_subject
                );
                $model->_curriculum = $data->examSchedule->_curriculum;
                $model->_education_year = $data->_education_year;
                $model->_semester = $data->_semester;
                $model->_student = $data->_student;
                $model->_subject = $data->_subject;
                $model->_employee = $data->_employee;

                $model->curriculum_name = $data->examSchedule->curriculum->name;
                $model->education_year_name = $data->educationYear->name;
                $semester_name = null;
                if (Semester::getByCurriculumSemester($data->examSchedule->_curriculum, $data->_semester) != null)
                    $semester_name = Semester::getByCurriculumSemester($data->examSchedule->_curriculum, $data->_semester)->name;
                elseif ($data->semester)
                    $semester_name = $data->semester->name;
                else
                    $semester_name = \common\models\system\classifier\Semester::findOne($data->_semester)->name;
                $model->semester_name = $semester_name;
                $model->student_name = $data->student->fullName;
                $model->student_id_number = $data->student->student_id_number;
                $model->subject_name = $data->subject->name;
                $model->employee_name = $data->employee->fullName;

                $model->total_acload = $subject->total_acload;
                $model->credit = $subject->credit;
                $model->total_point = $data->grade;

                if ($data->examSchedule->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $model->grade = $data->grade;
                } else {
                    if ($data->grade >= $five->min_border) {
                        $model->grade = $five->name;
                    } elseif ($data->grade >= $four->min_border) {
                        $model->grade = $four->name;
                    } elseif ($data->grade >= $three->min_border) {
                        $model->grade = $three->name;
                    }
                }

                //$model->grade = $data->grade;

                $data->send_record_status = EPerformance::SEND_RECORD_ACTIVE;
                $data->send_record_date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d H:i:s');
                $model->save(false);
                $data->save(false);
                $this->addSuccess(__('Information is sended successfully to academic record'));
                /* if($model->save()) {
                     $data->save();
                 }*/
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionEmployment()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_MARKETING) {
            $this->addInfo(
                __('This page is for the marketing profile only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentEmployment();
        $dataProvider = $searchModel->searchContingent($this->getFilterParams());

        $department = false;

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
            //$searchModel = new EStudentMeta();
            $query = $searchModel->searchContingent($this->getFilterParams(), $department, false);
            $query->andFilterWhere(['_education_year' => $education_year]);
            $countQuery = clone $query;
            $limit = 800;
            if ($countQuery->count() <= $limit) {
                $fileName = EStudentEmployment::generateEmploymentDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {

                $queue1 = Yii::$app->queue;
                $queue = Yii::$app->queueFile;
                $prefix = $queue1->channel;
                if ($queue1->redis->llen("$prefix.waiting") == 0) {
                    $queue = $queue1;
                }

                if ($queue
                    ->ttr(900)
                    ->push(
                        new EmloymentListFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'education_year' => $education_year,
                                'department' => $department,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['archive/employment', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Bitiruvchilar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar sahifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['archive/employment']);
            }
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @resource archive/employment-delete
     */
    public function actionEmploymentEdit($student = false, $employment = false, $download = false, $delete = false)
    {
        /**
         * @var $meta EStudentMeta
         */
        if ($this->_user()->role->code !== AdminRole::CODE_MARKETING) {
            $this->addInfo(
                __('This page is for the marketing profile only.')
            );
            return $this->goHome();
        }
        /**
         * @var EStudentEmployment $model
         */
        $model = null;
        $meta = null;

        $user = $this->_user();
        $department = false;

        $status = $this->get('status');

        $data = null;
        if ($status) {
            $result = [];
            try {
                $selected = null;
                if ($employment != 0) {
                    if ($exist_model = EStudentEmployment::findOne(['id' => $employment])) {
                        $selected = $exist_model->_graduate_fields_type;
                    }
                }
                $list = "";
                $out = "";
                if ($status == EStudentEmployment::EMPLOYMENT_STATUS_MASTER || $status == EStudentEmployment::EMPLOYMENT_STATUS_ORDINATOR || $status == EStudentEmployment::EMPLOYMENT_STATUS_DOCTORATE || $status == EStudentEmployment::EMPLOYMENT_STATUS_SECOND_HIHGER || $status == EStudentEmployment::EMPLOYMENT_STATUS_RETRAINING) {
                    $list = GraduateFieldsType::getFieldTypeOptions();
                } elseif ($status == EStudentEmployment::EMPLOYMENT_STATUS_EMPLOYEE) {
                    $list = GraduateFieldsType::getFieldTypeOptions('other');
                }
                foreach ($list as $item) {
                    if ($item->code == $selected) {
                        $out .= "<option value='" . $item->code . "' selected>" . $item->name . "</option>";
                    } else {
                        $out .= "<option value='" . $item->code . "'>" . $item->name . "</option>";
                    }


                }
                $result['success'] = true;
                $result['_graduate_fields_type'] = $out;
            } catch (\Exception $e) {
                $result['success'] = false;
                $result['manual'] = false;
                /*$result['error'] =
                    'Xatolik vujudga keldi.'
                ;*/
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }
        if ($employment) {
            $model = EStudentEmployment::findOne(['id' => $employment]);
            $meta = EStudentEmploymentMeta::findOne([
                '_student' => $model->_student,
                '_student_status' => StudentStatus::STUDENT_TYPE_GRADUATED
            ]);
            if ($delete && $this->canAccessToResource('archive/employment-delete')) {
                if ($model->tryToDelete(
                    function () use ($model) {
                        return $this->syncModelToApi($model, true);
                    },
                    $message
                )) {
                    if ($meta)
                        $meta->updateAttributes(['employment_registration' => EStudentMeta::STATUS_REGISTRATION_OFF]);
                    $this->addSuccess(__('Employment {id} deleted successfully', ['id' => $meta->student->getFullName()]));
                    return $this->redirect(['employment']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                    return $this->redirect(['employment-edit', 'employment' => $model->id]);
                }
            }

            if ($download) {

            }
        }

        if ($student) {
            if ($meta = EStudentEmploymentMeta::findOne([
                'id' => $student,
                //'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_GRADUATED
            ])) {


                $model = EStudentEmployment::findOne(['_student' => $meta->_student]);
                if ($model === null) {
                    $model = new EStudentEmployment();
                }
                if ($model->isNewRecord || $model->_education_year === null) {
                    $model->_education_year = $meta->_education_year;
                    $model->_education_type = $meta->_education_type;
                    $model->_education_form = $meta->_education_form;
                    $model->_level = $meta->_level;
                    $model->_gender = $meta->student->_gender;
                    $model->_department = $meta->_department;
                    $model->_specialty = $meta->_specialty_id;
                    $model->_payment_form = $meta->_payment_form;
                    $model->_group = $meta->_group;
                    $model->_student = $meta->_student;
                    $model->student = $meta->student->fullName;
                    $model->student_id_number = $meta->student->student_id_number;
                }
            }
        }

        if ($model) {
            //$model->scenario = EAcademicInformation::SCENARIO_INSERT;

            if ($this->checkModelToApi($model)) {
                return $this->redirect(['employment-edit', 'employment' => $employment, 'student' => $student]);
            }

            if ($model->load($this->post())) {
                if ($model->_employment_status !== EStudentEmployment::EMPLOYMENT_STATUS_REASON)
                    $model->_graduate_inactive = null;
                else {
                    $model->company_name = null;
                    $model->position_name = null;
                    $model->employment_doc_number = null;
                    $model->employment_doc_number = null;
                    $model->employment_doc_date = null;
                    $model->start_date = null;
                    $model->_graduate_fields_type = null;
                    $model->workplace_compatibility = null;
                }
                if ($model->employment_doc_date == null)
                    $model->employment_doc_date = null;
                try {

                    if ($model->save()) {
                        $this->syncModelToApi($model);

                        if ($meta)
                            $meta->updateAttributes(['employment_registration' => EStudentMeta::STATUS_REGISTRATION_ON]);

                        $this->addSuccess(__(
                            $employment ?
                                'Employment for student {name} has been updated' :
                                'Employment for student {name} has been created',
                            [
                                'name' => $model->student
                            ]));
                        return $this->redirect(['employment-edit', 'employment' => $model->id]);
                    } else {
                        $this->addError($model->getOneError());
                    }
                } catch (\Exception $exception) {
                    echo $exception->getTraceAsString();
                    die;
                    $this->addError($exception->getMessage());
                    return $this->refresh();
                }

            }

            return $this->render('employment-edit-student', [
                'model' => $model,
                'meta' => $meta,
                'department' => $department,
            ]);
        }

        $searchModel = new EStudentEmploymentMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }
        $dataProvider = $searchModel->searchForEmployment($this->getFilterParams(), $department);
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_student_meta._education_year' => EducationYear::getCurrentYear()->code]);
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionDiploma()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchDiploma($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_GRADUATED]);

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_meta._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * @resource archive/diploma-delete
     */
    public function actionDiplomaEdit($id)
    {
        $model = new EStudentDiploma();
        $student = false;
        if ($id) {
            $student = $this->findStudentMetaModel($id);
            $model = EStudentDiploma::findOne(['_student' => $student->_student]);
            if ($model === null) {
                $model = new EStudentDiploma();
                $model->fillKeysFromStudent($student);
                $model->student_name = $student->student->fullName;
                if ((bool)$this->get('fill', false) === true) {
                    $model->fillFromStudent($student);
                }
            }
        }
        $model->scenario = EStudentDiploma::SCENARIO_INSERT;

        if ($this->get('delete') && $this->canAccessToResource('archive/diploma-delete')) {
            $message = "";
            if ($model->accepted) {
                $this->addError(__('Can not delete accepted diploma'));
                return $this->redirect(['diploma-edit', 'id' => $model->_student]);
            }
            if ($model->tryToDelete(
                function () use ($model, $student) {
                    $student->updateAttributes(['diploma_registration' => EStudentMeta::STATUS_REGISTRATION_OFF]);
                    if ($this->syncModelToApi($model, true)) {
                        if ($model->diplomaBlank !== null) {
                            $this->syncModelToApi($model->diplomaBlank);
                        }
                        return true;
                    }
                    return false;
                },
                $message
            )) {
                $this->addSuccess(__('Diploma [{id}] deleted successfully', ['id' => $model->student_name]));
            } else {
                if ($message) {
                    $this->addError($message);
                }
            }

            return $this->redirect(['diploma']);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['diploma-edit', 'id' => $model->_student]);
        }
        $diplomaBlank = $model->diplomaBlank;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->isNewRecord) {
                $model->fillKeysFromStudent($student);
                if ($this->get('fill', false)) {
                    if (Yii::$app->language === Config::LANGUAGE_ENGLISH) {
                        $model->fillTranslationsFromStudent($student);
                    } else {
                        $model->fillTranslationsFromStudent($student, Config::LANGUAGE_ENGLISH);
                    }
                }
            }
            if ($model->accepted) {
                $this->addError(__('Can not save accepted diploma.'));
                return $this->redirect(['diploma-edit', 'id' => $model->_student]);
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $this->syncModelToApi($model);
                    if ($model->diplomaBlank !== null) {
                        $this->syncModelToApi($model->diplomaBlank);
                    }
                    if ($diplomaBlank !== null && $diplomaBlank->number !== $model->diplomaBlank->number) {
                        $diplomaBlank->status = EDiplomaBlank::STATUS_EMPTY;
                        $diplomaBlank->save();
                        $this->syncModelToApi($diplomaBlank);
                    }
                    $this->addSuccess(__('Diploma [{id}] updated successfully', ['id' => $model->diploma_number]));
                    $student->updateAttributes(['diploma_registration' => EStudentMeta::STATUS_REGISTRATION_ON]);
                    if ($transaction !== null) {
                        $transaction->commit();
                    }
                    return $this->redirect(['diploma-edit', 'id' => $model->_student]);
                }
            } catch (\Exception $exception) {
                $transaction->rollBack();
                $this->addError($exception->getMessage(), true);
            }
        }

        if ($this->get('errors', false)) {
            $model->validateTranslations();
            $model->validateSubjectsTranslations();
        }

        return $this->renderView(
            [
                'model' => $model,
                'student' => $student,
            ]
        );
    }

    public function actionDiplomaBlank($id = false)
    {
        if ($this->get('sync') && HEMIS_INTEGRATION) {
            if (HEMIS_INTEGRATION) {
                try {
                    if (($count = DiplomaBlankUpdater::importModels()) !== false) {
                        if ($count === 0) {
                            $this->addInfo(__('No updates'));
                        } else {
                            $this->addSuccess(__('{count} diploma blanks synced', ['count' => $count]));
                        }
                    } else {
                        $this->addError(__('Could not sync diploma blanks'));
                    }
                } catch (HemisApiError $e) {
                    $this->addWarning('HEMIS_ERROR: ' . $e->getMessage());
                } catch (\Exception $e) {
                    $this->addError(__("Ma'lumotni sinxornizatsiya qilishda xatolik yuz berdi"));
                }
            }
            return $this->redirect(['diploma-blank']);
        }
        $model = new EDiplomaBlank();
        $searchModel = new EDiplomaBlank();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if ($id) {
            $model = EDiplomaBlank::findOne($id);
            if ($model === null) {
                $model = new EDiplomaBlank();
            }
        }
        $model->scenario = EDiplomaBlank::SCENARIO_UPDATE;

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['diploma-blank', 'id' => $model->id]);
        }

        if ($id && $model->load(Yii::$app->request->post())) {
            if ($model->diploma !== null) {
                $this->addError(__('Can not update ordered diploma blank'));
                return $this->refresh();
            }
            if ($model->save()) {
                $this->syncModelToApi($model);
                $this->addSuccess(__('Diploma blank [{id}] updated successfully', ['id' => $model->number]));
            }/* else {
                $this->addError($model->getOneError());
            }*/
        }


        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => $model,
            ]
        );
    }

    public function actionDiplomaPrint($id)
    {
        $this->layout = 'diploma';
        /** @var EStudentDiploma $model */
        $model = EStudentDiploma::findOne(['_student' => $id]);
        if ($model === null) {
            $this->notFoundException();
        }
        if (!$model->validateTranslations()) {
            return $this->redirect(['diploma-edit', 'id' => $id, 'errors' => 1]);
        }
        if ($this->get('download', false) && file_exists($model->getDiplomaFilePath())) {
            return Yii::$app->response->sendFile(
                $model->getDiplomaFilePath(),
                'Diplom ' . $model->student_name . '.pdf'
            );
        }
        $content = $this->renderPartial('/diploma/pdf', ['model' => $model]);

        $pdf = new Pdf(
            [
                // set to use core fonts only
                'mode' => Pdf::MODE_UTF8,
                // A4 paper format
                'format' => Pdf::FORMAT_A4,
                // portrait orientation
                'orientation' => Pdf::ORIENT_LANDSCAPE,
                // stream to browser inline
                'destination' => Pdf::DEST_BROWSER,
                'content' => $content,
                'cssFile' => [
                    '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                    '@app/assets/app/css/diploma-pdf.css'
                ],
                // any css to be embedded if required
                'cssInline' => 'body {font-size:14px !important; font-family: "Times New Roman" !important} .title {text-indent:32px !important}',
                // set mPDF properties on the fly
                'options' => ['title' => 'Diploma'],
                'filename' => 'Diplom ' . $model->student_name . '.pdf',
                // call mPDF methods on the fly
                'methods' => [
                    'SetHeader' => [''],
                    'SetFooter' => [''],
                ]
            ]
        );

        //if (!file_exists($model->getDiplomaFilePath())) {
        FileHelper::createDirectory(dirname($model->getDiplomaFilePath()));
        $pdf->output($pdf->content, $model->getDiplomaFilePath(), Pdf::DEST_FILE);
        //}

        return $pdf->render();
    }

    public function actionDiplomaApplicationPrint($id)
    {
        $this->layout = 'diploma';
        /** @var EStudentDiploma $model */
        $model = EStudentDiploma::findOne(['_student' => $id]);
        /** @var EStudentMeta $student */
        $student = $this->findStudentMetaModel($id);
        if ($model === null) {
            $this->notFoundException();
        }
        if (!$model->validateSubjectsTranslations() || !$model->validateTranslations()) {
            return $this->redirect(['diploma-edit', 'id' => $id, 'errors' => 1]);
        }

        if ($this->get('download', false) && file_exists($model->getSupplementFilePath())) {
            return Yii::$app->response->sendFile(
                $model->getSupplementFilePath(),
                'Ilova ' . $model->student_name . '.pdf'
            );
        }

        $records = [];
        $totalAcload = 0;
        $totalPoint = 0;
        $totalRating = 0;
        $totalCredit = 0;
        $totalGpa = 0;
        $totalGrade = 0;
        $isFiveRating = false;
        $isCreditRating = false;
        /**
         * @var int $k
         * @var ECurriculumSubject $curriculumSubject
         * @var EAcademicRecord $record
         */
        foreach (
            EStudentMeta::getStudentSubjects($student)->andWhere(
                ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_SUBJECT]
            )->orderBy('e_curriculum_subject.position')->all() as $k => $curriculumSubject
        ) {
            $record = $curriculumSubject->getStudentSubjectRecord($model->_student);
            if (!$record) {
                throw new \Exception(sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name));
            }
            $point = sprintf(
                "%.2f / %.2f",
                round(((double)$record->total_point * $record->total_acload) / 100),
                $record->total_point
            );
            $totalRating += round(((double)$record->total_point * $record->total_acload) / 100);
            $totalCredit += $record->credit;
            if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                $isFiveRating = true;
                $point = round($record->total_point);
            } elseif ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT) {
                $isCreditRating = true;
                $totalGrade += $record->grade;
                $totalGpa += $record->credit * $record->grade;
                $point = sprintf(
                    "%.2f / %s / %s / %s",
                    round(((double)$record->total_point * $record->total_acload) / 100),
                    round($record->total_point),
                    round($record->credit),
                    round($record->grade)
                );
            }
            $records[] = [
                'id' => $k + 1,
                'name' => sprintf(
                    '%s / %s',
                    $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                    $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                ),
                'acload' => $record->total_acload,
                'point' => $point
            ];
            $totalAcload += $record->total_acload;
            $totalPoint += $record->total_point;
        }
        $total = sprintf("<b>%.2f / %.2f</b>", round($totalRating), (((double)$totalPoint) / count($records)));
        if ($isFiveRating) {
            $total = sprintf(
                "<b>%.2f (%s)</b>",
                $totalPoint / count($records),
                ceil(($totalPoint / count($records)) * 20)
            );
        } elseif ($isCreditRating) {
            $total = sprintf(
                "<b>%.2f / %.2f / %s / %.2f (%s)</b>",
                round($totalRating),
                (((double)$totalPoint) / count($records)),
                round($totalCredit),
                $totalGrade / count($records),
                ceil(($totalGrade / count($records)) * 20)
            );
        }
        $records[] = [
            'id' => '',
            'name' => "<b>JAMI / TOTAL</b>",
            'acload' => "<b>{$totalAcload}</b>",
            'point' => $total
        ];
        $courseRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_COURSE]
        )->orderBy('e_curriculum_subject.position')->all();
        if (count($courseRecords)) {
            $records[] = [
                'id' => '',
                'name' => sprintf(
                    '<b>%s / %s</b>',
                    'Kurs ishlari',
                    'Course papers'
                ),
                'acload' => '',
                'point' => ''
            ];
            foreach ($courseRecords as $k => $curriculumSubject) {
                $record = $curriculumSubject->getStudentSubjectRecord($model->_student);
                if (!$record) {
                    throw new \Exception(
                        sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name)
                    );
                }
                $point = sprintf(
                    "%.2f / %.2f",
                    round(((double)$record->total_point * $record->total_acload) / 100),
                    $record->total_point
                );
                if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $point = round($record->total_point);
                }
                $records[] = [
                    'id' => $k + 1,
                    'name' => sprintf(
                        '%s / %s',
                        $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                        $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                    ),
                    'acload' => '',
                    'point' => $point
                ];
            }
        }
        $practicumRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_PRACTICUM]
        )->orderBy('e_curriculum_subject.position')->all();
        if (count($practicumRecords)) {
            $records[] = [
                'id' => '',
                'name' => sprintf(
                    '<b>%s / %s</b>',
                    'Malakaviy amaliyot',
                    'Qualification practice'
                ),
                'acload' => '',
                'point' => ''
            ];
            foreach ($practicumRecords as $k => $curriculumSubject) {
                $record = $curriculumSubject->getStudentSubjectRecord($model->_student);
                if (!$record) {
                    throw new \Exception(
                        sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name)
                    );
                }
                $point = sprintf(
                    "%.2f / %.2f",
                    round(((double)$record->total_point * $record->total_acload) / 100),
                    $record->total_point
                );
                if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $point = round($record->total_point);
                }
                $records[] = [
                    'id' => $k + 1,
                    'name' => sprintf(
                        '%s / %s',
                        $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                        $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                    ),
                    'acload' => $record->total_acload,
                    'point' => $point
                ];
            }
        }
        $stateRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_STATE]
        )->orderBy('e_curriculum_subject.position')->all();
        if (count($stateRecords)) {
            $records[] = [
                'id' => '',
                'name' => sprintf(
                    '<b>%s / %s</b>',
                    'Yakuniy davlat attestatsiyalari',
                    'Final state attestation'
                ),
                'acload' => '',
                'point' => ''
            ];
            foreach ($stateRecords as $k => $curriculumSubject) {
                $record = $curriculumSubject->getStudentSubjectRecord($model->_student);
                if (!$record) {
                    throw new \Exception(
                        sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name)
                    );
                }
                $point = sprintf(
                    "%.2f / %.2f",
                    round(((double)$record->total_point * $record->total_acload) / 100),
                    $record->total_point
                );
                if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $point = round($record->total_point);
                }
                $records[] = [
                    'id' => $k + 1,
                    'name' => sprintf(
                        '%s / %s',
                        $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                        $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                    ),
                    'acload' => $record->total_acload,
                    'point' => $point
                ];
            }
        }
        $graduateRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE]
        )->orderBy('e_curriculum_subject.position')->all();
        if (count($graduateRecords) > 0) {
            $records[] = [
                'id' => '',
                'name' => sprintf(
                    '<b>%s / %s</b>',
                    'Bitiruv malakaviy ishi (magistrlik dissertatsiyasi)',
                    'Graduation qualification work (master\'s dissertation)'
                ),
                'acload' => '',
                'point' => ''
            ];
            $curriculumSubject = $graduateRecords[0];
            $record = $curriculumSubject->getStudentSubjectRecord($model->_student);
            if (!$record) {
                throw new \Exception(sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name));
            }
            $point = sprintf(
                "%.2f / %.2f",
                round(((double)$record->total_point * $record->total_acload) / 100),
                $record->total_point
            );
            if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                $point = round($record->total_point);
            }

            $records[] = [
                'id' => 1,
                'name' => sprintf(
                    '%s / %s: <br> %s / %s',
                    $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                    $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH),
                    $model->getTranslation(
                        'graduate_qualifying_work',
                        Config::LANGUAGE_UZBEK
                    ),
                    $model->getTranslation(
                        'graduate_qualifying_work',
                        Config::LANGUAGE_ENGLISH
                    )
                ),
                'acload' => $record->total_acload,
                'point' => $point
            ];
        }
        if ($isCreditRating) {
            $gpa = $totalGpa / $totalCredit;
            $records[] = [
                'id' => count($graduateRecords) + 1,
                'name' => 'O‘rtacha ball / GPA (Grade Point Average)',
                'acload' => '',
                'point' => sprintf('%.2f', $gpa),
            ];
        }
        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'tempDir' => Yii::getAlias('@runtime/mpdf')]);
        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->keep_table_proportions = true;

        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->WriteHTML($this->renderPartial('/diploma/application1', ['model' => $model]));
        $mpdf->AddPage();
        $application2 = $this->renderPartial('/diploma/application2', ['model' => $model]);
        $mpdf->WriteHTML($application2);
        $mpdf->AddPage();
        $mpdf->WriteHTML(
            $this->renderPartial(
                '/diploma/application3',
                ['model' => $model, 'records' => array_slice($records, 0, 50)]
            )
        );
        $mpdf->AddPage();
        $mpdf->WriteHTML(
            $this->renderPartial('/diploma/application4', ['model' => $model, 'records' => array_slice($records, 50)])
        );

        //if (!file_exists($model->getSupplementFilePath())) {
        FileHelper::createDirectory(dirname($model->getSupplementFilePath()));
        $mpdf->Output($model->getSupplementFilePath(), Pdf::DEST_FILE);
        //}
        return $mpdf->Output('Ilova ' . $model->student_name . '.pdf', 'I');
    }

    public function actionAccreditation()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchAccredication($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['e_student_meta.active' => true]);
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);

        if (!$searchModel->_group) {
            $dataProvider->query->andWhere('1 <> 1');
        }
        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_meta._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($code = $this->get('code')) {
            $studentMeta = EStudentMeta::findOne(['id' => $code]);
            if ($studentMeta->student !== null) {
                if ($this->get('plan')) {
                    $query = EStudentMeta::getStudentSubjects($studentMeta, false, false);
                }
                if ($this->get('marked')) {
                    $query = EStudentMeta::getMarkedSubjects($studentMeta);
                }

                if ($this->get('diff')) {
                    $plan = ArrayHelper::map(
                        EStudentMeta::getStudentSubjects($studentMeta)->all(),
                        '_subject',
                        '_subject'
                    );
                    $marked_list = [];
                    if (EStudentMeta::getMarkedSubjects($studentMeta, true)) {
                        $marked = EStudentMeta::getMarkedSubjects($studentMeta);
                        foreach ($marked->all() as $mark) {
                            $marked_list[$mark->_subject] = $mark->_subject;
                        }
                        // print_r($marked_list);
                    }
                    $query = EStudentMeta::getStudentSubjects($studentMeta, $marked_list, false);
                }
                $field = _BaseModel::getLanguageAttributeCode('name');

                /*$query->joinWith('subject')->addSelect(['e_subject.id', "e_subject._translations", 'e_subject.name'])->orderBy(
                    new Expression("e_subject._translations->>'$field' ASC, e_subject.name ASC")
                );*/

                $dataProvider = new ArrayDataProvider(
                    [
                        'allModels' => $query->all(),
                        'pagination' => [
                            'pageSize' => 100,
                        ],
                        'sort' => [
                            'defaultOrder' => [
                                "subject.name" => SORT_ASC,
                                //'e_subject.name' => SORT_ASC,
                            ],
                            'attributes' => [
                                'subject.name',
                                //"subject._translations"
                            ]
                        ],
                    ]
                );
            }

            return $this->renderAjax(
                'curriculum-subject-list',
                [
                    'dataProvider' => $dataProvider,
                    'query' => $query,
                ]
            );
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionAccreditationView(int $id)
    {
        /** @var EStudentMeta $model */
        if (($model = EStudentMeta::findOne($id)) === null) {
            $this->notFoundException();
        }
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties && Yii::$app->user->identity->employee->deanFaculties->id !== $model->_department) {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goBack();
            }
        }

        if ($this->get('download', false)) {
            $fileName = $model->generateAccreditationResult();

            return Yii::$app->response->sendFile($fileName, basename($fileName));
        }

        if ($this->get('record', false)) {
            $record = EAcademicRecord::findOne($this->get('record'));
            if ($record && $this->get('delete', false)) {
                try {
                    if ($record->delete()) {
                        $this->addSuccess(__('Student academic record deleted successfully.'));
                    }
                } catch (\Throwable $exception) {
                    $this->addError($exception->getMessage());
                }
                return $this->redirect(['accreditation-view', 'id' => $id]);
            }
        }

        if ($this->get('rating')) {
            /** @var ECurriculumSubject $subject */
            $subject = ECurriculumSubject::findOne($this->get('subject'));
            $five = GradeType::getGradeByCode(
                $subject->curriculum->_marking_system,
                GradeType::GRADE_TYPE_FIVE
            );
            $four = GradeType::getGradeByCode(
                $subject->curriculum->_marking_system,
                GradeType::GRADE_TYPE_FOUR
            );
            $three = GradeType::getGradeByCode(
                $subject->curriculum->_marking_system,
                GradeType::GRADE_TYPE_THREE
            );
            $student = $model->student;
            if ($model->getSubjects()->andFilterWhere(['e_student_subject._subject' => $subject->_subject])->exists() === false) {
                $this->addError(
                    __(
                        'Talaba "{student}"ga {subject} fani biriktirilmagan',
                        ['student' => $student->getFullName(), 'subject' => $subject->subject->name]
                    )
                );
                return $this->redirect(['accreditation-view', 'id' => $id]);
            }
            if ($this->get('record', false)) {
                $record = EAcademicRecord::findOne($this->get('record'));
                if ($record->load($this->post())) {
                    if ($subject->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                        $record->grade = $record->total_point;
                    } else {
                        if ($record->total_point >= $five->min_border) {
                            $record->grade = $five->name;
                        } elseif ($record->total_point >= $four->min_border) {
                            $record->grade = $four->name;
                        } elseif ($record->total_point >= $three->min_border) {
                            $record->grade = $three->name;
                        }
                    }
                    $record->save();
                    $this->addSuccess(
                        __(
                            '"{grade}" grade for {subject} added successfully',
                            ['grade' => $record->total_point, 'subject' => $record->subject->name]
                        )
                    );
                    return $this->redirect(['archive/accreditation-view', 'id' => $id]);
                }
            } else {
                $record = EAcademicRecord::findOne(
                    [
                        '_student' => $student->id,
                        '_curriculum' => $model->_curriculum,
                        //'_education_year' => $model->_education_year,
                        '_subject' => $subject->_subject,
                        '_semester' => $subject->_semester,
                    ]
                );
                if ($record === null) {
                    $record = new EAcademicRecord();
                }
                $record->subject_name = $subject->subject->name;
                $record->total_acload = $subject->total_acload;
                $record->credit = $subject->credit;

                if ($record->load($this->post())) {
                    $record->_curriculum = $model->_curriculum;
                    $record->_education_year = $model->_education_year;
                    $record->_student = $student->id;
                    $record->_subject = $subject->_subject;
                    $record->_semester = $subject->_semester;
                    $record->_employee = $subject->_employee;

                    $record->curriculum_name = $model->curriculum->name;
                    $record->education_year_name = $model->educationYear->name;
                    $semester_name = null;
                    if (Semester::getByCurriculumSemester($model->_curriculum, $subject->_semester) != null)
                        $semester_name = Semester::getByCurriculumSemester($model->_curriculum, $subject->_semester)->name;
                    elseif ($subject->semester)
                        $semester_name = $subject->semester->name;
                    else
                        $semester_name = \common\models\system\classifier\Semester::findOne($subject->_semester)->name;
                    $record->semester_name = $semester_name;
                    $record->student_name = $student->fullName;
                    $record->student_id_number = $student->student_id_number;
                    $record->employee_name = $subject->employee->fullName ?? '';

                    if ($subject->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                        $record->grade = $record->total_point;
                    } else {
                        if ($record->total_point >= $five->min_border) {
                            $record->grade = $five->name;
                        } elseif ($record->total_point >= $four->min_border) {
                            $record->grade = $four->name;
                        } elseif ($record->total_point >= $three->min_border) {
                            $record->grade = $three->name;
                        }
                    }
                    $record->save();
                    $this->addSuccess(
                        __(
                            '"{grade}" grade for {subject} added successfully',
                            ['grade' => $record->total_point, 'subject' => $subject->subject->name]
                        )
                    );

                    return $this->redirect(['archive/accreditation-view', 'id' => $id]);
                }
            }
            return $this->renderAjax(
                'accreditation-rating',
                [
                    'student' => $student,
                    'subject' => $subject,
                    'model' => $record,
                ]
            );
        }

        $query = EStudentMeta::getStudentSubjects($model);

        $subjects = [];
        $courses = [];
        $states = [];
        $practicums = [];
        $graduates = [];

        $models = $query->all();
        foreach ($models as $record) {
            if ($record->_rating_grade === RatingGrade::RATING_GRADE_SUBJECT) {
                $subjects[] = $record;
            } elseif ($record->_rating_grade === RatingGrade::RATING_GRADE_COURSE) {
                $courses[] = $record;
            } elseif ($record->_rating_grade === RatingGrade::RATING_GRADE_STATE) {
                $states[] = $record;
            } elseif ($record->_rating_grade === RatingGrade::RATING_GRADE_PRACTICUM) {
                $practicums[] = $record;
            } elseif ($record->_rating_grade === RatingGrade::RATING_GRADE_GRADUATE) {
                $graduates[] = $record;
            }
        }

        $allModels = array_merge($subjects, $courses, $states, $practicums, $graduates);
        $acloads = [
            RatingGrade::RATING_GRADE_SUBJECT => sprintf(
                '%s / %s',
                array_reduce(
                    $subjects,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->total_acload : 0);
                    },
                    0
                ),
                array_reduce(
                    $subjects,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->credit : 0);
                    },
                    0
                )
            ),
            /*RatingGrade::RATING_GRADE_COURSE => sprintf(
                '%s / %s',
                array_reduce(
                    $courses,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->total_acload: 0);
                    },
                    0
                ),
                array_reduce(
                    $courses,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->credit: 0);
                    },
                    0
                )
            ),
            RatingGrade::RATING_GRADE_STATE => sprintf(
                '%s / %s',
                array_reduce(
                    $states,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->total_acload: 0);
                    },
                    0
                ),
                array_reduce(
                    $states,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->credit: 0);
                    },
                    0
                )
            ),
            RatingGrade::RATING_GRADE_PRACTICUM => sprintf(
                '%s / %s',
                array_reduce(
                    $states,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->total_acload: 0);
                    },
                    0
                ),
                array_reduce(
                    $states,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->credit: 0);
                    },
                    0
                )
            ),
            RatingGrade::RATING_GRADE_GRADUATE => sprintf(
                '%s / %s',
                array_reduce(
                    $states,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->total_acload: 0);
                    },
                    0
                ),
                array_reduce(
                    $states,
                    function ($acc, $item) {
                        return $acc + ($item ? $item->credit: 0);
                    },
                    0
                )
            )*/
        ];
        $balls = [
            RatingGrade::RATING_GRADE_SUBJECT => array_reduce(
                    $subjects,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->total_point : 0);
                    },
                    0
                ) / count($subjects),
            /*RatingGrade::RATING_GRADE_COURSE => array_reduce(
                    $courses,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->total_point : 0);
                    },
                    0
                ) / count($courses),
            RatingGrade::RATING_GRADE_STATE => array_reduce(
                    $states,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->total_point : 0);
                    },
                    0
                ) / count($states),
            RatingGrade::RATING_GRADE_PRACTICUM => array_reduce(
                    $practicums,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->total_point : 0);
                    },
                    0
                ) / count($practicums),
            RatingGrade::RATING_GRADE_GRADUATE => array_reduce(
                    $graduates,
                    function ($acc, $item) use ($model) {
                        $r = $item->getStudentSubjectRecord($model->_student);
                        return $acc + ($r ? $r->total_point : 0);
                    },
                    0
                ) / count($graduates)*/
        ];
        $dataProvider = new ArrayDataProvider(
            [
                'allModels' => $allModels,
                'sort' => [
                    'defaultOrder' => [
                        '_rating_grade' => SORT_ASC,
                        'subject.name' => SORT_ASC
                    ],
                    'attributes' => ['subject.name', '_rating_grade']
                ],
                'pagination' => [
                    'pageSize' => 400,
                ],
            ]
        );

        return $this->renderView(
            [
                'model' => $model,
                'balls' => $balls,
                'acloads' => $acloads,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    public function actionBatchRate()
    {
        $searchModel = new EStudentMeta();

        $dataProvider = $searchModel->searchForBatchRating($this->getFilterParams());
        //$dataProvider->query->andFilterWhere(['e_student_meta.active' => true]);
        $studentSubject = new EStudentSubject();
        $studentSubjectProvider = $studentSubject->search($this->getFilterParams());
        $studentSubjectProvider->query->joinWith('student.meta')->orderBy('e_student.second_name');
        $studentSubjectProvider->query->andFilterWhere(
            [
                'e_student_subject._curriculum' => $searchModel->_curriculum,
                'e_student_subject._semester' => $searchModel->_semestr,
                'e_student_subject._group' => $searchModel->_group
            ]
        );

        $studentSubjectProvider->query->andFilterWhere(
            ['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED]
        );
        if (!$studentSubject->_subject || !$searchModel->_group || !$searchModel->_semestr || !$searchModel->_curriculum || !$searchModel->_education_type || !$searchModel->_education_form) {
            $dataProvider->query->andWhere('1 <> 1');
            $studentSubjectProvider->query->andWhere('1 <> 1');
        } else {
            /*$students = EStudentSubject::find()
                ->select(['_student', '_curriculum', '_education_year', '_semester', '_group', '_subject'])
                ->where(
                    [
                        '_curriculum' => $searchModel->_curriculum,
                        '_education_year' => $searchModel->curriculum->_education_year,
                        '_semester' => $searchModel->_semestr,
                        '_group' => $searchModel->_group,
                        '_subject' => $searchModel->_subject,
                    ]
                )->column();*/
            //$dataProvider->query->orWhere(['_student' => $students]);
        }
        $model = new EAcademicRecord();

        if ($this->get('subject', false)) {
            /** @var ECurriculumSubject $subject */
            $subject = ECurriculumSubject::findOne(
                ['_curriculum' => $this->get('curriculum'), '_subject' => $this->get('subject'), '_semester' => $this->get('semester')]
            );
            $count = 0;
            $five = GradeType::getGradeByCode(
                $subject->curriculum->_marking_system,
                GradeType::GRADE_TYPE_FIVE
            );
            $four = GradeType::getGradeByCode(
                $subject->curriculum->_marking_system,
                GradeType::GRADE_TYPE_FOUR
            );
            $three = GradeType::getGradeByCode(
                $subject->curriculum->_marking_system,
                GradeType::GRADE_TYPE_THREE
            );
            foreach ($this->post('student', []) as $studentId => $grade) {
                /** @var EStudent $student */
                $student = EStudent::findOne($studentId);
                if (!empty($grade) && (int)$grade > 0) {
                    $record = EAcademicRecord::find()->where(
                        [
                            '_curriculum' => $this->get('curriculum'),
                            '_subject' => $subject->_subject,
                            //'_education_year' => $subject->curriculum->_education_year,
                            '_semester' => $this->get('semester'),
                            '_student' => $studentId
                        ]
                    )->one();
                    if ($student->meta->getSubjects()->andFilterWhere(
                            ['e_student_subject._subject' => $subject->_subject]
                        )->exists() === false) {
                        continue;
                    }
                    if (!$record && empty($subject->total_acload)) {
                        continue;
                    } elseif (!$record && (int)$subject->total_acload === 0) {
                        continue;
                    } elseif ($record && empty($record->total_acload)) {
                        continue;
                    } elseif ($record && (int)$record->total_acload === 0) {
                        continue;
                    } elseif ($subject->curriculum->_marking_system === MarkingSystem::MARKING_SYSTEM_CREDIT) {
                        if (!$record && empty($subject->credit)) {
                            continue;
                        } elseif ($record && empty($record->credit)) {
                            continue;
                        }
                    }
                    if ($record !== null) {
                        $record->total_point = $grade;
                        if ($subject->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            $record->grade = $record->total_point;
                        } else {
                            if ($record->total_point >= $five->min_border) {
                                $record->grade = $five->name;
                            } elseif ($record->total_point >= $four->min_border) {
                                $record->grade = $four->name;
                            } elseif ($record->total_point >= $three->min_border) {
                                $record->grade = $three->name;
                            }
                        }
                        $record->save();
                        $count++;
                    } else {
                        /** @var Semester $semester */
                        $semester = Semester::getByCurriculumSemester($this->get('curriculum'), $this->get('semester'));
                        $student = EStudent::findOne($studentId);
                        $record = new EAcademicRecord();
                        $record->subject_name = $subject->subject->name;
                        $record->total_acload = $subject->total_acload;
                        $record->credit = $subject->credit;
                        $record->total_point = $grade;
                        $record->_curriculum = $this->get('curriculum');
                        $record->_education_year = $semester->_education_year;
                        $record->_student = $studentId;
                        $record->_subject = $subject->subject->id;
                        $record->_semester = $this->get('semester');
                        $record->_employee = $subject->_employee;
                        $record->curriculum_name = $student->meta->curriculum->name;
                        $record->education_year_name = $semester->educationYear->name;
                        $record->semester_name = $semester->name;
                        $record->student_name = $student->fullName;
                        $record->student_id_number = $student->student_id_number;
                        $record->employee_name = $subject->employee->fullName ?? '';

                        if ($subject->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            $record->grade = $record->total_point;
                        } else {
                            if ($record->total_point >= $five->min_border) {
                                $record->grade = $five->name;
                            } elseif ($record->total_point >= $four->min_border) {
                                $record->grade = $four->name;
                            } elseif ($record->total_point >= $three->min_border) {
                                $record->grade = $three->name;
                            }
                        }
                        if ($record->save()) {
                            $count++;
                        } else {
                            $this->addError($record->getOneError());
                        }
                    }
                }
            }
            $this->addSuccess(__('{count} students rated', ['count' => $count]));
            return $this->redirect(['batch-rate']);
        }
        return $this->renderView(
            [
                'model' => $model,
                'searchModel' => $searchModel,
                'studentSubject' => $studentSubject,
                'studentSubjectProvider' => $studentSubjectProvider
            ]
        );
    }

    public function actionCertificateCommittee()
    {
        $searchModel = new ECertificateCommittee();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_faculty' => $faculty]);
        }

        if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
            $faculty = Yii::$app->user->identity->employee->headDepartments->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionCertificateCommitteeEdit($id = false)
    {
        if ($id) {
            $model = ECertificateCommittee::findOne($id);
            if ($model === null) {
                $this->notFoundException();
            }
        } else {
            $model = new ECertificateCommittee();
            $model->_education_year = EducationYear::getCurrentYear()->code;
            if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
                $model->_faculty = $this->_user()->employee->deanFaculties->id;
            } elseif ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                $model->_faculty = $this->_user()->employee->headDepartments->parent;
                $model->_department = $this->_user()->employee->headDepartments->id;
            }
        }
        $model->scenario = ECertificateCommittee::SCENARIO_INSERT;
        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            if (0 < $model->membersCount) {
                $this->addError(__('Could not delete related data'));
                return $this->redirect(['archive/certificate-committee-edit', 'id' => $model->id]);
            }
            try {
                $model->delete();
                $this->addSuccess(__('Certificate committee `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['archive/certificate-committee']);
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Certificate committee `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Certificate committee `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['archive/certificate-committee-edit', 'id' => $model->id]);
            }
        }

        if ($this->request->isPost && $model->hasErrors()) {
            $this->addError($model->getErrorSummary(false));
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionCertificateCommitteeMember($id = false)
    {
        if ($id) {
            if (($model = ECertificateCommitteeMember::findOne($id)) === null) {
                $this->notFoundException();
            }
            $model->scenario = ECertificateCommitteeMember::SCENARIO_UPDATE;
        } else {
            $model = new ECertificateCommitteeMember(['scenario' => ECertificateCommitteeMember::SCENARIO_INSERT]);
        }

        $searchModel = new ECertificateCommitteeMember();
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $searchModel->_faculty = $faculty;
        }

        if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
            $searchModel->_department = $this->_user()->employee->headDepartments->id;
        }

        $dataProvider = $searchModel->search($this->getFilterParams());


        /*if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
            $faculty = Yii::$app->user->identity->employee->headDepartments->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }*/

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            try {
                $model->delete();
                $this->addSuccess(__('Certificate Committee Member `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['archive/certificate-committee-member']);
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Certificate Committee Member `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Certificate Committee Member `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['archive/certificate-committee-member', 'id' => $model->id]);
            }
        }

        if ($this->request->isPost && $model->hasErrors()) {
            $this->addError($model->getErrorSummary(false));
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => $model,
            ]
        );
    }

    public function actionGraduateWork()
    {
        $searchModel = new EGraduateQualifyingWork();

        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            $faculty = $this->_user()->employee->deanFaculties->id;
            $searchModel->_faculty = $faculty;
        }
        if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
            $searchModel->_faculty = $this->_user()->employee->headDepartments->parent;
            $searchModel->_department = $this->_user()->employee->headDepartments->id;
        }

        if ($this->get('download')) {
            $query = $searchModel->search($this->getFilterParams(), false);

            $countQuery = clone $query;
            $limit = 200;
            if ($countQuery->count() <= $limit) {
                $fileName = EGraduateQualifyingWork::generateDownloadFile($query);

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
                        new GraduateWorkFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'department' => $department,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['archive/graduate-work', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'BMI va MD mavzulari soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar saxifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['archive/graduate-work']);
            }
        }

        $dataProvider = $searchModel->search($this->getFilterParams());


        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionGraduateWorkEdit($id = false)
    {
        if ($id) {
            $model = EGraduateQualifyingWork::findOne($id);
            if ($model === null) {
                $this->notFoundException();
            }
        } else {
            $model = new EGraduateQualifyingWork();
            if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
                $model->_faculty = $this->_user()->employee->deanFaculties->id;
            } elseif ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                $model->_faculty = $this->_user()->employee->headDepartments->parent;
                $model->_department = $this->_user()->employee->headDepartments->id;
            }
        }
        $model->scenario = EGraduateQualifyingWork::SCENARIO_INSERT;
        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            try {
                $model->delete();
                $this->addSuccess(__('Graduate qualifying work `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['archive/graduate-work']);
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Graduate qualifying work `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Graduate qualifying work `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['archive/graduate-work-edit', 'id' => $model->id]);
            }
        }

        if ($this->request->isPost && $model->hasErrors()) {
            $this->addError($model->getOneError());
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionTranscriptEdit($student = false, $semester = false, $transcript = false, $download = false, $delete = false)
    {
        /**
         * @var $meta EStudentMeta
         */
        $model = null;
        $meta = null;

        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }
        if ($transcript) {
            $model = EAcademicInformation::findOne(['id' => $transcript]);
            if ($delete) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(__('{number} transcript has been deleted ', ['number' => $model->academic_register_number]));
                        return $this->redirect(['transcript']);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->redirect(['transcript-edit', 'transcript' => $model->id]);
                }
                return $this->redirect(['transcript']);
            }

            if ($download) {
                if (!$model->validateTranscriptTranslations()) {
                    return $this->redirect(['transcript-edit', 'transcript' => $model->id, 'errors' => 1]);
                }
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => Yii::getAlias('@runtime/mpdf'),
                ]);
                $mpdf->defaultCssFile = Yii::getAlias('@backend/assets/app/css/pdf-print.css');
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = true;

                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML($this->renderPartial('transcript-pdf', ['model' => $model]));

                return $mpdf->Output('transkript-' . $model->student->student_id_number . '.pdf', Destination::DOWNLOAD);
            }
        }

        if ($student) {
            if ($meta = EStudentTranscriptMeta::findOne([
                'id' => $student,
                'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])) {


                $model = EAcademicInformation::findOne(['_student' => $meta->_student, '_student_meta' => $meta->id]);
                if ($model === null) {
                    $model = new EAcademicInformation([
                        '_student' => $meta->_student,
                        '_student_meta' => $meta->id,
                        '_curriculum' => $meta->_curriculum,
                        '_department' => $meta->_department,
                    ]);
                    $model->fillKeysFromStudent($meta);
                    $model->student_name = $meta->student->fullName;
                    $model->fillFromStudent($meta);

                    if (Yii::$app->language === Config::LANGUAGE_ENGLISH) {
                        $model->fillTranslationsFromStudent($meta);
                    } else {
                        $model->fillTranslationsFromStudent($meta, Config::LANGUAGE_ENGLISH);
                    }

                }
                // $model->scenario = EAcademicInformation::SCENARIO_INSERT;
            }
        }

        if ($model) {
            $model->scenario = EAcademicInformation::SCENARIO_INSERT;
            if ($semester) {
                $model->_semester = $semester;
            }

            if ($model->load($this->post())) {
                if ($model->isNewRecord)
                    $model->_semester = $model->semester_id;
                /*if ($meta) {
                    $model->setAttributes([
                        '_student' => $meta->_student,
                        '_student_meta' => $meta->id,
                        '_curriculum' => $meta->_curriculum,
                        '_education_type' => $meta->_education_type,
                        '_education_form' => $meta->_education_form,
                        '_specialty' => $meta->_specialty_id,
                        '_department' => $meta->_department,
                        '_group' => $meta->_group,
                        '_education_year' => $meta->_education_year,
                    ]);
                }*/

                try {

                    if ($model->save()) {
                        $this->addSuccess(__(
                            $transcript ?
                                'The transcript for student {name} has been updated for {semester}' :
                                'The transcript for student {name} has been created for {semester}',
                            [
                                'name' => $model->student->getFullName(),
                                'semester' => $model->semester->name,
                            ]));
                        return $this->redirect(['transcript-edit', 'transcript' => $model->id]);
                    } else {
                        $this->addError($model->getOneError());
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->refresh();
                }

            }
            if ($this->get('errors', false)) {
                $model->validateTranscriptTranslations();
            }
            return $this->render('transcript-edit-student', [
                'model' => $model,
                'meta' => $meta,
                'department' => $department,
            ]);
        }

        $searchModel = new EStudentTranscriptMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForTranscript($this->getFilterParams(), $department),
        ]);
    }

    public function actionAcademicInformation()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchAcademic($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_meta._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($code = $this->get('code')) {
            if ($student = $this->findStudentMeta($code)) {
                $academic_information = EAcademicInformation::findOne(['_student_meta' => $student->id, '_student' => $student->_student]);
                $download = $this->get('download', -1);
                $file = $this->get('file');
                if ($download !== -1) {
                    if (is_array($academic_information->filename)) {
                        $files = $academic_information->filename;
                        if (isset($files['name'])) {
                            $file = Yii::getAlias('@root/') . $files['base_url'] . DS . $files['name'];

                            if (file_exists($file)) {
                                return Yii::$app->response->sendFile($file, $files['name']);
                            }
                        }
                    }

                    return $this->goHome();
                }

                if ($this->get('generate-pdf')) {
                    $records = [];
                    $totalAcload = 0;
                    $totalPoint = 0;
                    $totalRating = 0;
                    $totalCredit = 0;
                    $totalGpa = 0;
                    $totalGrade = 0;
                    $isFiveRating = false;
                    $isCreditRating = false;
                    /**
                     * @var int $k
                     * @var ECurriculumSubject $curriculumSubject
                     * @var EAcademicRecord $record
                     */
                    foreach (
                        EStudentMeta::getStudentSubjects($student)->andWhere(
                            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_SUBJECT]
                        )->orderBy('e_curriculum_subject.position')->all() as $k => $curriculumSubject
                    ) {

                        $record = $curriculumSubject->getStudentSubjectRecord($student->_student);
                        if (!$record) {
                            throw new \Exception(sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name));
                        }
                        $point = sprintf(
                            "%.2f / %.2f",
                            round(((double)$record->total_point * $record->total_acload) / 100),
                            $record->total_point
                        );
                        $totalRating += round(((double)$record->total_point * $record->total_acload) / 100);
                        $totalCredit += $record->credit;
                        if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                            $isFiveRating = true;
                            $point = round($record->total_point);
                        } elseif ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT) {
                            $isCreditRating = true;
                            $totalGrade += $record->grade;
                            $totalGpa += $record->credit * $record->grade;
                            $point = sprintf(
                            //"%.2f / %s / %s / %s",
                                "%.2f / %s",
                                //round(((double)$record->total_point * $record->total_acload) / 100),
                                round($record->total_point),
                                //round($record->credit),
                                round($record->grade)
                            );
                        }
                        $records[] = [
                            'id' => $k + 1,
                            'name' => sprintf(
                                '%s / %s',
                                $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                                $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                            ),
                            'acload' => $record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT ? $record->credit : $record->total_acload,
                            'point' => $point
                        ];
                        $totalAcload += $record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT ? $record->credit : $record->total_acload;
                        $totalPoint += $record->total_point;
                    }
                    $total = sprintf("<b>%.2f / %.2f</b>", round($totalRating), (((double)$totalPoint) / count($records)));
                    if ($isFiveRating) {
                        $total = sprintf(
                            "<b>%.2f (%s)</b>",
                            $totalPoint / count($records),
                            ceil(($totalPoint / count($records)) * 20)
                        );
                    } elseif ($isCreditRating) {
                        $total = sprintf(
                            "<b>%.2f / %s </b>",
                            (((double)$totalPoint) / count($records)),
                            $totalGrade / count($records)
                        );
                    }
                    $records[] = [
                        'id' => '',
                        'name' => "<b>JAMI / TOTAL</b>",
                        'acload' => "<b>{$totalAcload}</b>",
                        'point' => $total
                    ];
                    $courseRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
                        ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_COURSE]
                    )->orderBy('e_curriculum_subject.position')->all();
                    if (count($courseRecords)) {
                        $records[] = [
                            'id' => '',
                            'name' => sprintf(
                                '<b>%s / %s</b>',
                                'Kurs ishlari',
                                'Course papers'
                            ),
                            'acload' => '',
                            'point' => ''
                        ];
                        foreach ($courseRecords as $k => $curriculumSubject) {
                            $record = $curriculumSubject->getStudentSubjectRecord($student->_student);
                            if (!$record) {
                                throw new \Exception(
                                    sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name)
                                );
                            }
                            $point = sprintf(
                                "%.2f / %.2f",
                                //round(((double)$record->total_point * $record->total_acload) / 100),
                                $record->total_point,
                                $record->grade
                            );
                            if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                                $point = round($record->total_point);
                            }
                            $records[] = [
                                'id' => $k + 1,
                                'name' => sprintf(
                                    '%s / %s',
                                    $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                                    $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                                ),
                                'acload' => $record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT ? $record->credit : $record->total_acload,
                                'point' => $point
                            ];
                        }
                    }
                    $practicumRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
                        ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_PRACTICUM]
                    )->orderBy('e_curriculum_subject.position')->all();
                    if (count($practicumRecords)) {
                        $records[] = [
                            'id' => '',
                            'name' => sprintf(
                                '<b>%s / %s</b>',
                                'Malakaviy amaliyot',
                                'Qualification practice'
                            ),
                            'acload' => '',
                            'point' => ''
                        ];
                        foreach ($practicumRecords as $k => $curriculumSubject) {
                            $record = $curriculumSubject->getStudentSubjectRecord($student->_student);
                            if (!$record) {
                                throw new \Exception(
                                    sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name)
                                );
                            }
                            $point = sprintf(
                                "%.2f / %.2f",
                                //round(((double)$record->total_point * $record->total_acload) / 100),
                                $record->total_point,
                                $record->grade
                            );
                            if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                                $point = round($record->total_point);
                            }
                            $records[] = [
                                'id' => $k + 1,
                                'name' => sprintf(
                                    '%s / %s',
                                    $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                                    $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                                ),
                                'acload' => $record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT ? $record->credit : $record->total_acload,
                                'point' => $point
                            ];
                        }
                    }
                    $stateRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
                        ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_STATE]
                    )->orderBy('e_curriculum_subject.position')->all();
                    if (count($stateRecords)) {
                        $records[] = [
                            'id' => '',
                            'name' => sprintf(
                                '<b>%s / %s</b>',
                                'Yakuniy davlat attestatsiyalari',
                                'Final state attestation'
                            ),
                            'acload' => '',
                            'point' => ''
                        ];
                        foreach ($stateRecords as $k => $curriculumSubject) {
                            $record = $curriculumSubject->getStudentSubjectRecord($student->_student);
                            if (!$record) {
                                throw new \Exception(
                                    sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name)
                                );
                            }
                            $point = sprintf(
                                "%.2f / %.2f",
                                //round(((double)$record->total_point * $record->total_acload) / 100),
                                $record->total_point,
                                $record->grade
                            );
                            if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                                $point = round($record->total_point);
                            }
                            $records[] = [
                                'id' => $k + 1,
                                'name' => sprintf(
                                    '%s / %s',
                                    $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                                    $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH)
                                ),
                                'acload' => $record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_CREDIT ? $record->credit : $record->total_acload,
                                'point' => $point
                            ];
                        }
                    }
                    $graduateRecords = EStudentMeta::getStudentSubjects($student)->andWhere(
                        ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE]
                    )->orderBy('e_curriculum_subject.position')->all();
                    if (count($graduateRecords) > 0) {
                        $records[] = [
                            'id' => '',
                            'name' => sprintf(
                                '<b>%s / %s</b>',
                                'Bitiruv malakaviy ishi (magistrlik dissertatsiyasi)',
                                'Graduation qualification work (master\'s dissertation)'
                            ),
                            'acload' => '',
                            'point' => ''
                        ];
                        $curriculumSubject = $graduateRecords[0];
                        $record = $curriculumSubject->getStudentSubjectRecord($student->_student);
                        if (!$record) {
                            throw new \Exception(sprintf('Academic record for `%s` not found', $curriculumSubject->subject->name));
                        }
                        $point = sprintf(
                            "%.2f / %.2f",
                            round(((double)$record->total_point * $record->total_acload) / 100),
                            $record->total_point
                        );
                        if ($record->curriculum->markingSystem->code === MarkingSystem::MARKING_SYSTEM_FIVE) {
                            $point = round($record->total_point);
                        }

                        $records[] = [
                            'id' => 1,
                            'name' => sprintf(
                                '%s / %s: <br> %s / %s',
                                $record->subject->getTranslation('name', Config::LANGUAGE_UZBEK),
                                $record->subject->getTranslation('name', Config::LANGUAGE_ENGLISH),
                                $model->getTranslation(
                                    'graduate_qualifying_work',
                                    Config::LANGUAGE_UZBEK
                                ),
                                $model->getTranslation(
                                    'graduate_qualifying_work',
                                    Config::LANGUAGE_ENGLISH
                                )
                            ),
                            'acload' => $record->total_acload,
                            'point' => $point
                        ];
                    }
                    if ($isCreditRating) {
                        $gpa = $totalGpa / $totalCredit;
                        $records[] = [
                            'id' => "",
                            'name' => MarkingSystem::getCreditScale(),
                            'acload' => 'Jami kreditlar miqdori / Total credits <br>' . $totalCredit,
                            'point' => '<br>O‘rtacha ball / GPA  <br><br>' . sprintf('%.2f', $gpa),
                            //'acload' => '',
                            //'point' => sprintf('%.2f', $gpa),
                        ];
                    }

                    if ($this->get('ready')) {
                        //$this->layout = 'diploma';
                        // get your HTML raw content without any layouts or scripts
                        if ($academic_information->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT) {
                            $content = $this->renderPartial(
                                '_academic_application_credit',
                                ['model' => $academic_information, 'student' => $student, 'records' => array_slice($records, 0, 50)]
                            );
                        } else {
                            $content = $this->renderPartial(
                                '_academic_application_other',
                                ['model' => $academic_information, 'student' => $student, 'records' => array_slice($records, 0, 50)]
                            );
                        }


                        //$destination = Pdf::DEST_DOWNLOAD;

                        $pdf = new Pdf([
                            'mode' => Pdf::MODE_UTF8,
                            'format' => Pdf::FORMAT_A4,
                            'orientation' => Pdf::ORIENT_PORTRAIT,
                            'destination' => Pdf::DEST_BROWSER,
                            'filename' => $student->id . '.pdf',
                            'content' => $content,
                            //'cssFile' => '@backend/assets/app/css/pdf-print.css',

                            //'cssFile' => '@app/assets/app/css/diploma-application.css',
                            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.css',

                            'cssInline' => 'body {font-size:14px !important; font-family: "Times New Roman" !important} .title {text-indent:32px !important} .table-custom tr, .table-custom td {border:0; font-size:9pt;} .custom-underline {border-bottom:1px dotted #ccc;} .custom-line {border-top:1px dotted #ccc;}',
                            'methods' => [
                                //  'SetHeader' => [date("d.m.Y")],
                                'SetFooter' => ['{PAGENO}'],
                            ]
                        ]);
                        //return $pdf->render();
                        $content1 = $pdf->content;
                        $filename = $pdf->filename;
                        $dir = Yii::getAlias("@root/private/transcript") . DS . $student->_education_year . DS . $student->_department;
                        if (!file_exists($dir . DS . $filename)) {
                            if (!is_dir($dir)) {
                                FileHelper::createDirectory($dir, 0777);
                            }
                        }
                        try {
                            $path = $pdf->Output($content1, $dir . DS . $filename, \Mpdf\Output\Destination::FILE);
                            $data['name'] = $filename;
                            $data['order'] = "";
                            $data['type'] = "application/pdf";
                            $data['base_url'] = '/private/transcript/' . $student->_education_year . '/' . $student->_department;
                            $academic_information->filename = $data;
                            $academic_information->academic_status = EAcademicInformation::ACADEMIC_INFORMATION_STATUS_GENERATED;
                            $academic_information->save(false);
                            $this->addSuccess(
                                __('The transcript file was created successfully')
                            );

                        } catch (\Exception $e) {
                            $this->addError($e->getMessage());
                        }


                    } else {
                        if ($academic_information->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT) {
                            return $this->render(
                                '_academic_application_credit',
                                ['model' => $academic_information, 'student' => $student, 'records' => array_slice($records, 0, 50)]
                            );
                        } else {
                            return $this->render(
                                '_academic_application_other',
                                ['model' => $academic_information, 'student' => $student, 'records' => array_slice($records, 0, 50)]
                            );
                        }
                    }


                }


            }
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionAcademicInformationEdit($id)
    {
        $model = new EAcademicInformation();
        $student = false;
        if ($id) {
            $student = $this->findStudentMeta($id);
            $model = EAcademicInformation::findOne(['_student_meta' => $student->id]);
            if ($model === null) {
                $model = new EAcademicInformation();
                $model->fillKeysFromStudent($student);
                //$model->student_name = $student->student->fullName;

            }
        }
        if ($model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT)
            $model->scenario = EAcademicInformation::SCENARIO_INSERT_CREDIT;
        else
            $model->scenario = EAcademicInformation::SCENARIO_INSERT_OTHER;

        if ($this->get('delete')) {
            $message = "";
            /*if ($model->accepted) {
                $this->addError(__('Can not delete accepted diploma'));
                return $this->redirect(['diploma-edit', 'id' => $model->_student]);
            }*/
            if ($model->delete()) {
                $this->addSuccess(__('Academic Information [{id}] deleted successfully', ['code' => $model->id]));
            }

            return $this->redirect(['diploma']);
        }
        if ($model->isNewRecord) {
            $model->academic_register_date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
        }
        if ($model->load(Yii::$app->request->post())) {
            $model->academic_status = EAcademicInformation::ACADEMIC_INFORMATION_STATUS_PROCESS;
            /*if ($model->isNewRecord) {
                $model->fillKeysFromStudent($student);

            }*/

            /*if ($model->accepted) {
                $this->addError(__('Can not save accepted diploma.'));
                return $this->redirect(['diploma-edit', 'id' => $model->_student]);
            }*/
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $this->addSuccess(__('Academic Information [{id}] created successfully', ['id' => $model->id]));
                    if ($transaction !== null) {
                        $transaction->commit();
                    }
                    return $this->redirect(['academic-information']);
                    //return $this->redirect(['academic-information', 'id' => $model->_student_meta]);
                }
            } catch (\Exception $exception) {
                $transaction->rollBack();
                $this->addError($exception->getMessage(), true);
            }
        }

        if ($this->get('errors', false)) {
            $model->validateTranslations();
            $model->validateSubjectsTranslations();
        }

        return $this->renderView(
            [
                'model' => $model,
                'student' => $student,
            ]
        );
    }

    public function actionTranscript($subjects = false)
    {
        $searchModel = new EAcademicInformation();
        $dataProvider = $searchModel->searchContingent($this->getFilterParams());

        $department = false;


        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_academic_information._department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAcademicInformationData($subjects = false)
    {
        $searchModel = new EAcademicInformationData();
        $dataProvider = $searchModel->searchContingent($this->getFilterParams());

        $department = false;

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_academic_information_data._department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAcademicInformationDataEdit($student = false, $semester = false, $information = false, $download = false, $delete = false)
    {
        /**
         * @var $meta EStudentMeta
         */
        $model = null;
        $meta = null;

        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }
        if ($information) {
            $model = EAcademicInformationData::findOne(['id' => $information]);
            if ($delete) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(__('{number} academic information data has been deleted ', ['number' => $model->blank_number]));
                        return $this->redirect(['academic-information-data']);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->redirect(['academic-information-data-pdf', 'information' => $model->id]);
                }
                return $this->redirect(['academic-information-data']);
            }
            $marking_system = null;
            $marking_system = $model->studentMeta->curriculum->_marking_system;
            if ($download) {
                $this->layout = 'academic.php';
                $content2 = $this->renderPartial('academic-information-data-subjects-pdf', [
                    'model' => $model,
                    'marking_system' => $marking_system,
                ]);

                $content = $this->renderPartial('academic-information-data-pdf', [
                    'model' => $model,
                    'content2' => $content2,
                ]);
                $pdf = new Pdf([
                    'mode' => Pdf::MODE_UTF8,
                    'format' => Pdf::FORMAT_A4,
                    'orientation' => Pdf::ORIENT_PORTRAIT,
                    'destination' => Pdf::DEST_BROWSER,
                    'filename' => 'Akademik_ma`lumotnoma-' . $model->student->student_id_number . '.pdf',
                    'content' => $content,
                    'cssFile' => [
                        //   '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                        '@app/assets/app/css/academic.css'
                    ],
                    'methods' => [
                        'SetHeader' => [],
                        'SetFooter' => [],
                    ]
                ]);
                $content1 = $pdf->content;
                $filename = $pdf->filename;
                return $pdf->Output($content1, $filename, \Mpdf\Output\Destination::DOWNLOAD);
            }
        }

        if ($student) {
            if ($meta = EStudentAcademicInformationDataMeta::findOne([
                'id' => $student,
                'active' => true,
                '_student_status' => [StudentStatus::STUDENT_TYPE_EXPEL, StudentStatus::STUDENT_TYPE_GRADUATED]
            ])) {


                $model = EAcademicInformationData::findOne(['_student' => $meta->_student, '_student_meta' => $meta->id]);
                if ($model === null) {
                    $model = new EAcademicInformationData([
                        '_student' => $meta->_student,
                        '_student_meta' => $meta->id,
                        '_curriculum' => $meta->_curriculum,
                        '_department' => $meta->_department,
                    ]);
                    $model->fillKeysFromStudent($meta);
                    //$model->first_name = $meta->student->first_name;
                    $model->fillFromStudent($meta);


                }
                // $model->scenario = EAcademicInformation::SCENARIO_INSERT;
            }
        }

        if ($model) {
            $model->scenario = EAcademicInformationData::SCENARIO_INSERT;
            if ($semester) {
                $model->_semester = $semester;
            }
            $decree = null;

            if ($model->load($this->post())) {
                if (empty($model->continue_start_date))
                    $model->continue_start_date = null;
                if (empty($model->continue_end_date))
                    $model->continue_end_date = null;
                if (empty($model->studied_start_date))
                    $model->studied_start_date = null;
                if (empty($model->studied_end_date))
                    $model->studied_end_date = null;
                if ($model->isNewRecord)
                    $model->_semester = $model->semester_id;

                if ($decree = EDecree::findOne($model->_decree)) {
                    if ($model->isNewRecord) {
                        $reason = ExpelReason::findOne($model->expulsion_decree_reason)->name;
                    } else {
                        $reason = $model->expulsion_decree_reason;
                    }
                    $model->setAttributes([
                        'expulsion_decree_reason' => $reason,
                        'expulsion_decree_number' => $decree->number,
                        'expulsion_decree_date' => $decree->date,
                    ]);
                }

                try {

                    if ($model->save()) {
                        $this->addSuccess(__(
                            $information ?
                                'The Academic Information for student {name} has been updated for {semester}' :
                                'The Academic Information for student {name} has been created for {semester}',
                            [
                                'name' => $model->student->getFullName(),
                                'semester' => $model->semester->name,
                            ]));
                        return $this->redirect(['academic-information-data-edit', 'information' => $model->id]);
                    } else {
                        $this->addError($model->getOneError());
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->refresh();
                }

            }

            return $this->render('academic-information-data-edit-student', [
                'model' => $model,
                'meta' => $meta,
                'department' => $department,
            ]);
        }

        $searchModel = new EStudentAcademicInformationDataMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForAcademicInformationData($this->getFilterParams(), $department),
        ]);
    }

    protected function findEmploymentModel($id)
    {
        //if (($model = EStudentEmployment::findOne($id)) !== null) {
        if (($model = EStudentEmployment::findOne(['_student' => $id])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentModel($id)
    {
        if (($model = EStudent::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /**
     * @param $id
     * @return EStudentMeta|array|\yii\db\ActiveRecord|null
     * @throws \yii\web\NotFoundHttpException
     */
    protected function findStudentMetaModel($id)
    {
        if (($model = EStudentMeta::find()->where(
                ['_student' => $id, '_student_status' => StudentStatus::STUDENT_TYPE_GRADUATED]
            )->one()) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentMeta($id)
    {
        if (($model = EStudentMeta::find()->where(
                ['id' => $id, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]
            )->one()) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    public function actionAcademicSheet($id = false)
    {
        if ($id) {
            if ($model = EStudentAcademicSheetMeta::findOne($id)) {

                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => Yii::getAlias('@runtime/mpdf'),
                ]);
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = true;

                $mpdf->SetTitle(__('Archive Academic Sheet'));
                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML($this->renderPartial('academic-sheet-view', ['model' => $model]));

                return $mpdf->Output('talaba-varaqasi-' . $model->student->student_id_number . '.pdf', $this->get('download') ? Destination::DOWNLOAD : Destination::INLINE);
            }
        }

        $searchModel = new EStudentAcademicSheetMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchContingent($this->getFilterParams(), $department),
        ]);
    }

    public function actionReference()
    {
        $searchModel = new EStudentReference();
        $dataProvider = $searchModel->searchContingent($this->getFilterParams());

        $department = false;

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_reference._department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if ($file = $this->get('file')) {
            if ($reference = EStudentReference::findOne(['id' => $file])) {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => Yii::getAlias('@runtime/mpdf'),
                ]);
                $mpdf->defaultCssFile = Yii::getAlias('@backend/assets/app/css/pdf-print.css');
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = true;
                $univer = EUniversity::findCurrentUniversity();
                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML($this->renderPartial('@frontend/views/student/reference-pdf', ['model' => $reference, 'univer'=>$univer]));

                return $mpdf->Output('Reference-' . $reference->student->student_id_number . '.pdf', Destination::DOWNLOAD);

            }
            return $this->redirect(['reference']);
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
