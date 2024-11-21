<?php

namespace backend\controllers;

use backend\models\FormAdminLogin;
use backend\models\FormAdminReset;
use backend\models\FormHemisAuth;
use common\components\Config;
use common\components\event\ToggleEvent;
use common\models\data\ExamStudent;
use common\models\report\ReportContract;
use common\models\report\ReportEmployment;
use common\models\system\Admin;
use common\models\system\AdminRole;
use MongoDB\BSON\ObjectId;
use Yii;
use yii\base\ActionEvent;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\helpers\BaseFileHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class DashboardController
 * @package backend\controllers
 */
class DashboardController extends BackendController
{
    public $layout = 'main';

    public function actionR()
    {
        /**
         * @var $model ReportEmployment
         */
        if ($model = ReportEmployment::find()->one()) {
            $model->syncToApi();
        }
    }

    public function beforeAction($action)
    {
        if ($action->id == 'sort' || $action->id == 'toggle') {
            Yii::$app->request->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actions()
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        return [
            'file-upload' => [
                'class' => 'common\components\file\CropUpload',
                'fileparam' => 'files',
            ],
            'file-delete' => [
                'class' => 'common\components\file\CropUploadDelete',
            ],
        ];
    }

    /**
     * @skipAccess
     * @skipResource dashboard/file-upload
     * @skipResource dashboard/file-delete
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        $this->layout = 'dashboard';
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            return $this->render('teacher');
        } elseif ($this->_user()->role->code == AdminRole::CODE_ACADEMIC) {
            return $this->render('academic');
        } elseif ($this->_user()->role->code == AdminRole::CODE_STAFF) {
            return $this->render('staff');
        } elseif ($this->_user()->role->code == AdminRole::CODE_MARKETING) {
            return $this->render('marketing');
        } elseif ($this->_user()->role->code == AdminRole::CODE_ACCOUNTING) {
            return $this->render('accounting');
        } elseif ($this->_user()->role->code == AdminRole::CODE_FINANCE_CONTROL) {
            return $this->render('finance-control');
        } elseif ($this->_user()->role->code == AdminRole::CODE_DIRECTION) {
            return $this->render('direction');
        } elseif ($this->_user()->role->code == AdminRole::CODE_DOCTORATE) {
            return $this->render('doctorate');
        } elseif ($this->_user()->role->code == AdminRole::CODE_SCIENCE) {
            return $this->render('science');
        }elseif ($this->_user()->role->code == AdminRole::CODE_TUTOR) {
            return $this->render('tutor');
        } elseif ($this->_user()->role->code == AdminRole::CODE_DEAN ||
            $this->_user()->role->code == AdminRole::CODE_SUPER_ADMIN ||
            $this->_user()->role->code == AdminRole::CODE_MIN_ADMIN) {
            return $this->render('dean');
        } else
            return $this->render('index');
    }

    /**
     * @skipAccess
     */
    public function actionVersion()
    {
        $this->layout = 'dashboard';
        return $this->renderView();
    }

    /**
     * @skipAccess
     */
    public function actionSort($model)
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $tableSchema = Yii::$app->db->getSchema()->getTableSchema($model);

        if ($tableSchema != null) {
            $command = Yii::$app->db->createCommand();

            $count = 0;
            foreach ($this->post('data') as $position => $id) {
                $count += $command->update($model, ['position' => $position], [$tableSchema->primaryKey[0] => $id])->execute();
            }

            if ($count) {
                $this->addSuccess(__('Table [{name}] sorted', ['name' => $model]), true, false);
            }
        }


        return [];
    }

    /**
     * @skipAccess
     */
    public function actionToggle($model, $attribute)
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $tableSchema = Yii::$app->db->getSchema()->getTableSchema($model);
        if ($tableSchema != null) {
            $command = Yii::$app->db->createCommand();


            if ($this->get('batch')) {
                $ids = [];
                $state = boolval($this->post('state'));

                foreach ($this->post('items') as $id) {
                    $ids[] = intval($id);
                }
                if ($command->update($model, [$attribute => $state], [$tableSchema->primaryKey[0] => $ids])->execute()) {
                    if ($state) {
                        $this->addSuccess(__('Column [{column}] of the table [{name}] is enabled', ['name' => $model, 'column' => $attribute]), true, false);
                    } else {
                        $this->addSuccess(__('Column [{column}] of the table [{name}] is disabled', ['name' => $model, 'column' => $attribute]), true, false);
                    }
                }
            } else {
                $id = intval($this->get('id'));


                $event = new ToggleEvent([
                    'table' => $model,
                    'attribute' => $attribute,
                    'id' => $id,
                    'primaryKey' => $tableSchema->primaryKey[0],
                ]);

                $this->trigger(EVENT_BEFORE_TOGGLE, $event);

                if ($hasUpdate = $command->update($model, [$attribute => new Expression("NOT $attribute")], [$tableSchema->primaryKey[0] => $id])->execute()) {
                    $this->addSuccess(__('Column [{column}] of the table [{name}] with [{id}] is toggled', ['name' => $model, 'column' => $attribute, 'id' => $id]), true, false);
                }

                $event->hasUpdate = $hasUpdate;

                $this->trigger(EVENT_AFTER_TOGGLE, $event);
            }

        }

        return [];
    }

    /**
     * @skipAccess
     */
    public function actionSwitchRole($id)
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        $user = $this->_user();

        if ($role = AdminRole::findOne($id)) {
            if ($user->hasRole($role)) {
                if ($user->updateAttributes(['_role' => $role->id])) {
                    Yii::$app->session->removeAll();
                    $this->removeCache();
                    $this->addSuccess(__('User role changed to {name}', ['name' => $role->name]));
                }
            }
        }

        return $this->goHome();
    }

    /**
     * @skipAccess
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->handleFailure();

        $model = new FormAdminLogin();
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

    /**
     * @skipAccess
     */
    public function actionHemisAuth()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        if (!Config::isHemisAuthenticationRequired()) {
            return $this->goHome();
        }

        $model = new FormHemisAuth();

        if ($this->_user()->role->isSuperAdminRole()) {
            if ($model->load(Yii::$app->request->post())) {

                if ($model->login()) {
                    $this->addSuccess(__('Authentication to HEMIS performed successfully'));
                    return $this->goBack();
                } else {
                    Yii::$app->session->set('hemis_auth_login', $model->login);
                    $this->addError($model->getFirstError('password'), false);
                }

                return $this->redirect('hemis-auth');
            } else {
                if ($login = Config::get(Config::CONFIG_SYS_HEMIS_LOGIN)) {
                    $model->login = $login;
                }

                if (!$model->login)
                    $model->login = Yii::$app->session->get('hemis_auth_login');
            }
        } else {
            $this->addError(__('HEMIS Autentifiktsiyadan o\'tish uchun Super Admininistrator rolida bo\'lishingiz kerak!'));
        }


        return $this->renderView([
            'model' => $model,
        ]);
    }

    /**
     * @skipAccess
     */
    public function actionProfile()
    {

        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        $model = $this->_user();
        $model->setScenario('profile');

        $this->layout = 'dashboard';

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->change_password)
                $this->addSuccess(__('Password updated successfully'), false);
            $this->addSuccess(__('Your profile updated successfully'));

            Yii::$app->user->switchIdentity($model);

            return $this->redirect(['dashboard/profile']);
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * @skipAccess
     */
    public function actionReset($token = false)
    {
        return $this->goHome();

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

    /**
     * @skipAccess
     */
    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
            Yii::$app->session->addFlash('success', __('You have logged out'));
        }

        return $this->goHome();
    }

    /**
     * @skipAccess
     */
    public function actionError()
    {
        $this->layout = 'main';

        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }

}
