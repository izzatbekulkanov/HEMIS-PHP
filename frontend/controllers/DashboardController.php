<?php

namespace frontend\controllers;

use backend\models\FormAdminLogin;
use backend\models\FormAdminReset;
use common\models\archive\EStudentDiploma;
use common\models\finance\EStudentContract;
use common\models\student\EStudent;
use common\models\system\AdminRole;
use common\models\system\classifier\Soato;
use common\models\system\SystemLogin;
use frontend\models\academic\StudentDiploma;
use frontend\models\form\FormStudentDiploma;
use frontend\models\form\FormStudentLogin;
use frontend\models\form\FormStudentPin;
use frontend\models\form\FormStudentProfile;
use frontend\models\system\Student;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Response;
use yii\widgets\ActiveForm;

class DashboardController extends FrontendController
{
    public $layout = 'empty';

    public function actions()
    {
        return [
            'file-upload' => [
                'class' => 'common\components\file\CropUpload',
                'fileparam' => 'files',
            ],
            'file-delete' => [
                'class' => 'common\components\file\CropUploadDelete',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'maxLength' => 5,
                'offset' => 3,
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        $this->layout = 'dashboard';

        return $this->render('index');
    }

    public function actionDiploma($d = false, $hash = false)
    {
        if ($d) {
            if ($diploma = EStudentDiploma::findOne(['hash' => $d, 'accepted' => true])) {
                if ($this->get('diploma')) {
                    if (file_exists($diploma->getDiplomaFilePath())) {
                        return Yii::$app->response->sendFile($diploma->getDiplomaFilePath(), "diplom-{$diploma->student->student_id_number}.pdf");
                    }
                    return $this->redirect(['diploma', 'd' => $diploma->hash]);
                }

                if ($this->get('supplement')) {
                    if (file_exists($diploma->getSupplementFilePath())) {
                        return Yii::$app->response->sendFile($diploma->getSupplementFilePath(), "ilova-{$diploma->student->student_id_number}.pdf");
                    }
                    return $this->redirect(['diploma', 'd' => $diploma->hash]);
                }
                return $this->render('diploma_view', ['selected' => $diploma]);
            }
        }

        $model = new FormStudentPin();
        if ($model->load($this->post())) {
            if ($diploma = $model->findStudentDiploma()) {
                return $this->redirect(linkTo(['dashboard/diploma', 'd' => $diploma->hash]));
            } else {
                $errors = $model->getFirstErrors();
                $this->addError(array_pop($errors));
            }
        }

        $this->view->title = __('Find Diploma');
        return $this->render('pin', ['model' => $model]);
    }

    public function actionContract($c = false)
    {
        if ($c && $this->getSession('contracts')) {
            if ($student = EStudent::findOne(['passport_pin' => $this->getSession('contracts')])) {
                if ($contract = EStudentContract::findOne(['hash' => $c, '_student' => $student->id])) {
                    if (is_array($contract->filename)) {
                        $files = $contract->filename;
                        if (isset($files['name'])) {
                            $file = Yii::getAlias('@root/') . $files['base_url'] . DS . $files['name'];
                            if (file_exists($file)) {
                                return Yii::$app->response->sendFile($file, $files['name']);
                            }
                        }
                    }
                    $this->addError(__('Contract file not found'));
                }
            }

            return $this->redirect(['dashboard/contract']);
        }

        $model = new FormStudentPin();
        if ($model->load($this->post())) {
            if ($contracts = $model->findStudentContracts()) {
                $this->setSession('contracts', $model->pin);
                return $this->render('contracts', ['contracts' => $contracts]);
            } else {
                $errors = $model->getFirstErrors();
                $this->addError(array_pop($errors));
            }

            return $this->refresh();
        }

        $this->setSession('contracts', false);
        $this->view->title = __('Find Contract');
        return $this->render('pin', ['model' => $model]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->handleFailure();

        $model = new FormStudentLogin();
        $fails = Yii::$app->session->get('fails', 0);
        $model->fails = $fails;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                $this->addSuccess(__('You have logged in successfully'));
                return $this->goBack();
            } else {
                Yii::$app->session->set('auth_login', $model->login);
                $errors = $model->getFirstErrors();
                $this->addError(array_pop($errors), false);
                Yii::$app->session->set('fails', $fails + 1);
            }

            return $this->redirect('login');
        } else {
            $model->login = Yii::$app->session->get('auth_login');
        }

        return $this->render('login', [
            'model' => $model,
            'fails' => $fails,
        ]);
    }

    public function actionProfile()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        if ($this->get('region')) {
            if ($parents = $_POST['depdrop_parents']) {
                $cat_id = $parents[0];
                $catList = Soato::getChildrenOption($cat_id);
                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                usort($result, function ($a, $b) {
                    return $a['name'] > $b['name'];
                });
                return Json::encode(['output' => $result, 'selected' => '']);
            }
            return Json::encode(['output' => '', 'selected' => '']);
        }

        $model = FormStudentProfile::findOne($this->_user()->id);

        $this->layout = 'dashboard';
        $this->activeMenu = 'system';

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->addSuccess(__('Your profile updated successfully'));
                if ($model->change_password) {
                    $this->addSuccess(__('Password updated successfully'), false);
                    Yii::$app->user->switchIdentity(Student::findOne($model->id));
                }

            }

            return $this->redirect(['dashboard/profile']);
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }


    public function actionReset($token = false)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->handleFailure();

        $model = new FormAdminReset();
        $model->setScenario($token ? 'resetPassword' : 'resetRequest');


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($token) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                try {
                    if ($model->resetAdminPassword($token)) {
                        Yii::$app->session->set('fails', 0);

                        $this->addSuccess(__('Password changed successfully.'), false);

                        return $this->goHome();
                    }
                } catch (InvalidParamException $e) {
                    $this->addError($e->getMessage());
                }

                return $this->redirect(['/dashboard/login']);
            }

            return $this->render('resetPassword', [
                'model' => $model,
            ]);
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->sendEmail()) {
                    $this->addSuccess(__('Check your email for further instructions.'), false);

                    return $this->redirect(['/dashboard/login']);
                } else {
                    $this->addError(__('Sorry, we are unable to reset password for email provided.'));
                }
                return $this->redirect(['/dashboard/reset']);
            }

            return $this->render('resetRequest', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
            Yii::$app->session->addFlash('success', __('You have logged out'));
        }

        return $this->goHome();
    }


    public function actionError()
    {
        $this->layout = 'empty';

        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }

    public function actionLogins()
    {
        $this->layout = 'dashboard';
        $this->activeMenu = 'system';

        $searchModel = new SystemLogin();

        return $this->render('logins', [
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getFilterParams()),
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCache()
    {
        $this->removeCache();

        $this->addSuccess(__('System cache cleared successfully'), false);
        if ($url = Yii::$app->request->getReferrer()) {
            return $this->redirect($url);
        }

        return $this->goHome();
    }


}
