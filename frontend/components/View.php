<?php

namespace frontend\components;

use common\models\curriculum\Semester;
use common\models\system\Admin;
use common\models\system\AdminResource;
use frontend\assets\FrontendAsset;
use frontend\models\system\Student;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\IdentityInterface;

class View extends \backend\components\View
{

    const PARAM_SEMESTER = 'semester';
    const PARAM_WEEK = 'week';

    public function getMenuItems()
    {
        $menu = array();
        $menuItems = Yii::$app->params['studentMenu'];

        if ($admin = $this->_user()) {
            $keys = [Yii::$app->language, $admin->id, $admin->updated_at->getTimestamp(), serialize($menuItems)];
            $key = md5(serialize($keys));

            $menu = Yii::$app->cache->get($key);
            if ($menu === false) {
                $menu = $menuItems;

                foreach ($menu as $id => &$item) {
                    $item['label'] = __(trim($item['label']));
                    if (isset($item['items']) && !empty($item['items'])) {
                        foreach ($item['items'] as $p => &$childItem) {
                            if ($childItem['enable'] == false) {
                                //unset($menu[$id]['items'][$p]);
                            }
                            $childItem['label'] = __(Inflector::camel2words(str_replace('/', ' ', $childItem['url'])));
                            $childItem['id'] = trim($childItem['url'], '/');
                            $childItem['url'] = Url::to([$childItem['url']]);
                        }
                        if (count($menu[$id]['items']) == 0) {
                            unset($menu[$id]);
                        }
                    }
                    if ((!isset($item['items']) || count($item['items']) == 0)) {
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

    /**
     * @return Student|IdentityInterface
     */
    private $_user;

    public function _user()
    {
        if (!Yii::$app->user->isGuest && !$this->_user) {
            $this->_user = Yii::$app->user->identity;
        }

        return $this->_user;
    }

    public function getHomeLinks()
    {
        $menu = [];
        $menuItems = Yii::$app->params['studentMenu'];

        if ($admin = $this->_user()) {
            $keys = ['home', Yii::$app->language, $admin->id, $admin->updated_at->getTimestamp(), serialize($menuItems)];
            $key = md5(serialize($keys));

            $menu = Yii::$app->cache->get($key);
            if ($menu === false) {
                foreach ($menuItems as $id => &$item) {
                    if (isset($item['items']) && !empty($item['items'])) {
                        foreach ($item['items'] as $p => &$childItem) {

                            $childItem['label'] = __(Inflector::camel2words(str_replace('/', ' ', $childItem['url'])));;
                            $childItem['id'] = trim($childItem['url'], '/');
                            $childItem['url'] = Url::to([$childItem['url']]);
                            if (isset($childItem['home']) && $childItem['home']) {
                                $menu[] = $childItem;
                            }
                        }
                    }
                }

                Yii::$app->cache->set($key, $menu, 3200);
            }
        }

        return $menu;
    }


    public function getImageUrl($name)
    {
        return $this->getAssetManager()->getBundle(FrontendAsset::class)->baseUrl . '/' . $name;
    }

    /**
     * @return \common\models\curriculum\Semester
     */
    public function getSelectedSemester()
    {
        $semesters = $this->_user()->getSemesters();
        if (count($semesters)) {
            $first = array_keys($semesters)[0];
            $current = $this->_user()->meta->_semestr;
            $cookie = Yii::$app->request->cookies->get(self::PARAM_SEMESTER);
            $selected = Yii::$app->request->get('semester', $cookie ? $cookie->value : $current);
            if (!isset($semesters[$selected])) {
                $selected = $first;
            }

            return $semesters[$selected];
        }

        return new Semester();
    }

}