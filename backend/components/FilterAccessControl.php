<?php

namespace backend\components;


use common\models\system\Admin;
use common\models\system\AdminResource;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class FilterAccessControl extends ActionFilter
{
    public $user = 'user';

    /**
     * @todo gather from @skipAccess methods
     */
    public $exceptResources = [
        'dashboard/login',
        'dashboard/logout',
        'dashboard/reset',
        'dashboard/error',
        'ajax/captcha',
    ];

    protected function getFullActionName()
    {
        return Yii::$app->controller->id . "/" . Yii::$app->controller->action->id;
    }

    public function beforeAction($action)
    {
        /**
         * @var $user Admin
         */

        $user = Yii::$app->user->identity;
        $path = $this->getFullActionName();

        /**
         * @var $resource AdminResource
         */

        if (in_array($path, $this->exceptResources)) {
            return parent::beforeAction($action);
        }

        if ($resource = AdminResource::findOne(['path' => $path])) {
            if ($resource->active) {
                if ($resource->skip) {
                    if ($user !== null) {
                        return parent::beforeAction($action);
                    }

                    $this->denyAccess();
                }
            }
        }

        if ($user && $user->canAccessToResource($path)) {
            return parent::beforeAction($action);
        }

        $this->denyAccess();

        return false;
    }

    protected function denyAccess()
    {
        $user = Yii::$app->user;

        if ($user !== false && $user->getIsGuest()) {
            Yii::$app->user->loginRequired();
        } else {
            throw new ForbiddenHttpException(__('You are not allowed to perform this action.'));
        }
    }
}