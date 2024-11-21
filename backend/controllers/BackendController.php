<?php

namespace backend\controllers;

use backend\components\FilterAccessControl;
use common\components\Config;
use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\models\SyncLog;
use common\models\structure\EUniversity;
use common\models\system\Admin;
use common\models\system\SystemLog;
use common\models\system\SystemLogin;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * @skipAccess
 * Class BackendController
 * @package backend\controllers
 */
class BackendController extends Controller
{
    public $layout = 'dashboard';
    public $activeMenu;

    /**
     * @var Admin|IdentityInterface
     */
    protected $_user;

    public function beforeAction($action)
    {
        if (Yii::$app->id == 'app-backend') {
            if ($action->id != 'error') {
                if ($duration = (intval(getenv('BACK_LOCK_TIME')) * 60)) {
                    $start = getenv('BACK_LOCK_START');
                    if ($start) {
                        if ($start = date_create_from_format('d-m-Y H:i', $start)) {
                            if (time() < $start->getTimestamp()) {
                                if (!Yii::$app->request->isAjax)
                                    $this->addError(__('Migration scheduled on {date}, after {minute} minutes backend stops permanently!', ['date' => "<b>" . Yii::$app->formatter->asDatetime($start->getTimestamp()) . "</b>", 'minute' => ceil(($start->getTimestamp() - time()) / 60)]));
                            } else {
                                $has = $start->getTimestamp() + $duration - time();
                                if ($has >= 0) {
                                    echo __('Migration going, it completes after {minute} minutes', ['minute' => ceil($has / 60)]);
                                    return false;
                                }
                            }
                        } else {
                            echo "BACK_LOCK_START format incorrect, use as 09-02-2018 02:10\n";
                        }
                    }
                }
                $user = $this->_user();
                $path = $action->controller->id . '/' . $action->id;
                if (EUniversity::findCurrentUniversity() == null) {
                    if ($path != 'structure/university-update') {
                        if (!Yii::$app->user->isGuest && $user->role && $user->role->isSuperAdminRole()) {
                            $this->addError(__('Please, configure university information'));
                            Yii::$app->response->redirect(linkTo(['structure/university-update']));
                            return false;
                        } else {

                        }
                    }
                } else if (Config::isHemisAuthenticationRequired()) {
                    if ($path != 'dashboard/hemis-auth') {
                        if (!Yii::$app->user->isGuest && $user->role && $user->role->isSuperAdminRole()) {
                            Yii::$app->response->redirect(linkTo(['dashboard/hemis-auth']));
                            return false;
                        }
                    }
                } else if (!Yii::$app->user->isGuest && $user->hasAttribute('password_valid') && $user->password_valid == false) {
                    if (!in_array($path, ['dashboard/profile', 'dashboard/logout'])) {
                        $this->addError(__('Sizning parolingiz xavfsizlik talablariga mos emas, iltimos, parolingizni yangilang!'));
                        Yii::$app->response->redirect(linkTo(['dashboard/profile']));
                        return false;
                    }
                }

                if (!Yii::$app->user->isGuest && $user->role->isDeanRole()) {
                    if ($user->employee->deanFaculties == null) {
                        if (!in_array($path, ['dashboard/switch-role', 'dashboard/profile', 'dashboard/index', 'dashboard/login', 'dashboard/logout'])) {
                            $this->addInfo(
                                __('The institution department is not attached to your account. ')
                            );
                            Yii::$app->response->redirect(linkTo(['dashboard/index']));
                            return false;
                        }
                    }
                }
            }

        }

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        if ($this->activeMenu)
            Yii::$app->session->set('activeMenu', $this->activeMenu);

        return parent::afterAction($action, $result);
    }

    /**
     * @deprecated
     */
    protected function saveSearchParams()
    {
        $path = $this->getRoute() . '_search';

    }

    public function clearFilterParams()
    {
        $path = $this->getRoute() . '_search';
        Yii::$app->session->offsetUnset($path);
    }

    public function getFilterParams()
    {
        $path = $this->getRoute() . '_search';
        if ($this->get('clear-filter')) {
            Yii::$app->session->offsetUnset($path);

            $this->redirect(currentTo(['clear-filter' => null]));
            return [];
        }

        if (Yii::$app->request->isAjax && !Yii::$app->request->isPost) {
            Yii::$app->session->set($path, $this->get());
            Yii::$app->session->set($path . '_page', $this->get('page'));
            Yii::$app->session->set($path . '_per-page', $this->get('per-page'));
        }

        if ($this->get('page') == null || $this->get('per-page') == null) {
            $_GET = array_merge([
                'page' => Yii::$app->session->get($path . '_page'),
                'per-page' => Yii::$app->session->get($path . '_per-page'),
            ], $_GET);
        }

        $params = (array)Yii::$app->session->get($path);
        return ArrayHelper::merge($params, $this->get());
    }

    /**
     * @return Admin|IdentityInterface|Response
     */
    public function _user()
    {
        if (!Yii::$app->user->isGuest && !$this->_user) {
            $this->_user = Yii::$app->user->identity;
            if ($this->_user == null) {
                Yii::$app->user->logout();

                return $this->goHome();
            }
        }

        return $this->_user;
    }


    public function canAccessToResource($string)
    {
        if ($this->_user()->canAccessToResource($string)) {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => FilterAccessControl::className(),
            ],
        ];
    }

    protected function addSuccess($message, $logAction = true, $showMessage = true)
    {
        if ($logAction) {
            SystemLog::captureAction($message);
        }

        if ($showMessage) {
            Yii::$app->session->addFlash('success', $message);
        }
    }

    protected function addError($message, $logAction = false, $showMessage = true)
    {
        if ($logAction) {
            SystemLog::captureAction($message);
        }

        if ($showMessage) {
            Yii::$app->session->addFlash('error', $message);
        }
    }

    protected function addInfo($message)
    {
        Yii::$app->session->addFlash('info', $message);
    }

    protected function addWarning($message)
    {
        Yii::$app->session->addFlash('warning', $message);
    }


    protected function post($name = null, $default = null)
    {
        return Yii::$app->request->post($name, $default);
    }

    protected function get($name = null, $default = null)
    {
        return Yii::$app->request->get($name, $default);
    }

    public function isAjax()
    {
        return Yii::$app->request->isAjax;
    }

    public function isGet()
    {
        return Yii::$app->request->isGet;
    }

    public function setSession($param, $value = null)
    {
        Yii::$app->session->set($param, $value);
    }

    public function getSession($param)
    {
        return Yii::$app->session->get($param);
    }

    public function isPjax()
    {
        return Yii::$app->request->isPjax;
    }

    protected function handleFailure()
    {
        if (SystemLogin::getIsLoginActionLimited(Yii::$app->request->getUserIP())) {
            echo __('So much login fails, please, don\'t try unauthorized access');
            die;
        }
    }

    protected function notFoundException()
    {
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function renderView($params = [])
    {
        return $this->render($this->action->id, $params);
    }

    public function removeCache()
    {
        $dirs = [
            '@backend/runtime/cache',
            '@frontend/runtime/cache',
        ];
        foreach ($dirs as $dir) {
            $dir = Yii::getAlias($dir);
            if (is_dir($dir))
                BaseFileHelper::removeDirectory($dir);
        }

        try {
            Yii::$app->cache->flush();
        } catch (\Exception $exception) {

        }
    }

    protected $_syncError;

    protected function syncModelToApi(HemisApiSyncModel $model, $delete = false)
    {
        if (HEMIS_INTEGRATION) {
            try {
                if ($model->syncToApi($delete)) {
                    $this->addSuccess(__('Model synced to HEMIS API successfully'), false, true);
                    return true;
                }
            } catch (HemisApiError $e) {
                $this->addWarning('HEMIS_ERROR: ' . $e->getMessage());
                SyncLog::registerModel($model, $e->getMessage(), $delete);
                $this->_syncError = $e->getMessage();
            } catch (\Exception $e) {
                $this->addError(__("Ma'lumotni sinxornizatsiya qilishda xatolik yuz berdi"));
                SyncLog::registerModel($model, $e->getMessage(), $delete);
                $this->_syncError = $e->getMessage();
            }
        } else {
            return true;
        }
        return false;
    }

    protected function deleteModelToApi(HemisApiSyncModel $model)
    {
        if ($this->get('delete')) {
            $message = false;
            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                return true;
            } else {
                if ($message)
                    $this->addError($message);
            }
        }
        return false;
    }

    protected function checkModelToApi(HemisApiSyncModel $model)
    {

        if ($this->get('check')) {
            try {
                $model->checkToApi(true);
                if ($model->_sync_status == HemisApiSyncModel::STATUS_ACTUAL) {
                    $this->addSuccess(__('Sync Status: {status}', ['status' => $model->getSyncStatusLabel()]));
                } else {
                    $this->addError(__('Sync Status: {status}', ['status' => $model->getSyncStatusLabel()]));
                }
            } catch (\Exception $e) {
                $this->addError($e->getMessage());
            }

            return true;
        }

        return false;
    }
}
