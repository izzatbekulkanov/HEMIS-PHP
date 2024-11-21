<?php

namespace common\components;

use yii\helpers\ArrayHelper;
use yii\web\AssetBundle;

class TimeStampAssetBundle extends AssetBundle
{
    public function registerAssetFiles($view)
    {
        if (version_compare(\Yii::getVersion(), '2.0.39.2') > -1) {
            $manager = $view->getAssetManager();
            foreach ($this->js as $js) {
                if (is_array($js)) {
                    $file = array_shift($js);
                    $options = ArrayHelper::merge($this->jsOptions, $js);
                    $view->registerJsFile($manager->getAssetUrl($this, $file), $options);
                } else {
                    if ($js !== null) {
                        $view->registerJsFile($manager->getAssetUrl($this, $js), $this->jsOptions);
                    }
                }
            }
            foreach ($this->css as $css) {
                if (is_array($css)) {
                    $file = array_shift($css);
                    $options = ArrayHelper::merge($this->cssOptions, $css);
                    $view->registerCssFile($manager->getAssetUrl($this, $file), $options);
                } else {
                    if ($css !== null) {
                        $view->registerCssFile($manager->getAssetUrl($this, $css), $this->cssOptions);
                    }
                }
            }
        } else {
            parent::registerAssetFiles($view);
        }
    }
}