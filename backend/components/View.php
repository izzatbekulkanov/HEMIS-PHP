<?php

namespace backend\components;

use backend\assets\BackendAsset;
use common\components\Config;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminResource;
use frontend\models\system\Student;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use function foo\func;

class View extends \yii\web\View
{
    protected $_bodyClass = [];

    public function getMenuItems()
    {
        $menu = array();

        if ($admin = $this->_user()) {
            $keys = [Yii::$app->language, $admin->role->code, $admin->role->updated_at->getTimestamp()];
            $key = md5(serialize($keys));

            $menu = Yii::$app->cache->get($key);
            if ($menu === false) {
                $menu = Yii::$app->params['backendMenu'];
                $resources = ArrayHelper::map(AdminResource::find()
                    ->all(), 'path', function ($item) {
                    return $item->getNameLabel();
                });

                foreach ($menu as $id => &$item) {
                    $item['label'] = __(trim($item['label']));
                    if (isset($item['items']) && !empty($item['items'])) {
                        foreach ($item['items'] as $p => &$childItem) {
                            if (!$this->_user()->canAccessToResource($childItem['url'])) {
                                unset($menu[$id]['items'][$p]);
                            }
                            $childItem['label'] = isset($resources[$childItem['url']]) ? $resources[$childItem['url']] : __($childItem['label']);
                            $childItem['id'] = trim($childItem['url'], '/');
                            $childItem['url'] = Url::to([$childItem['url']]);
                        }
                        if (count($menu[$id]['items']) == 0 && !$admin->canAccessToResource($item['url'])) {
                            unset($menu[$id]);
                        }
                    }
                    if (!$admin->canAccessToResource($item['url']) && (!isset($item['items']) || count($item['items']) == 0)) {
                        unset($menu[$id]);
                    }
                    $item['id'] = trim($item['url'], '/');
                    $item['url'] = Url::to([$item['url']]);
                }

                Yii::$app->cache->set($key, $menu, 3200);
            }
        }

        return $menu;
    }

    public function getImageUrl($name)
    {
        return $this->getAssetManager()->getBundle(BackendAsset::class)->baseUrl . '/' . $name;
    }

    public function getSystemLogo()
    {
        if ($file = Config::get(Config::CONFIG_SYS_UI_LOGO)) {
            return _BaseModel::getCropImage($file, 250, 250);
        }
        return $this->getImageUrl('img/gerb.png');
    }

    /**
     * @return Admin|Student|IdentityInterface
     */
    public function _user()
    {
        return $this->context->_user();
    }

    public function getFullPath()
    {
        if (Yii::$app->controller)
            return Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
    }


    public function getBodyClass()
    {
        return implode(' ', $this->_bodyClass);
    }

    public function addBodyClass($class)
    {
        $this->_bodyClass[] = $class;
    }

    public function getResourceLink($link, $url = [], $options = [], $resource = false)
    {
        if ($resource == false) {
            $resource = is_array($url) ? $url[0] : $url;
        }
        if ($this->_user()->canAccessToResource($resource)) {
            return Html::a($link, $url, $options);
        }

        return '';
    }

    public function beforeRender($viewFile, $params)
    {

        if (Yii::$app->id == 'app-backend')
            if ($r = AdminResource::getResourceByPath($this->getFullPath())) {
                if ($this->title == null)
                    $this->title = $r->getNameLabel();
            }

        return parent::beforeRender($viewFile, $params);
    }

    public function getControllerActionTitle()
    {
        return __(Inflector::camel2words(str_replace('/', ' ', $this->getFullPath())));
    }

}