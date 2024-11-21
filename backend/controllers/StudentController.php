<?php

namespace backend\controllers;

use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiError;
use common\models\academic\EDecreeStudent;
use common\models\archive\EAcademicInformation;
use common\models\archive\EAcademicInformationData;
use common\models\archive\EAcademicInformationDataSubject;
use common\models\archive\EAcademicRecord;
use common\models\archive\EStudentDiploma;
use common\models\archive\EStudentEmployment;
use common\models\archive\ETranscriptSubject;
use common\models\attendance\EAttendance;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EExamStudent;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubject;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeForeign;
use common\models\student\EStudentOlympiad;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use common\models\performance\EPerformance;
use common\models\performance\EStudentGpa;
use common\models\performance\EStudentPtt;
use common\models\student\EAdmissionQuota;
use common\models\student\EGroup;
use common\models\student\EQualification;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\student\EStudentAward;
use common\models\student\EStudentExchange;
use common\models\student\EStudentMeta;
use common\models\student\EStudentSport;
use common\models\system\AdminRole;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\Gender;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\ScienceBranch;
use common\models\system\classifier\StudentStatus;
use common\models\system\job\ContingentStudentContingentFileGenerateJob;
use common\models\system\job\StudentContingentFileGenerateJob;
use Yii;
use yii\base\Exception;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;
use yii\queue\redis\Queue;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class StudentController extends BackendController
{
    public $activeMenu = 'student';


    public function actionExchange($id = false, $edit = false)
    {
        $searchModel = new EStudentExchange();
        $model = false;
        if ($id) {
            $model = EStudentExchange::findOne($id);
            if ($model == null) {
                $this->notFoundException();
            }
        } elseif ($edit) {
            $model = new EStudentExchange([
                '_education_year' => EducationYear::getCurrentYear()->code,
                'exchange_type' => EStudentExchange::TYPE_OUTGOING,
            ]);
        }

        if ($model) {
            if ($this->checkModelToApi($model)) {
                return $this->redirect(['exchange', 'id' => $model->id]);
            }
            if ($this->get('delete')) {
                $message = false;
                if ($model->tryToDelete(
                    function () use ($model) {
                        return $this->syncModelToApi($model, true);
                    },
                    $message
                )) {
                    $this->addSuccess(
                        __('Student exchange of {name} deleted successfully', ['name' => $model->full_name])
                    );
                    return $this->redirect(['exchange']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                return $this->redirect(['exchange', 'id' => $model->id]);
            }

            if ($this->get('specialties')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    MasterSpeciality::find()
                        ->orWhereLike('name', $q = $this->get('query'))
                        ->orWhereLike('code', $q)
                        ->andWhere(['active' => true])
                        ->all(),
                    function (MasterSpeciality $data) {
                        return ['name' => $data->name];
                    });
            }
            if ($this->get('universities')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    EStudentExchange::find()
                        ->orWhereLike('university', $q = $this->get('query'))
                        ->all(),
                    function (EStudentExchange $data) {
                        return ['name' => $data->university];
                    });
            }
            if ($model->load($this->post())) {
                if ($model->save()) {
                    $this->syncModelToApi($model);

                    $this->addSuccess(__($id ? 'Student exchange of {name} updated successfully' : 'Student exchange of {name} created successfully', ['name' => $model->full_name]));

                    return $this->redirect(['exchange', 'id' => $model->id]);
                }
            }
            return $this->render('exchange-edit', ['model' => $model]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }


    public function actionOlympiad($id = false, $edit = false)
    {
        $searchModel = new EStudentOlympiad();
        $model = false;
        if ($id) {
            $model = EStudentOlympiad::findOne($id);
            if ($model == null) {
                $this->notFoundException();
            }
        } elseif ($edit) {
            $model = new EStudentOlympiad([
                '_education_year' => EducationYear::getCurrentYear()->code,
                'olympiad_type' => EStudentOlympiad::TYPE_REPUBLIC,
            ]);
        }

        if ($model) {
            if ($this->checkModelToApi($model)) {
                return $this->redirect(['olympiad', 'id' => $model->id]);
            }
            if ($this->get('delete')) {
                $message = false;
                if ($model->tryToDelete(
                    function () use ($model) {
                        return $this->syncModelToApi($model, true);
                    },
                    $message
                )) {
                    $this->addSuccess(
                        __('Student olympiad of {name} deleted successfully', ['name' => $model->student->getFullName()])
                    );
                    return $this->redirect(['olympiad']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                return $this->redirect(['olympiad', 'id' => $model->id]);
            }

            if ($this->get('students')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    EStudent::find()
                        ->orWhereLike('first_name', $q = $this->get('query'))
                        ->orWhereLike('second_name', $q)
                        ->orWhereLike('third_name', $q)
                        ->orWhereLike('passport_number', $q)
                        ->orWhereLike('student_id_number', $q)
                        ->limit(30)
                        ->all(),
                    function (EStudent $data) {
                        return [
                            'name' => $data->getFullName(),
                            'code' => $data->student_id_number,
                            'id' => $data->id,
                        ];
                    });
            }

            if ($model->load($this->post())) {
                if ($model->save()) {
                    $this->syncModelToApi($model);

                    $this->addSuccess(__($id ? 'Student olympiad of {name} updated successfully' : 'Student olympiad of {name} created successfully', ['name' => $model->student->getFullName()]));

                    return $this->redirect(['olympiad', 'id' => $model->id]);
                }
            }
            return $this->render('olympiad-edit', ['model' => $model]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionSport($id = false, $edit = false)
    {
        $searchModel = new EStudentSport();
        $model = false;
        if ($id) {
            $model = EStudentSport::findOne($id);
            if ($model == null) {
                $this->notFoundException();
            }
        } elseif ($edit) {
            $model = new EStudentSport([
                '_education_year' => EducationYear::getCurrentYear()->code,
            ]);
        }

        if ($model) {
            if ($this->checkModelToApi($model)) {
                return $this->redirect(['sport', 'id' => $model->id]);
            }
            if ($this->get('delete')) {
                $message = false;
                if ($model->tryToDelete(
                    function () use ($model) {
                        return $this->syncModelToApi($model, true);
                    },
                    $message
                )) {
                    $this->addSuccess(
                        __('Student sport of {name} deleted successfully', ['name' => $model->student->getFullName()])
                    );
                    return $this->redirect(['sport']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                return $this->redirect(['sport', 'id' => $model->id]);
            }

            if ($this->get('students')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    EStudent::find()
                        ->orWhereLike('first_name', $q = $this->get('query'))
                        ->orWhereLike('second_name', $q)
                        ->orWhereLike('third_name', $q)
                        ->orWhereLike('passport_number', $q)
                        ->orWhereLike('student_id_number', $q)
                        ->limit(30)
                        ->all(),
                    function (EStudent $data) {
                        return [
                            'name' => $data->getFullName(),
                            'code' => $data->student_id_number,
                            'id' => $data->id,
                        ];
                    });
            }

            if ($model->load($this->post())) {
                if ($model->save()) {
                    $this->syncModelToApi($model);

                    $this->addSuccess(__($id ? 'Student sport of {name} updated successfully' : 'Student sport of {name} created successfully', ['name' => $model->student->getFullName()]));

                    return $this->redirect(['sport', 'id' => $model->id]);
                }
            }
            return $this->render('sport-edit', ['model' => $model]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionSpecial()
    {
        $searchModel = new ESpecialty();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $faculty = false;
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

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }


    public function actionSpecialEdit($id = false)
    {
        if ($id) {
            $model = $this->findSpecialModel($id);
        } else {
            $model = new ESpecialty();
        }
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $model->scenario = ESpecialty::SCENARIO_HIGHER_DEAN;
                $model->_department = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } else {
            $model->scenario = ESpecialty::SCENARIO_HIGHER;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __(
                            'Specialty `{name}` deleted successfully.',
                            [
                                'name' => $model->code,
                            ]
                        )
                    );
                    return $this->redirect(['special']);
                }
            } catch (Exception $e) {
                if ($e->getCode() == 23503) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(['special']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($id) {
                if ($model->_education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
                    if ($specialty = BachelorSpeciality::findOne($model->specialty_id)) {
                        $model->_bachelor_specialty = $specialty->id;
                        if (ESpecialty::getSpecialtyExist($model->_bachelor_specialty, $model->_education_type, $model->_department) > 1) {
                            $this->addError(__('The selected specialty is already attached to the faculty'));
                            return $this->redirect(['special']);
                        }
                    }
                } elseif ($model->_education_type == EducationType::EDUCATION_TYPE_MASTER) {
                    if ($specialty = MasterSpeciality::findOne($model->specialty_id)) {
                        $model->_master_specialty = $specialty->id;

                        if (ESpecialty::getSpecialtyExist($model->_master_specialty, $model->_education_type, $model->_department) > 1) {
                            $this->addError(__('The selected specialty is already attached to the faculty'));
                            return $this->redirect(['special']);
                        }
                    }
                }
            } else {
                if ($model->_education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
                    if ($specialty = BachelorSpeciality::findOne($model->specialty_id)) {
                        $model->_bachelor_specialty = $specialty->id;
                        if (ESpecialty::getSpecialtyExist($model->_bachelor_specialty, $model->_education_type, $model->_department) > 0) {
                            $this->addError(__('The selected specialty is already attached to the faculty'));
                            return $this->redirect(['special']);
                        }
                    }
                } elseif ($model->_education_type == EducationType::EDUCATION_TYPE_MASTER) {
                    if ($specialty = MasterSpeciality::findOne($model->specialty_id)) {
                        $model->_master_specialty = $specialty->id;

                        if (ESpecialty::getSpecialtyExist($model->_master_specialty, $model->_education_type, $model->_department) > 0) {
                            $this->addError(__('The selected specialty is already attached to the faculty'));
                            return $this->redirect(['special']);
                        }
                    }
                }
            }

            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Specialty `{name}` updated successfully.',
                            [
                                'name' => $model->code,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Specialty `{name}` created successfully.',
                            [
                                'name' => $model->code,
                            ]
                        )
                    );
                }
                return $this->redirect(['special']);
            }
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionGroup()
    {
        $searchModel = new EGroup();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_specialty._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        return $this->render(
            'group',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionGroupEdit($id = false)
    {
        if ($id) {
            $model = $this->findGroupModel($id);
            $model->scenario = EGroup::SCENARIO_INSERT;
        } else {
            $model = new EGroup();
            $model->scenario = EGroup::SCENARIO_INSERT;
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

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __(
                            'Group `{name}` deleted successfully.',
                            [
                                'name' => $model->name,
                            ]
                        )
                    );
                    return $this->redirect(['group']);
                }
            } catch (Exception $e) {
                if ($e->getCode() == 23503) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(['group-edit', 'id' => $model->id]);
        }


        if ($model->load(Yii::$app->request->post())) {
            $curriculum = ECurriculum::findOne($model->_curriculum);
            $model->_department = $curriculum->_department;
            $model->_education_type = $curriculum->_education_type;
            $model->_education_form = $curriculum->_education_form;
            $model->_specialty_id = $curriculum->_specialty_id;
            if ($model->save()) {
                EStudent::setSyncRequiredByGroup($model);

                if ($id) {
                    $this->addSuccess(
                        __(
                            'Group `{name}` updated successfully.',
                            [
                                'name' => $model->name,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Group `{name}` created successfully.',
                            [
                                'name' => $model->name,
                            ]
                        )
                    );
                }
                $student_meta = EStudentMeta::findOne(['_group' => $model->id]);
                if (($student_meta = EStudentMeta::find()->where(['_group' => $model->id])->one()) !== null) {
                    EStudentMeta::updateAll(
                        [
                            '_curriculum' => $model->_curriculum,
                            '_department' => $model->_department,
                            '_education_type' => $model->_education_type,
                            '_education_form' => $model->_education_form,
                            '_specialty_id' => $model->_specialty_id,
                        ],
                        [
                            '_group' => $model->id,
                        ]
                    );
                }
            }
            return $this->redirect(['group']);
        }

        return $this->renderView(
            [
                'model' => $model,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionStudent()
    {
        $searchModel = new EStudent();
        $faculty = null;

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if ($this->_user()->employee && $this->_user()->employee->deanFaculties) {
                $faculty = $this->_user()->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $dataProvider = $searchModel->searchByFaculty($this->_user(), $this->getFilterParams(), $this->get('problems'));

        return $this->render(
            'student',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    /**
     * @resource student/delete-from-database
     */
    public function actionStudentEdit($id = false, $ctgId = null)
    {
        $ctg = $ctgId !== null;
        if ($this->get('pin_hint')) {
            return $this->renderFile('@backend/views/layouts/pin.php');
        }

        $passport = $this->get('passport');
        $pin = $this->get('pin');
        $citizenship = $this->get('citizenship');

        $lang = Yii::$app->language;
        if ($pin && $passport && $citizenship) {
            $result = [];
            if (HEMIS_INTEGRATION) {
                if ($citizenship == '11') {
                    try {
                        if ($data = HemisApi::getApiClient()->getPassportData($passport, $pin)) {
                            $result['success'] = true;
                            if ($lang === Config::LANGUAGE_ENGLISH) {
                                $result['first_name'] = is_string($data->name_engl) ? $data->name_engl : '';
                                $result['second_name'] = is_string($data->surname_engl) ? $data->surname_engl : '';
                            } else {
                                $result['first_name'] = $data->name_latin;
                                $result['second_name'] = $data->surname_latin;
                            }
                            $result['third_name'] = $data->patronym_latin;
                            $result['birth_date'] = $data->birth_date;
                            $result['gender'] = $data->sex == 1 ? Gender::GENDER_MALE : Gender::GENDER_FEMALE;
                            $this->setSessionPassportData(
                                $passport,
                                [
                                    'name_en' => is_string($data->name_engl) ? $data->name_engl : '',
                                    'surname_en' => is_string($data->surname_engl) ? $data->surname_engl : '',
                                    'name_latin' => is_string($data->name_latin) ? $data->name_latin : '',
                                    'surname_latin' => is_string($data->surname_latin) ? $data->surname_latin : '',
                                    'patronym_latin' => is_string($data->patronym_latin) ? $data->patronym_latin : '',
                                ]
                            );
                        }
                    } catch (HemisApiError $e) {
                        $result['success'] = false;
                        $result['manual'] = false;
                        $result['error'] = __($e->getMessage());
                    } catch (\Exception $e) {
                        $result['success'] = false;
                        $result['manual'] = true;
                        $result['error'] = __(
                            'Server bilan ulanishda xatolik vujudga keldi, ma\'lumotlarni kiritishda davom eting'
                        );
                    }
                }

                $studentData = [
                    'success' => true
                ];
                try {
                    if ($data = HemisApi::getApiClient()->validateStudentData($citizenship == '11' ? $pin : $passport)) {
                    }
                } catch (HemisApiError $e) {
                    $studentData['success'] = false;
                    $studentData['code'] = $e->getCode();
                    $studentData['error'] = $e->getMessage();
                } catch (\Exception $e) {
                    $studentData['success'] = false;
                    $studentData['error'] = __(
                        'Server bilan ulanishda xatolik vujudga keldi, ma\'lumotlarni kiritishda davom eting'
                    );
                }
            }


            $studentItems = [];
            $content = [];

            if ($id == false && $this->get('from') == null) {
                $content = $this->renderPartial('@backend/views/student/student-edit-rows', [
                    'students' => $studentItems = EStudent::find()
                        ->with(['meta'])
                        ->where(['passport_pin' => $pin])
                        ->orFilterWhere(['passport_number' => $passport])
                        ->all()
                ]);
            }

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'hemis' => $result,
                'students' => count($studentItems),
                'content' => $content,
                'studentData' => $studentData,
            ];
        }
        $old_group = "";
        $user = $this->_user();
        if ($id) {
            $model = $this->findStudentModel($id);

            if ($user->role->isTutorRole()) {
                if (!$user->canAccessToGroup($model->meta->group)) {
                    $this->addError(__("Tutor {name}ga ushbu {group} guruhi biriktirilmagan", ['name' => $user->getFullName(), 'group' => $model->meta->group->name]));
                    return $this->redirect(['student/student-contingent']);
                }
            }

            $contingent = $ctg ? $this->findStudentMetaModel2($ctgId) : $model->meta;
            if ($contingent == null) {
                $contingent = new EStudentMeta();
            }
            $old_group = $contingent->_group;

            $model->scenario = EStudent::SCENARIO_INSERT;
            $contingent->scenario = EStudentMeta::SCENARIO_INSERT;
        } else {
            $model = new EStudent();
            $model->scenario = EStudent::SCENARIO_INSERT;
            $contingent = new EStudentMeta();
            $contingent->scenario = EStudentMeta::SCENARIO_INSERT;
        }
        $faculty = "";
        if ($this->_user()->role->isDeanRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $contingent->_department = $faculty;
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
        if (Yii::$app->request->isAjax && $contingent->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($contingent);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['student-edit', 'id' => $model->id]);
        }
        if ($this->get('reset')) {
            $model->setPassword($model->passport_number);
            if ($model->save(false)) {
                $this->addSuccess(__('Password of {name} reset successfully', ['name' => $model->getFullName()]));
            }
            return $this->redirect([$ctg ? 'student-contingent' : 'student']);
        }

        if ($this->get('delete')) {
            if ($this->canAccessToResource('student/delete-from-database')) {
                /*if (($contingent->_semestr == Semester::SEMESTER_FIRST && $contingent->_student_status == StudentStatus::STUDENT_TYPE_STUDIED) || $contingent->_student_status == StudentStatus::STUDENT_TYPE_APPLIED) {
                    EStudentSubject::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EAttendance::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EPerformance::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentTaskActivity::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    ESubjectTaskStudent::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EExamStudent::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EAcademicInformation::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    ETranscriptSubject::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EAcademicInformationData::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EAcademicInformationDataSubject::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );

                    EStudentEmployment::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentDiploma::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EAcademicRecord::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EDecreeStudent::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentGpa::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentPtt::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentAward::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentContract::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    EStudentContractType::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );

                    EStudentMeta::deleteAll(
                        ['AND',
                            ['_student' => $model->id]
                        ]
                    );
                    $message = false;
                    if ($model->tryToDelete(
                        function () use ($model) {
                            return $this->syncModelToApi($model, true);
                        },
                        $message
                    )) {
                        $this->addSuccess(
                            __(
                                'Student `{name}` deleted successfully.',
                                [
                                    'name' => $model->fullName,
                                ]
                            )
                        );
                        $r = Yii::$app->request->referrer;
                        if ($r && strpos($r, 'student-contingent-edit')) {
                            return $this->redirect(['student-contingent']);
                        }

                        return $this->redirect([$ctg ? 'student-contingent' : 'student']);
                    } else {
                        if ($message) {
                            $this->addError($message);
                        }
                    }
                }*/ //else {
                $message = false;
                if ($model->tryToDelete(
                    function () use ($model) {
                        return $this->syncModelToApi($model, true);
                    },
                    $message
                )) {
                    $this->addSuccess(
                        __(
                            'Student `{name}` deleted successfully.',
                            [
                                'name' => $model->fullName,
                            ]
                        )
                    );
                    $r = Yii::$app->request->referrer;
                    if ($r && strpos($r, 'student-contingent-edit')) {
                        return $this->redirect(['student-contingent']);
                    }

                    return $this->redirect([$ctg ? 'student-contingent' : 'student']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                //}
            }
            return $this->redirect(['student-edit', 'id' => $model->id]);
        }

        if ($model->isNewRecord) {
            if ($f = $this->get('from')) {
                if ($item = EStudent::findOne($f)) {
                    $model->setAttributes($item->getAttributes([
                        'passport_pin',
                        'passport_number',
                        'birth_date',
                        '_gender',
                        '_nationality',
                        '_citizenship',
                        '_country',
                        '_province',
                        '_current_province',
                        '_current_district',
                        '_district',
                        '_accommodation',
                        '_social_category',
                        'home_address',
                        'current_address',
                        'phone',
                        'image',
                        'other',
                        '_translations',
                    ]), false);

                    if ($model->_citizenship != '11') {
                        $model->setAttributes($item->getAttributes([
                            'first_name',
                            'second_name',
                            'third_name',
                        ]));
                    } else {
                        if ($value = $this->getSessionPassportData($model->passport_number, 'name_latin')) {
                            $model->first_name = $value;
                        }
                        if ($value = $this->getSessionPassportData($model->passport_number, 'surname_latin')) {
                            $model->second_name = $value;
                        }
                        if ($value = $this->getSessionPassportData($model->passport_number, 'patronym_latin')) {
                            $model->third_name = $value;
                        }
                    }
                }
            } else {
                $model->_citizenship = '11';
                $model->_nationality = '1161';
                $model->_gender = '11';
                $model->_country = 'UZ';
                $model->_province = '1726';
                $model->_current_province = '1726';
                $model->year_of_enter = date('Y');
                $model->_accommodation = '15';
                $model->_student_type = '11';
            }
        }

        if ($model->load(Yii::$app->request->post()) && $contingent->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if ($model->save()) {
                        if ($value = $this->getSessionPassportData($model->passport_number, 'name_en')) {
                            $model->setTranslation('first_name', $value, Config::LANGUAGE_ENGLISH);
                        }
                        if ($value = $this->getSessionPassportData($model->passport_number, 'surname_en')) {
                            $model->setTranslation('second_name', $value, Config::LANGUAGE_ENGLISH);
                        }
                        if ($value = $this->getSessionPassportData($model->passport_number, 'name_latin')) {
                            $model->setTranslation('first_name', $value, Config::LANGUAGE_UZBEK);
                        }
                        if ($value = $this->getSessionPassportData($model->passport_number, 'surname_latin')) {
                            $model->setTranslation('second_name', $value, Config::LANGUAGE_UZBEK);
                        }
                        if ($value = $this->getSessionPassportData($model->passport_number, 'patronym_latin')) {
                            $model->setTranslation('third_name', $value, Config::LANGUAGE_UZBEK);
                        }
                        if ($model->updateAttributes(['_translations' => $model->_translations])) {
                            $model->refresh();
                        };


                        $contingent->_student = $model->id;
                        $special = ESpecialty::findOne(['id' => $contingent->_specialty_id]);
                        $contingent->_education_type = $special->_education_type;
                        //$student_subject = EStudentSubject::findOne(['_semester' => $contingent->_semestr, '_curriculum' => $contingent->_curriculum, '_student'=>$contingent->_student, '_education_year' => $contingent->_education_year, '_group' => $old_group]);
                        if ((int)$old_group) {
                            if (($student_subject = EStudentSubject::find()->where(
                                    [
                                        '_semester' => $contingent->_semestr,
                                        '_curriculum' => $contingent->_curriculum,
                                        '_student' => $contingent->_student,
                                        '_education_year' => $contingent->_education_year,
                                        '_group' => $old_group,
                                    ]
                                )->one()) !== null) {
                                //try {
                                EStudentSubject::updateAll(
                                    [
                                        '_semester' => $contingent->_semestr,
                                        '_curriculum' => $contingent->_curriculum,
                                        '_education_year' => $contingent->_education_year,
                                        '_group' => $contingent->_group,
                                    ],
                                    [
                                        '_curriculum' => $contingent->_curriculum,
                                        '_student' => $contingent->_student,
                                        '_group' => $old_group,
                                        '_semester' => $contingent->_semestr,
                                    ]
                                );
                                //}
                                /*catch (Exception $e) {
                                    $this->addError($e->getMessage());
                                }*/
                            }
                        }
                        //$contingent->_department = $faculty;
                        if ($contingent->isNewRecord) {
                            $contingent->_student_status = StudentStatus::STUDENT_TYPE_APPLIED;
                        }

                        if ($contingent->save(false)) {
                            $transaction->commit();
                            $oldId = $model->student_id_number;
                            $this->syncModelToApi($model);

                            if ($id) {
                                $this->addSuccess(
                                    __(
                                        'Student `{name}` updated successfully.',
                                        [
                                            'name' => $model->fullName,
                                        ]
                                    )
                                );
                            } else {
                                $this->addSuccess(
                                    __(
                                        'Student `{name}` created successfully.',
                                        [
                                            'name' => $model->fullName,
                                        ]
                                    )
                                );
                            }

                            if ($this->_syncError == null) {
                                if ($model->student_id_number && $oldId == null) {
                                    $this->addSuccess(
                                        __(
                                            'Student synced to HEMIS API and generated id {b}{student_id}{/b}',
                                            ['student_id' => $model->student_id_number]
                                        )
                                    );
                                }
                            }

                            $this->clearSessionPassportData($model->passport_number);
                            return $this->redirect(
                                [$ctg ? 'student-contingent-edit' : 'student-edit', 'id' => $ctg ? $ctgId : $model->id]
                            );
                        }
                    }
                } catch (IntegrityException $exception) {
                    $transaction->rollBack();
                    $this->addError($exception->getMessage(), true, false);
                    $this->addError(__('Talabani saqlashda xatolik yuz berdi'), false);
                } catch (\Exception $exception) {
                    $transaction->rollBack();
                    $this->addError($exception->getMessage(), true);
                }
            } else {
                $this->addError($model->getOneError());
            }
        }

        return $this->render(
            'student-edit',
            [
                'model' => $model,
                'contingent' => $contingent,
                'faculty' => $faculty,
                'ctg' => $ctg,
            ]
        );
    }

    public function actionStudentPassportEdit($id)
    {
        $passport = $this->get('passport');
        $pin = $this->get('pin');
        if ($this->get('pin_hint')) {
            return $this->renderFile('@backend/views/layouts/pin.php');
        }

        if ($pin && $passport) {
            $result = [];
            if (HEMIS_INTEGRATION) {
                try {
                    if ($data = HemisApi::getApiClient()->getPassportData($passport, $pin)) {
                        $result['success'] = true;
                        $result['first_name'] = $data->name_latin;
                        $result['second_name'] = $data->surname_latin;
                        $result['third_name'] = $data->patronym_latin;
                        $result['birth_date'] = $data->birth_date;
                        $result['gender'] = $data->sex == 1 ? Gender::GENDER_MALE : Gender::GENDER_FEMALE;
                    }
                } catch (HemisApiError $e) {
                    $result['success'] = false;
                    $result['manual'] = false;
                    $result['error'] = __($e->getMessage());
                } catch (\Exception $e) {
                    $result['success'] = false;
                    $result['manual'] = true;
                    $result['error'] = __(
                        'Server bilan ulanishda xatolik vujudga keldi, ma\'lumotlarni kiritishda davom eting'
                    );
                }
            }

            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }
        $model = $this->findStudentModel($id);

        $model->scenario = EStudent::SCENARIO_INSERT;
        $model->scenario_passport_edit = true;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($model->save()) {
                    $transaction->commit();
                    $this->syncModelToApi($model);
                    $this->addSuccess(
                        __(
                            'Passport information of `{name}` student updated successfully.',
                            [
                                'name' => $model->fullName,
                            ]
                        )
                    );
                    if ($model->student_id_number !== null && $model->getOldAttribute('student_id_number') === null) {
                        $this->addSuccess(
                            __(
                                'Student synced to HEMIS API and generated id {b}{student_id}{/b}',
                                ['student_id' => $model->student_id_number]
                            )
                        );
                    }
                    return $this->redirect(['student/student-passport-edit', 'id' => $model->id]);
                }
            } catch (IntegrityException $exception) {
                if ($transaction !== null) {
                    $transaction->rollBack();
                }
                $this->addError($exception->getMessage(), true, false);
                $this->addError(__('Talabani saqlashda xatolik yuz berdi'));
            } catch (\Exception $exception) {
                $transaction->rollBack();
                $this->addError($exception->getMessage(), true);
            }
        }
        if ($model->hasErrors()) {
            $this->addError($model->getOneError());
        }

        return $this->render(
            'student-passport-edit',
            [
                'model' => $model,
                'ctg' => $this->get('ctgId', false),
            ]
        );
    }

    public function actionStudentFixed()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search_fixed($this->getFilterParams());
        $faculty = $this->_user()->role->isDeanRole() ? Yii::$app->user->identity->employee->deanFaculties->id : false;
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_APPLIED]);
        if ($this->_user()->role->isDeanRole())
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionToFixedGroups()
    {
        /**
         * @var $model EStudentMeta
         * @var $_group EGroup
         * @var $_level Course
         * @var $_semester Semester
         */
        $selection = (array)Yii::$app->request->post('selection');
        $_curriculum = ECurriculum::findOne((int)Yii::$app->request->post('curriculum', -1));
        $_group = EGroup::findOne((int)Yii::$app->request->post('group', -1));
        //   $_semester = Semester::findOne(['code' => Yii::$app->request->post('semester'), 'active' => true]);
        $_semester = Semester::getByCurriculumSemester($_curriculum->id, Yii::$app->request->post('semester'));
        $_level = Course::findOne(['code' => Yii::$app->request->post('level'), 'active' => true]);
        $_education_year = EducationYear::findOne(['code' => Yii::$app->request->post('education_year'), 'active' => true]);

        if (is_array($selection) && $_curriculum && $_group && $_semester && $_level && $_education_year) {
            $success = 0;
            foreach ($selection as $id) {
                $model = EStudentMeta::findOne((int)$id);
                if($model->student->_sync_status == 'actual'){
                    if ($duplicated = EStudentMeta::findOne(
                        [
                            '_curriculum' => $_curriculum->id,
                            '_student' => $model->_student,
                            '_education_type' => $model->_education_type,
                            '_education_year' => $_education_year->code,
                            '_semestr' => $_semester->code,
                            '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        ]
                    )) {
                        if ($duplicated->id != $model->id) {
                            $duplicated->delete();
                        }
                    }
                    $model->_group = $_group->id;
                    $model->_semestr = $_semester->code;
                    $model->_level = $_level->code;
                    $model->_student_status = StudentStatus::STUDENT_TYPE_STUDIED;
                    $model->_education_form = $_curriculum->_education_form;
                    $model->_curriculum = $_curriculum->id;
                    $model->_education_year = $_education_year->code;
                    if ($model->save(false)) {
                        $model->student->setAsShouldBeSynced();
                        $success++;
                    }
                }

                if ($success) {
                    $this->addSuccess(
                        __('{count} students assigned to group {group}', ['count' => $success, 'group' => $_group->name])
                    );
                }

            }

            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionStudentContingent()
    {
        $searchModel = new EStudentMeta();
        $department = null;
        $groups = null;
        $user = $this->_user();

        if ($user->role->isDeanOrTutorRole()) {
            if ($user->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } else if ($this->_user()->role->isTutorRole()) {
            $groups = ArrayHelper::getColumn($this->_user()->tutorGroups, 'id');
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
            $query = $searchModel->searchContingent($this->getFilterParams(), $department, false);

            $countQuery = clone $query;
            $limit = 2000;
            if ($countQuery->count() <= $limit) {
                $fileName = EStudentMeta::generateDownloadFile($query);

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
                        new StudentContingentFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'department' => $department,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['student/student-contingent', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Talabalar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar saxifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['student/student-contingent']);
            }
        }

        if ($this->get('contingent-download')) {
            $query = $searchModel->searchContingent($this->getFilterParams(), $department, false);

            $countQuery = clone $query;
            $limit = 2000;
            if ($countQuery->count() <= $limit) {
                $fileName = EStudentMeta::generateContingentDownloadFile($query);

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
                        new ContingentStudentContingentFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'department' => $department,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['student/student-contingent', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Talabalar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar saxifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['student/student-contingent']);
            }
        }

        $dataProvider = $searchModel->searchContingent($this->getFilterParams(), $department);

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $department,
            ]
        );
    }

    public function actionStudentContingentEdit($id = false)
    {
        //UPDATE_STUDENT
        $contingent = $this->findStudentMetaModel2($id);
        return $this->actionStudentEdit($contingent->_student, $id);

        $model = $this->findStudentModel($contingent->_student);
        $model->scenario = EStudent::SCENARIO_INSERT;
        $contingent->scenario = EStudentMeta::SCENARIO_UPDATE;
        $model->birth_date = Yii::$app->formatter->asDate($model->birth_date, 'php:Y-m-d');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()) && $contingent->load(
                Yii::$app->request->post()
            )) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
            return ActiveForm::validate($contingent);
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
        if ($model->load(Yii::$app->request->post()) && $contingent->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $contingent->_student = $model->id;
                $special = ESpecialty::findOne(['id' => $contingent->_specialty_id]);
                $contingent->_education_type = $special->_education_type;
                $contingent->_student_status = StudentStatus::STUDENT_TYPE_STUDIED;
                //$_semester = Semester::getByCurriculumSemester($contingent->_curriculum, $contingent->_semestr);
                //$contingent->_education_year = $_semester->_education_year;
                $contingent->save(false);

                $student_subject = EStudentSubject::findOne(
                    [
                        '_semester' => $contingent->_semestr,
                        '_curriculum' => $contingent->_curriculum,
                        '_student' => $contingent->_student,
                        '_education_year' => $contingent->_education_year,
                    ]
                );
                if (($student_meta = EStudentSubject::find()->where(
                        [
                            '_semester' => $contingent->_semestr,
                            '_curriculum' => $contingent->_curriculum,
                            '_student' => $contingent->_student,
                            '_education_year' => $contingent->_education_year,
                        ]
                    )->one()) !== null) {
                    EStudentSubject::updateAll(
                        [
                            //      '_curriculum' => $contingent->_curriculum,
                            '_education_year' => $contingent->_education_year,
                            '_group' => $contingent->_group,
                        ],
                        [
                            '_semestr' => $contingent->_semestr,
                            '_curriculum' => $contingent->_curriculum,
                            '_student' => $contingent->_student,
                        ]
                    );
                }
                return $this->redirect(['student-contingent']);
            }

            $model->save();
            $contingent->save();

            if ($id) {
                $this->addSuccess(
                    __(
                        'Student `{name}` updated successfully.',
                        [
                            'name' => $model->fullName,
                        ]
                    )
                );
            } else {
                $this->addSuccess(
                    __(
                        'Student `{name}` created successfully.',
                        [
                            'name' => $model->fullName,
                        ]
                    )
                );
            }

            return $this->redirect(['student-contingent']);
        }

        return $this->render(
            '/student/student-edit',
            [
                'model' => $model,
                'contingent' => $contingent,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionStudentNotMeta()
    {
        $students = EStudentMeta::find()->all();
        $list = array();
        foreach ($students as $student) {
            $list [$student->_student] = $student->_student;
        }
        $searchModel = new EStudent();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['not in', 'id', $list]);

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionAdmissionQuota()
    {
        $searchModel = new EAdmissionQuota();
        $dataProvider = $searchModel->search($this->getFilterParams());

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionAdmissionQuotaEdit($id = false)
    {
        if ($id) {
            if (($model = EAdmissionQuota::findOne($id)) === null) {
                $this->notFoundException();
            }
            $model->scenario = EAdmissionQuota::SCENARIO_UPDATE;
        } else {
            $model = new EAdmissionQuota(['scenario' => EAdmissionQuota::SCENARIO_INSERT]);
        }
        $searchModel = new EAdmissionQuota();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            try {
                $model->delete();
                $this->addSuccess(__('Admission quota `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['student/admission-quota-edit']);
            } catch (IntegrityException $exception) {
                $this->addError(__('Could not delete related data'));
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if ($model->isNewRecord) {
                    $this->addSuccess(
                        __(
                            'Admission quota `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Admission quota `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['student/admission-quota-edit', 'id' => $model->id]);
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

    public function actionStudentAward()
    {
        $searchModel = new EStudentAward(['scenario' => 'search']);
        $dataProvider = $searchModel->search($this->getFilterParams());

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionStudentAwardEdit($id = false)
    {
        if ($id) {
            if (($model = EStudentAward::findOne($id)) === null) {
                $this->notFoundException();
            }
            $model->scenario = EStudentAward::SCENARIO_UPDATE;
        } else {
            $model = new EStudentAward(['scenario' => EStudentAward::SCENARIO_INSERT]);
        }
        $searchModel = new EStudentAward();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            try {
                $model->delete();
                $this->addSuccess(__('Student award `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['student/student-award']);
            } catch (IntegrityException $exception) {
                $this->addError(__('Could not delete related data'));
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if (!$id) {
                    $this->addSuccess(
                        __(
                            'Student award `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Student award `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['student/student-award-edit', 'id' => $model->id]);
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

    public function actionQualification()
    {
        $searchModel = new EQualification();
        if ($this->_user()->role->isDeanRole()) {
            $searchModel->_faculty = $this->_user()->employee->deanFaculties->id;
        }
        $dataProvider = $searchModel->search($this->getFilterParams());

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionQualificationEdit($id = false)
    {
        if ($id) {
            if (($model = EQualification::findOne($id)) === null) {
                $this->notFoundException();
            }
            $model->scenario = EQualification::SCENARIO_UPDATE;
        } else {
            $model = new EQualification(['scenario' => EQualification::SCENARIO_INSERT]);
        }
        $searchModel = new EQualification();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            try {
                $model->delete();
                $this->addSuccess(__('Qualification `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['student/qualification']);
            } catch (IntegrityException $exception) {
                $this->addError(__('Could not delete related data'));
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
            return $this->redirect(['student/qualification-edit', 'id' => $model->id]);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if (!$id) {
                    $this->addSuccess(
                        __(
                            'Qualification `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Qualification `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['student/qualification-edit', 'id' => $model->id]);
            }
        }

        if ($this->request->isPost && $model->hasErrors()) {
            $this->addError($model->getOneError());
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => $model,
            ]
        );
    }

    protected function findSpecialModel($id)
    {
        if (($model = ESpecialty::findOne($id)) !== null) {
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

    /**
     * @param $id
     * @return EStudent
     * @throws NotFoundHttpException
     */
    protected function findStudentModel($id)
    {
        if (($model = EStudent::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentMetaModel($id)
    {
        if (($model = EStudentMeta::find()->where(['_student' => $id])->one()) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /**
     * @param $id
     * @return EStudentMeta
     * @throws NotFoundHttpException
     */
    protected function findStudentMetaModel2($id)
    {
        if (($model = EStudentMeta::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    private function setSessionPassportData($student, $data)
    {
        Yii::$app->session->set('session_passport_data_' . $student, $data);
    }

    private function getSessionPassportData($student, $field = null)
    {
        $data = Yii::$app->session->get('session_passport_data_' . $student);
        if ($field !== null && isset($data[$field])) {
            return $data[$field];
        }
        return '';
    }

    private function clearSessionPassportData($student)
    {
        return Yii::$app->session->remove('session_passport_data_' . $student);
    }
}
