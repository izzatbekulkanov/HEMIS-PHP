<?php


namespace frontend\controllers;

use common\components\Config;
use common\models\academic\EDecreeStudent;
use common\models\archive\EGraduateQualifyingWork;
use common\models\curriculum\EducationYear;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\finance\EStudentContract;
use common\models\structure\EUniversity;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\system\classifier\ContractSummaType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\StudentStatus;
use frontend\models\academic\StudentDecree;
use frontend\models\academic\StudentDiploma;
use frontend\models\archive\StudentReference;
use frontend\models\finance\StudentContract;
use frontend\models\finance\StudentContractInvoice;
use frontend\models\finance\StudentContractType;
use frontend\models\system\StudentMeta;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class StudentController extends FrontendController
{
    public $activeMenu = 'student';

    public function actionDecree($file = false)
    {
        /**
         * @var $model EDecreeStudent
         */
        if ($file) {
            if ($model = EDecreeStudent::findOne(['_student' => $this->_user()->id, '_decree' => $file])) {
                if (is_array($model->decree->file)) {
                    $fileData = $model->decree->file;
                    if (isset($fileData['path'])) {
                        $file = Yii::getAlias('@static/uploads/') . $fileData['path'];

                        if (file_exists($file)) {
                            return Yii::$app->response->sendFile($file, $fileData['name']);
                        }
                    }
                }
            }
            return $this->redirect(['decree']);
        }

        $searchModel = new StudentDecree();

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getFilterParams()),
        ]);
    }

    public function actionContract()
    {
        /**
         * @var $model EStudentContract
         */
        if ($file = $this->get('file')) {
            if ($model = StudentContract::findOne(['_student' => $this->_user()->id, 'id' => $file])) {
                if (is_array($model->filename)) {
                    $fileData = $model->filename;
                    if (isset($fileData['name'])) {
                        $file = Yii::getAlias('@root') . $fileData['base_url'] . DS . $fileData['name'];
                        if (file_exists($file)) {
                            return Yii::$app->response->sendFile($file, $fileData['name']);
                        }
                    }
                }
            }
            return $this->redirect(['contract']);
        }
        if ($file = $this->get('invoice')) {
            if ($model = StudentContractInvoice::findOne(['_student' => $this->_user()->id, 'id' => $file])) {
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
                $mpdf->WriteHTML($this->renderPartial('@backend/views/finance/contract-invoice-pdf', ['model' => $model, 'univer'=>$univer]));

                return $mpdf->Output('Invoice-' . $this->_user()->student_id_number . '.pdf', Destination::DOWNLOAD);
            }
            return $this->redirect(['contract']);
        }
        if ($this->get('set')) {
            if ($this->_user()->meta->_student_status == StudentStatus::STUDENT_TYPE_STUDIED && $this->_user()->meta->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                try {

                    $model = new StudentContractType();
                    $model->scenario = StudentContractType::SCENARIO_CREATE_SELF;
                    $model->_education_year = EducationYear::getCurrentYear()->code;
                    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ActiveForm::validate($model);
                    }
                    $list_contract_summa_type = "";
                    if($this->_user()->meta->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $this->_user()->meta->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $this->_user()->meta->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $this->_user()->meta->_education_form == EducationForm::EDUCATION_FORM_EVENING)
                        $list_contract_summa_type = ContractSummaType::getClassifierOtherOptions();
                    else
                        $list_contract_summa_type = ContractSummaType::getClassifierOptions();

                    if ($model->load($this->post())) {
                        // $model->_education_type = ESpecialty::findOne(['id' => $model->_specialty])->_education_type;
                        $old = StudentContractType::findOne([
                            '_student'=>$this->_user()->id,
                            '_department'=>$this->_user()->meta->_department,
                            '_specialty'=>$this->_user()->meta->_specialty_id,
                            '_education_year'=> $model->_education_year,
                            '_education_form'=>$this->_user()->meta->_education_form,
                        ]);
                        if($old !== null)
                            $model = $old;
                        $model->_student = $this->_user()->id;
                        $model->_specialty = $this->_user()->meta->_specialty_id;
                        $model->_department = $this->_user()->meta->_department;
                        $model->_education_form = $this->_user()->meta->_education_form;
                        $model->_created_self = StudentContractType::STATUS_ENABLE;
                        $model->contract_status = StudentContractType::CONTRACT_REQUEST_STATUS_SEND;
                        $univer = EUniversity::findCurrentUniversity();
                        if ($model->save()) {
                            $studentContract = new EStudentContract();
                            $studentContract->_student_contract_type = $model->id;
                            $studentContract->_contract_summa_type = $model->_contract_summa_type;
                            $studentContract->contract_form_type = $model->contract_form_type;
                            $studentContract->_contract_type = $model->_contract_type;
                            $studentContract->_student = $model->_student;
                            $studentContract->_specialty = $model->_specialty;
                            $studentContract->_department = $model->_department;
                            $studentContract->_education_type = ESpecialty::findOne(['id' => $model->_specialty])->_education_type;
                            $studentContract->_education_form = $model->_education_form;
                            $studentContract->_education_year = $model->_education_year;
                            $studentContract->contract_status = $model->contract_status;
                            $studentContract->_group = $this->_user()->meta->_group;
                            $studentContract->_curriculum = $this->_user()->meta->_curriculum;
                            $studentContract->_level = Semester::getCourseCode( $studentContract->_curriculum, $model->_education_year)->_level;
                            $studentContract->mailing_address = $univer->mailing_address;
                            $studentContract->bank_details = $univer->bank_details;
                            //$studentContract->date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
                            $studentContract->summa = null;
                            if ($studentContract->save(true)) {
                                $this->addSuccess(
                                    __(
                                        'Contract Order {name} created successfully',
                                        ['name' => $model->id]
                                    )
                                );
                            }
                            return $this->redirect(['student/contract']);
                        }
                    }

                    if (Yii::$app->request->isAjax) {
                        return $this->renderAjax('_set-contract-order', [
                            'model' => $model,
                            'list_contract_summa_type' => $list_contract_summa_type,
                        ]);
                    } else {
                        return $this->render('_set-contract-order', [
                            'model' => $model,
                            'list_contract_summa_type' => $list_contract_summa_type,

                        ]);
                    }
                } catch (\Exception $e) {
                    if ($e->getCode() == 23505) {
                        $this->addError(__('You cannot order a contract for the selected academic year'));
                    } else {
                        $this->addError($e->getMessage());
                    }
                }
            } else {
                $this->addError(__('It is not possible to order a contract in your status'));
            }
            return $this->redirect(['contract']);
        }

        $searchModel = new StudentContract();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getFilterParams()),
        ]);
    }

    public function actionDiploma()
    {
        /**
         * @var $model EStudentContract
         */
        if ($id = $this->get('diploma')) {
            if ($model = StudentDiploma::findOne(['_student' => $this->_user()->id, 'id' => $id])) {
                if (file_exists($model->getDiplomaFilePath())) {
                    return Yii::$app->response->sendFile($model->getDiplomaFilePath(), "diplom-{$model->student->student_id_number}.pdf");
                }
            }
            return $this->redirect(['diploma']);
        }

        if ($id = $this->get('supplement')) {
            if ($model = StudentDiploma::findOne(['_student' => $this->_user()->id, 'id' => $id])) {
                if (file_exists($model->getSupplementFilePath())) {
                    return Yii::$app->response->sendFile($model->getSupplementFilePath(), "ilova-{$model->student->student_id_number}.pdf");
                }
            }
            return $this->redirect(['diploma']);
        }


        $searchModel = new StudentDiploma();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getFilterParams()),
        ]);
    }

    public function actionDocument($id = -1)
    {
        $student = $this->_user();
        $documents = $student->getDownloadableDocuments();

        if ($id !== -1) {
            if (isset($documents[$id])) {
                if ($result = call_user_func($documents[$id]['callback'])) {
                    return $result;
                }
            }
            return $this->redirect(['student/document']);
        }


        return $this->renderView([
            'documents' => $documents
        ]);
    }

    public function actionGraduateQualifying()
    {

        $records = [];


        $theme = EGraduateQualifyingWork::findOne(['_student' => $this->_user()->id]);

        /**
         * @var int $k
         * @var ECurriculumSubject $curriculumSubject
         * @var AcademicRecord $record
         */



        $graduateRecords = StudentMeta::getStudentSubjects($this->_user()->meta)->andWhere(
            ['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE]
        )->orderBy('e_curriculum_subject.position')->all();
        if (count($graduateRecords) > 0) {

            $curriculumSubject = $graduateRecords[0];
            $record = $curriculumSubject->getStudentSubjectRecord($this->_user()->id);
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
                'id' => '',
                'name' => sprintf(
                    '%s: <br>%s',
                    __('Graduation qualification work (master\'s dissertation)'),
                    $theme->work_name
                ),

                'acload' => sprintf(
                    "%s",
                    $record ? round($record->total_acload) : $curriculumSubject->total_acload
                ),
                'point' => $mark,
                'grade' => $grade
            ];
        }
        elseif ($theme){
            $records[] = [
                'id' => '',
                'name' => sprintf(
                    '%s: <br>%s',
                    __('Graduation qualification work (master\'s dissertation)'),
                    $theme->work_name
                ),

                'acload' => '',
                'point' => '',
                'grade' => ''
            ];
        }
        return $this->renderView([
            'records' => $records
        ]);
    }

    public function actionReference()
    {
        /**
         * @var $model StudentReference
         */
        if ($file = $this->get('file')) {
            if ($reference = StudentReference::findOne(['_student' => $this->_user()->id, 'id' => $file])) {
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
                $mpdf->WriteHTML($this->renderPartial('reference-pdf', ['model' => $reference, 'univer'=>$univer]));

                return $mpdf->Output('Reference-' . $this->_user()->student_id_number . '.pdf', Destination::DOWNLOAD);

            }
            return $this->redirect(['reference']);
        }

        if ($this->get('get')) {
            $univer = EUniversity::findCurrentUniversity();
            if ($this->_user()->meta->_student_status == StudentStatus::STUDENT_TYPE_STUDIED) {

                try {
                    $model = new StudentReference();
                    $model->_student_meta = $this->_user()->meta->id;
                    $model->_student = $this->_user()->id;
                    $model->_department = $this->_user()->meta->_department;
                    $model->_specialty = $this->_user()->meta->_specialty_id;
                    $model->_education_type = $this->_user()->meta->_education_type;
                    $model->_education_form = $this->_user()->meta->_education_form;
                    $model->_education_year = $this->_user()->meta->_education_year;
                    $model->_curriculum = $this->_user()->meta->_curriculum;
                    $model->_group = $this->_user()->meta->_group;
                    $model->_semester = $this->_user()->meta->_semestr;
                    $model->_level = $this->_user()->meta->_level;
                    $model->university_name = $univer->getTranslation('name', Config::LANGUAGE_UZBEK);;
                    $model->first_name = $this->_user()->getTranslation('first_name', Config::LANGUAGE_UZBEK);
                    $model->second_name = $this->_user()->getTranslation('second_name', Config::LANGUAGE_UZBEK);
                    $model->third_name = $this->_user()->getTranslation('third_name', Config::LANGUAGE_UZBEK);
                    $model->passport_pin = $this->_user()->passport_pin;
                    $model->birth_date = $this->_user()->birth_date;
                    $model->year_of_enter = $this->_user()->year_of_enter;
                    $model->_citizenship = $this->_user()->_citizenship;
                    $model->_payment_form = $this->_user()->meta->_payment_form;
                    $num = StudentReference::getCountReference($this->_user()->id);
                    $model->reference_number = $this->_user()->student_id_number.'/'.$num;
                    $model->reference_date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');

                    $model->department_name = $this->_user()->meta->department->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->specialty_name = $this->_user()->meta->specialty->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->education_type_name = $this->_user()->meta->educationType->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->education_form_name = $this->_user()->meta->educationForm->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->education_year_name = $this->_user()->meta->educationYear->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->curriculum_name = $this->_user()->meta->curriculum->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->semester_name = $this->_user()->meta->semester->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->level_name = $this->_user()->meta->level->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->group_name = $this->_user()->meta->group->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->citizenship_name = $this->_user()->citizenship->getTranslation('name', Config::LANGUAGE_UZBEK);
                    $model->payment_form_name = $this->_user()->meta->paymentForm->getTranslation('name', Config::LANGUAGE_UZBEK);

                    if ($model->save(false)) {
                        $this->addSuccess(
                            __(
                                'Reference {name} created successfully',
                                ['name' => $model->reference_number]
                            )
                        );
                        return $this->redirect(['student/reference']);
                    }

                } catch (\Exception $e) {
                    if ($e->getCode() == 23505) {
                        $this->addError(__('You cannot get a reference for the selected semester'));
                    } else {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['student/reference']);
                }
            } else {
                $this->addError(__('It is not possible to get a reference in your status'));
            }
            return $this->redirect(['reference']);
        }

        $searchModel = new StudentReference();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getFilterParams()),
        ]);
    }

    public function actionPersonalData()
    {
        $student = $this->_user();

        return $this->renderView([
            'model' => $student
        ]);
    }
}