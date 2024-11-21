<?php

namespace backend\controllers;

use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiError;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeAcademicDegree;
use common\models\employee\EEmployeeCompetition;
use common\models\employee\EEmployeeForeign;
use common\models\employee\EEmployeeMeta;
use common\models\employee\EEmployeeProfessionalDevelopment;
use common\models\employee\EEmployeeTraining;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\system\Admin;
use common\models\system\AdminGroup;
use common\models\system\AdminRole;
use common\models\system\classifier\Gender;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\ScienceBranch;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\TeacherStatus;
use common\models\system\classifier\TrainingType;
use common\models\system\job\EmployeeContingentFileGenerateJob;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\queue\redis\Queue;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class EmployeeController extends BackendController
{
    public $activeMenu = 'employee';

    public function actionTutorGroup($id = false)
    {
        /**
         * @var $role AdminRole
         */
        if ($role = AdminRole::findOne(['code' => AdminRole::CODE_TUTOR])) {

            $faculty = $this->_user()->role->isDeanRole() ? @$this->_user()->employee->deanFaculties->id : null;

            if ($id) {
                /**
                 * @var $model Admin
                 * @var $group EGroup
                 */
                if ($model = Admin::findOne($id)) {
                    if ($model->employee == null) {
                        $this->addError(__('Ushbu foydalanuvchida lavozim ma\'lumotlari mavjud emas'));
                        return $this->redirect(['employee/tutor-group']);
                    }

                    if ($model->hasRole($role)) {
                        if (
                            $faculty == null ||
                            EEmployeeMeta::find()->where([
                                '_employee' => $model->_employee,
                                '_department' => $faculty
                            ])->count()) {

                            $searchModel = new EGroup();

                            if ($group = $this->get('group')) {
                                if ($group = EGroup::findOne($group)) {
                                    if ($group->department->id == $faculty || in_array($group->_department, ArrayHelper::getColumn($model->employee->departments, 'id'))) {
                                        $data = ['_admin' => $model->id, '_group' => $group->id];
                                        if (isset($model->tutorGroups[$group->id])) {
                                            AdminGroup::deleteAll($data);
                                        } else {
                                            (new AdminGroup($data))->save();
                                        }
                                    }
                                }
                                return [];
                            }

                            return $this->render(
                                'tutor-group-edit',
                                [
                                    'model' => $model,
                                    'searchModel' => $searchModel,
                                    'dataProvider' => $searchModel->searchForTutor($this->get(), $model, $faculty),
                                ]
                            );
                        }
                    }
                }

                return $this->redirect(['employee/tutor-group']);
            }

            $searchModel = new Admin();

            return $this->renderView(
                [
                    'dataProvider' => $searchModel->searchForTutor($this->getFilterParams(), $faculty),
                    'searchModel' => $searchModel,
                ]
            );
        }
    }


    public function actionAccount($id = false)
    {
        if ($model = $this->findEmployeeModel($id)) {
            $admin = $model->admin;
            if ($admin) {
                if ($this->get('delete')) {
                    try {
                        if ($admin->delete()) {
                            $this->addSuccess(
                                __(
                                    'Administrator `{name}` deleted successfully.',
                                    [
                                        'name' => $admin->login,
                                    ]
                                )
                            );
                        }
                    } catch (Exception $e) {
                        $this->addError($e->getMessage());
                    }

                    return $this->redirect(['employee', 'id' => $model->id]);
                }

                $admin->scenario = Admin::SCENARIO_UPDATE;
            } else {
                $admin = $model->createAdmin();
                $admin->scenario = Admin::SCENARIO_INSERT;
            }


            if (Yii::$app->request->isAjax && $admin->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ActiveForm::validate($admin);
            }

            if ($admin->load(Yii::$app->request->post()) && $admin->save()) {
                $this->addSuccess(
                    __(
                        'Account `{name}` updated successfully.',
                        [
                            'name' => $admin->login,
                        ]
                    )
                );

                if ($admin->change_password) {
                    $this->addSuccess(
                        __(
                            'Password `{name}` updated successfully.',
                            [
                                'name' => $admin->login,
                            ]
                        )
                    );
                }

                return $this->redirect(['employee', 'id' => $model->id, 'account' => 1]);
            }

            return $this->render('employee-admin', ['model' => $admin, 'employee' => $model]);
        }
    }

    public function actionAcademicDegree($id = false, $edit = false)
    {
        $searchModel = new EEmployeeAcademicDegree();
        $model = false;
        if ($id) {
            $model = EEmployeeAcademicDegree::findOne($id);
            if ($model == null) {
                $this->notFoundException();
            }
        } elseif ($edit) {
            $model = new EEmployeeAcademicDegree([
                'diploma_type' => EEmployeeAcademicDegree::TYPE_RANK,
                '_education_year' => EducationYear::getCurrentYear()->code,
                '_country' => 'UZ',
            ]);
        }

        if ($model) {
            if ($this->checkModelToApi($model)) {
                return $this->redirect(['academic-degree', 'id' => $model->id]);
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
                        __('Academic information of {name} deleted successfully', ['name' => $model->employee->fullName])
                    );
                    return $this->redirect(['academic-degree']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                return $this->redirect(['academic-degree', 'id' => $model->id]);
            }

            if ($this->get('employees')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    ArrayHelper::getColumn(
                        (new EEmployee(['search' => $this->get('q')]))
                            ->searchContingent([], false)
                            ->all(),
                        function (EEmployee $data) {
                            return ['id' => $data->id, 'name' => $data->fullName, 'code' => $data->employee_id_number];
                        })
                ];
            }
            if ($this->get('specialties')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    ScienceBranch::find()
                        ->orWhereLike('name', $q = $this->get('query'))
                        ->orWhereLike('code', $q)
                        ->andWhere(['active' => true])
                        ->all(),
                    function (ScienceBranch $data) {
                        return ['code' => $data->code, 'short' => StringHelper::truncateWords($data->name, 5), 'name' => $data->name];
                    });
            }
            if ($model->load($this->post())) {
                if ($model->save()) {
                    $this->syncModelToApi($model);

                    $this->addSuccess(__($id ? 'Academic information for {name} updated successfully' : 'Academic information for {name} created successfully', ['name' => $model->employee->fullName]));

                    return $this->redirect(['academic-degree', 'id' => $model->id]);
                }
            }
            return $this->render('academic-degree-edit', ['model' => $model]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionForeignTraining($id = false, $edit = false)
    {
        $searchModel = new EEmployeeTraining();
        $model = false;
        if ($id) {
            $model = EEmployeeTraining::findOne($id);
            if ($model == null) {
                $this->notFoundException();
            }
        } elseif ($edit) {
            $model = new EEmployeeTraining([
                '_training_type' => TrainingType::TRAINING_TYPE_LECTURE,
                '_education_year' => EducationYear::getCurrentYear()->code,
            ]);
        }

        if ($model) {
            if ($this->checkModelToApi($model)) {
                return $this->redirect(['foreign-training', 'id' => $model->id]);
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
                        __('Foreign training of {name} deleted successfully', ['name' => $model->employee->fullName])
                    );
                    return $this->redirect(['foreign-training']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                return $this->redirect(['foreign-training', 'id' => $model->id]);
            }

            if ($this->get('employees')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    ArrayHelper::getColumn(
                        (new EEmployee(['search' => $this->get('q')]))
                            ->searchContingent([], false)
                            ->all(),
                        function (EEmployee $data) {
                            return ['id' => $data->id, 'name' => $data->fullName, 'code' => $data->employee_id_number];
                        })
                ];
            }
            if ($this->get('specialties')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    ScienceBranch::find()
                        ->orWhereLike('name', $q = $this->get('query'))
                        ->orWhereLike('code', $q)
                        ->andWhere(['active' => true])
                        ->all(),
                    function (ScienceBranch $data) {
                        return ['name' => $data->name];
                    });
            }
            if ($this->get('universities')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    EEmployeeTraining::find()
                        ->orWhereLike('university', $q = $this->get('query'))
                        ->all(),
                    function (EEmployeeTraining $data) {
                        return ['name' => $data->university];
                    });
            }
            if ($model->load($this->post())) {
                if ($model->save()) {
                    $this->syncModelToApi($model);

                    $this->addSuccess(__($id ? 'Foreign Training for {name} updated successfully' : 'Foreign Training for {name} created successfully', ['name' => $model->employee->fullName]));

                    return $this->redirect(['foreign-training', 'id' => $model->id]);
                }
            }
            return $this->render('foreign-training-edit', ['model' => $model]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionForeignEmployee($id = false, $edit = false)
    {
        $searchModel = new EEmployeeForeign();
        $model = false;
        if ($id) {
            $model = EEmployeeForeign::findOne($id);
            if ($model == null) {
                $this->notFoundException();
            }
        } elseif ($edit) {
            $model = new EEmployeeForeign([
                '_education_year' => EducationYear::getCurrentYear()->code,
            ]);
        }

        if ($model) {
            if ($this->checkModelToApi($model)) {
                return $this->redirect(['foreign-employee', 'id' => $model->id]);
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
                        __('Foreign employee {name} deleted successfully', ['name' => $model->full_name])
                    );
                    return $this->redirect(['foreign-employee']);
                } else {
                    if ($message) {
                        $this->addError($message);
                    }
                }
                return $this->redirect(['foreign-employee', 'id' => $model->id]);
            }

            if ($this->get('specialties')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    ScienceBranch::find()
                        ->orWhereLike('name', $q = $this->get('query'))
                        ->orWhereLike('code', $q)
                        ->andWhere(['active' => true])
                        ->all(),
                    function (ScienceBranch $data) {
                        return ['name' => $data->name];
                    });
            }
            if ($this->get('subjects')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    ESubject::find()
                        ->orWhereLike('name', $q = $this->get('query'))
                        ->orWhereLike('name_uz', $q = $this->get('query'), '_translations')
                        ->orWhereLike('name_en', $q = $this->get('query'), '_translations')
                        ->orWhereLike('name_ru', $q = $this->get('query'), '_translations')
                        ->andWhere(['active' => true])
                        ->all(),
                    function (ESubject $data) {
                        return ['name' => $data->name];
                    });
            }
            if ($this->get('work_places')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::getColumn(
                    EEmployeeForeign::find()
                        ->orWhereLike('work_places', $q = $this->get('query'))
                        ->all(),
                    function (EEmployeeForeign $data) {
                        return ['name' => $data->work_place];
                    });
            }
            if ($model->load($this->post())) {
                if ($model->save()) {
                    $this->syncModelToApi($model);

                    $this->addSuccess(__($id ? 'Foreign Employee {name} updated successfully' : 'Foreign Employee {name} created successfully', ['name' => $model->full_name]));

                    return $this->redirect(['foreign-employee', 'id' => $model->id]);
                }
            }
            return $this->render('foreign-employee-edit', ['model' => $model]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionEmployee($id = false)
    {
        if ($id) {
            if ($model = $this->findEmployeeModel($id)) {
                if ($this->checkModelToApi($model)) {
                    return $this->redirect(['employee', 'id' => $model->id]);
                }

                return $this->render('employee-view', ['model' => $model]);
            }
        }

        $searchModel = new EEmployee();

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
            $query = $searchModel->searchContingent($this->getFilterParams(), false);

            $countQuery = clone $query;
            $limit = 200;
            if ($countQuery->count() <= $limit) {
                $fileName = EEmployee::generateDownloadFile($query);

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
                        new EmployeeContingentFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['employee/employee', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Hodimlar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar saxifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['employee/employee']);
            }
        }

        return $this->renderView(
            [
                'dataProvider' => $searchModel->searchContingent($this->getFilterParams()),
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionEmployeeEdit($id = false)
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

        if ($id) {
            $model = $this->findEmployeeModel($id);
            $model->scenario = EEmployee::SCENARIO_INSERT;
        } else {
            $model = new EEmployee();
            $model->scenario = EEmployee::SCENARIO_INSERT;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
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
                    __('Employee `{name}` deleted successfully.', ['name' => $model->fullName])
                );
                return $this->redirect(['employee']);
            } else {
                if ($message) {
                    $this->addError($message);
                }
            }

            return $this->redirect(['employee-edit', 'id' => $model->id]);
        }

        $oldId = $model->employee_id_number;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __(
                            'Employee `{name}` updated successfully.',
                            [
                                'name' => $model->getFullName(),
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Employee `{name}` created successfully.',
                            [
                                'name' => $model->getFullName(),
                            ]
                        )
                    );
                }
                if ($model->employee_id_number && $oldId == null) {
                    $this->addSuccess(
                        __(
                            'Employee synced to HEMIS API and generated id {b}{employee_id}{/b}',
                            ['employee_id' => $model->employee_id_number]
                        )
                    );
                }

                return $this->redirect(['employee-edit', 'id' => $model->id]);
            }
        } else {
            if ($model->isNewRecord) {
                $model->_citizenship = '11';
                $model->_gender = '11';
                $model->year_of_enter = date('Y');
            }
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionEmployeePassportEdit($id)
    {
        $passport = $this->get('passport');
        $pin = $this->get('pin');
        if ($this->get('pin_hint')) {
            return $this->renderFile('@backend/views/layouts/pin.php');
        }
        $lang = Yii::$app->language;

        if ($pin && $passport) {
            $result = [];
            if (HEMIS_INTEGRATION) {
                try {
                    if ($data = HemisApi::getApiClient()->getPassportData($passport, $pin)) {
                        $result['success'] = true;
                        if ($lang === Config::LANGUAGE_ENGLISH) {
                            $result['first_name'] = $data->name_engl;
                            $result['second_name'] = $data->surname_engl;
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
                                'surname_en' => is_string($data->surname_engl) ? $data->surname_engl : ''
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


            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }

        $model = $this->findEmployeeModel($id);
        $model->scenario = EEmployee::SCENARIO_INSERT;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $oldId = $model->employee_id_number;
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->session->has('session_passport_data_' . $model->passport_number) && $lang !== Config::LANGUAGE_ENGLISH) {
                $model->setTranslation(
                    'first_name',
                    $this->getSessionPassportData($model->passport_number, 'name_en'),
                    Config::LANGUAGE_ENGLISH
                );
                $model->setTranslation(
                    'second_name',
                    $this->getSessionPassportData($model->passport_number, 'surname_en'),
                    Config::LANGUAGE_ENGLISH
                );
            }
            if ($model->save()) {
                $this->syncModelToApi($model);

                $this->addSuccess(
                    __(
                        'Passport information of `{name}` employee updated successfully.',
                        [
                            'name' => $model->getFullName(),
                        ]
                    )
                );
                if ($model->employee_id_number !== null && $oldId === null) {
                    $this->addSuccess(
                        __(
                            'Employee synced to HEMIS API and generated id {b}{employee_id}{/b}',
                            ['employee_id' => $model->employee_id_number]
                        )
                    );
                }
                $this->clearSessionPassportData($model->passport_number);

                return $this->redirect(['employee-passport-edit', 'id' => $model->id]);
            }
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionTeacher($id = false)
    {
        if ($this->get('edit') || $this->get('delete')) {
            if ($id == false && !$this->get('employee')) {
                $this->addInfo(__('Choose employee to register staff'));
                return $this->redirect(['employee']);
            }
            return $this->staffEdit($id, 'teacher');
        }

        return $this->staff('teacher');
    }

    public function actionDirection($id = false)
    {
        if ($this->get('edit') || $this->get('delete')) {
            if ($id == false && !$this->get('employee')) {
                $this->addInfo(__('Choose employee to register staff'));
                return $this->redirect(['employee']);
            }
            return $this->staffEdit($id, 'direction');
        }

        return $this->staff('direction');
    }

    protected function staff($type = 'teacher')
    {
        $searchModel = new EEmployeeMeta();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if ($this->get('download')) {
            $countQuery = clone $dataProvider->query;
            $limit = 200;
            if ($countQuery->count() <= $limit) {
                $fileName = EEmployeeMeta::generateDownloadFile($dataProvider->query);

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
                        new EmployeeContingentFileGenerateJob(
                            [
                                'type' => 'employee_meta',
                                'filterParams' => $this->getFilterParams(),
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['employee/employee', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Hodimlar soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar saxifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['employee/' . $type]);
            }
        }

        if ($type == 'teacher') {
            $dataProvider->query->andFilterWhere(['in', '_position', TeacherPositionType::TEACHER_POSITIONS]);
        } else {
            $dataProvider->query->andFilterWhere(['not in', '_position', TeacherPositionType::TEACHER_POSITIONS]);
        }

        return $this->render(
            'staff',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'type' => $type,
            ]
        );
    }

    public function actionEmployeeStatus()
    {
        $searchModel = new EEmployeeMeta();
        $searchModel->_position = TeacherPositionType::TEACHER_POSITIONS;
        $dataProvider = $searchModel->search_status($this->getFilterParams());
        $searchModelFix = new EEmployeeMeta();

        return $this->render(
            'status',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'searchModelFix' => $searchModelFix,
            ]
        );
    }

    public function actionProfessionalDevelopment()
    {
        $searchModel = new EEmployeeProfessionalDevelopment();
        $searchModel->scenario = 'search';
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = EDepartment::find()->select('id, parent')->where(['parent' => $faculty])->column();
                $dataProvider->query->andFilterWhere(['e_employee_meta._department' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } elseif ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                $dataProvider->query->andFilterWhere(['e_employee_meta._department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        return $this->render(
            'professional-development',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionProfessionalDevelopmentMonitoring()
    {
        $searchModel = new EEmployeeProfessionalDevelopment();
        $searchModel->scenario = 'search';
        $dataProvider = $searchModel->searchForMonitoring($this->getFilterParams());
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = EDepartment::find()->select('id, parent')->where(['parent' => $faculty])->column();
                $dataProvider->query->andFilterWhere(['_department' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } else {
            if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                if (Yii::$app->user->identity->employee->headDepartments) {
                    $department = Yii::$app->user->identity->employee->headDepartments->id;
                    $dataProvider->query->andFilterWhere(['_department' => $department]);
                } else {
                    $this->addInfo(
                        __('The institution department is not attached to your account. ')
                    );
                    return $this->goHome();
                }
            }
        }

        return $this->render(
            'professional-development-monitoring',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * @resource employee/professional-development-delete
     *
     * @param false $id
     * @return array|string|Response
     * @throws NotFoundHttpException
     */
    public function actionProfessionalDevelopmentEdit($id = false)
    {
        $department = "";
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $department = EDepartment::find()->select('id, parent')->where(['parent' => $department])->column();
            }
        } else {
            if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                if (Yii::$app->user->identity->employee->headDepartments) {
                    $department = Yii::$app->user->identity->employee->headDepartments->id;
                }
            }
        }

        if ($id) {
            $model = EEmployeeProfessionalDevelopment::findOne($id);
            if (!$model) {
                $this->notFoundException();
            }
        } else {
            $model = new EEmployeeProfessionalDevelopment();
            $model->scenario = EEmployeeProfessionalDevelopment::SCENARIO_INSERT;
        }

        if ($this->get('delete', false)) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __(
                            'Employee professional development `{name}` deleted successfully.',
                            ['name' => $model->employee->fullName]
                        )
                    );
                    return $this->redirect(['employee/professional-development']);
                }
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(
                    __(
                        'Employee {name} professional development updated successfully',
                        ['name' => $model->employee->fullName]
                    )
                );
            } else {
                $this->addSuccess(
                    __(
                        'Employee {name} professional development created successfully',
                        ['name' => $model->employee->fullName]
                    )
                );
            }
            return $this->redirect(['employee/professional-development-edit', 'id' => $model->id]);
        }

        return $this->render(
            'professional-development-edit',
            [
                'model' => $model,
                'teachers' => EEmployeeMeta::getTeachers($department),
            ]
        );
    }

    public function actionCompetition()
    {
        $searchModel = new EEmployeeCompetition();
        $searchModel->scenario = 'search';
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = EDepartment::find()->select('id, parent')->where(['parent' => $faculty])->column();
                $dataProvider->query->andFilterWhere(['e_employee_meta._department' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } elseif ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                $dataProvider->query->andFilterWhere(['e_employee_meta._department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        return $this->render(
            'competition',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionCompetitionMonitoring()
    {
        $searchModel = new EEmployeeCompetition();
        $searchModel->scenario = 'search';
        $dataProvider = $searchModel->searchForMonitoring($this->getFilterParams());
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = EDepartment::find()->select('id, parent')->where(['parent' => $faculty])->column();
                $dataProvider->query->andFilterWhere(['_department' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } else {
            if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                if (Yii::$app->user->identity->employee->headDepartments) {
                    $department = Yii::$app->user->identity->employee->headDepartments->id;
                    $dataProvider->query->andFilterWhere(['_department' => $department]);
                } else {
                    $this->addInfo(
                        __('The institution department is not attached to your account. ')
                    );
                    return $this->goHome();
                }
            }
        }

        return $this->render(
            'competition-monitoring',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * @resource employee/competition-delete
     *
     * @param false $id
     * @return array|string|Response
     * @throws NotFoundHttpException
     */
    public function actionCompetitionEdit($id = false)
    {
        $department = "";
        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $department = EDepartment::find()->select('id, parent')->where(['parent' => $department])->column();
            }
        } else {
            if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                if (Yii::$app->user->identity->employee->headDepartments) {
                    $department = Yii::$app->user->identity->employee->headDepartments->id;
                }
            }
        }

        if ($id) {
            $model = EEmployeeCompetition::findOne($id);
            if (!$model) {
                $this->notFoundException();
            }
        } else {
            $model = new EEmployeeCompetition();
            $model->scenario = EEmployeeCompetition::SCENARIO_INSERT;
        }

        if ($this->get('delete', false)) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __(
                            'Employee competition `{name}` deleted successfully.',
                            ['name' => $model->employee->fullName]
                        )
                    );
                    return $this->redirect(['employee/competition']);
                }
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(
                    __(
                        'Employee {name} competition updated successfully',
                        ['name' => $model->employee->fullName]
                    )
                );
            } else {
                $this->addSuccess(
                    __(
                        'Employee {name} competition created successfully',
                        ['name' => $model->employee->fullName]
                    )
                );
            }
            return $this->redirect(['employee/competition-edit', 'id' => $model->id]);
        }

        return $this->render(
            'competition-edit',
            [
                'model' => $model,
                'teachers' => EEmployeeMeta::getTeachers($department),
            ]
        );
    }

    protected function staffEdit($id = false, $type = 'teacher')
    {
        $employee = false;

        if ($id) {
            $model = $this->findEmployeeMetaModel($id);
            $model->scenario = EEmployeeMeta::SCENARIO_INSERT;
        } else {
            $model = new EEmployeeMeta();
            $model->_employee_status = TeacherStatus::TEACHER_STATUS_WORKING;
            $model->scenario = EEmployeeMeta::SCENARIO_UPDATE;
        }

        if ($e = $this->get('employee')) {
            if ($employee = $this->findEmployeeModel($e)) {
                $model->_employee = $employee->id;
            }
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
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
                    __('Employee Work `{name}` deleted successfully.', ['name' => $model->employee->getFullName()])
                );
                return $this->redirect(["employee/$type"]);
            } else {
                if ($message) {
                    $this->addError($message);
                }
            }


            return $this->redirect(["employee/$type", 'id' => $model->id, 'edit' => 1]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($type == 'teacher') {
                $model->_employee_type = \common\models\system\classifier\EmployeeType::EMPLOYEE_TYPE_TEACHER;
            } else {
            }

            if ($model->save()) {
                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __(
                            'Employee Work `{name}` updated successfully.',
                            [
                                'name' => $model->employee->getFullName(),
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Employee Work`{name}` created successfully.',
                            [
                                'name' => $model->employee->getFullName(),
                            ]
                        )
                    );
                }
            }

            return $this->redirect(["employee/$type", 'id' => $model->id, 'edit' => 1]);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(Url::current(['check' => null]));
        }

        if ($type == 'teacher') {
            $model->_employee_type = \common\models\system\classifier\EmployeeType::EMPLOYEE_TYPE_TEACHER;
        } else {
        }

        return $this->render(
            '/employee/staff-edit',
            [
                'model' => $model,
                'employee' => $employee,
                'type' => $type,
            ]
        );
    }

    public function actionToStatus()
    {
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('status')) {
            $selection = (array)Yii::$app->request->post('selection');
            if ($status = Yii::$app->request->post('status')) {
                $transaction = Yii::$app->db->beginTransaction();
                $contract_number = Yii::$app->request->post('contract_number');
                $contract_date = Yii::$app->request->post('contract_date');
                $changed = 0;
                try {
                    foreach ($selection as $id) {
                        if ($model = EEmployeeMeta::findOne((int)$id)) {
                            $model->contract_number = $contract_number;
                            $model->contract_date = $contract_date;
                            $model->_employee_status = $status;
                            $model->save(false);
                            $changed++;
                        }
                    }
                    if ($changed) {
                        $transaction->commit();
                        $this->addSuccess(__('Changed status of {count} employees to {status}', [
                            'count' => $changed,
                            'status' => TeacherStatus::getClassifierOptions()[$status]
                        ]));
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param $id
     * @return EEmployee
     * @throws NotFoundHttpException
     */
    protected function findEmployeeModel($id)
    {
        if (($model = EEmployee::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /**
     * @param $id
     * @return EEmployeeMeta
     * @throws NotFoundHttpException
     */
    protected function findEmployeeMetaModel($id)
    {
        if (($model = EEmployeeMeta::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    private function setSessionPassportData($employee, $data)
    {
        Yii::$app->session->set('session_passport_data_' . $employee, $data);
    }

    private function getSessionPassportData($employee, $field = null)
    {
        $data = Yii::$app->session->get('session_passport_data_' . $employee);
        if ($field !== null && isset($data[$field])) {
            return $data[$field];
        }
        return '';
    }

    private function clearSessionPassportData($employee)
    {
        return Yii::$app->session->remove('session_passport_data_' . $employee);
    }
}
