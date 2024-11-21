<?php


namespace frontend\controllers;


use backend\components\FilterAccessControl;
use backend\controllers\BackendController;
use common\components\Browser;
use common\models\system\Admin;
use frontend\components\StudentFilterAccessControl;
use frontend\components\View;
use frontend\models\system\Student;
use Yii;
use yii\helpers\BaseFileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\IdentityInterface;
use yii\web\Response;

class FrontendController extends BackendController
{
    const COOKIE_DEVICE_ID = '_did';

    public function beforeAction($action)
    {
        if ($selected = Yii::$app->request->get('semester')) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => View::PARAM_SEMESTER,
                'value' => $selected,
                'expire' => ((time() + 30 * 24 * 3600)),
            ]));
        }
        if ($week = Yii::$app->request->get('week')) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => View::PARAM_WEEK,
                'value' => $week,
                'expire' => ((time() + 30 * 24 * 3600)),
            ]));
        }

        $user = $this->_user();
        $path = $action->controller->id . '/' . $action->id;

        if (!Yii::$app->user->isGuest && $user->hasAttribute('password_valid') && $user->password_valid == false) {
            if (!in_array($path, ['dashboard/profile', 'dashboard/logout', 'dashboard/login'])) {
                $this->addError(__('Sizning parolingiz xavfsizlik talablariga mos emas, iltimos, parolingizni yangilang!'));
                Yii::$app->response->redirect(linkTo(['dashboard/profile']));
                return false;
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * @return \common\models\curriculum\Semester
     */
    public function getSelectedSemester()
    {
        return Yii::$app->view->getSelectedSemester();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => StudentFilterAccessControl::className(),
            ],
        ];
    }

    /**
     * @return Student|IdentityInterface|Response
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

    public function handleFailure()
    {

    }

    public function removeCache()
    {
        $dirs = [
        ];
        foreach ($dirs as $dir) {
            $dir = Yii::getAlias($dir);
            if (is_dir($dir))
                BaseFileHelper::removeDirectory($dir);
        }

        Yii::$app->cache->flush();
    }


    public function getDeviceUniqueId()
    {
        if ($cookies = Yii::$app->request->cookies->get(self::COOKIE_DEVICE_ID)) {
            return $cookies->value;
        } else {
            $deviceId = md5(Yii::$app->security->generateRandomString());

            Yii::$app->response->cookies->add(new Cookie([
                'name' => self::COOKIE_DEVICE_ID,
                'value' => $deviceId,
                'httpOnly' => true,
                'expire' => time() + 365 * 24 * 3600,
            ]));

            return $deviceId;
        }
    }
}