<?php
/**
 * Wrapper widget for Choices.js
 * https://github.com/jshjohnson/Choices
 */

namespace backend\widgets\choices;

use yii\bootstrap\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;

class ChoicesSelect extends InputWidget
{
    public $clientOptions = [];
    public $items = [];
    public $multiple = false;
    public $fetchUrl = false;
    public $pluginOptions = [
        'searchEnabled' => true,
        'searchChoices' => true,
        'searchFloor' => true,
        'removeItemButton' => true,
        'removeItems' => true
    ];
    public $options = [
        'class' => 'js-choice'
    ];

    public function run()
    {
        $this->options['id'] = $this->getId();
        $this->options['multiple'] = $this->multiple;

        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            echo Html::dropDownList($this->name, $this->value, $this->items, $this->options);
        }
        $this->registerAssets();
    }

    /**
     * Register client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        ChoicesAsset::register($view);
        $options = Json::encode($this->pluginOptions);
        $urlSetting = "";

        if ($this->fetchUrl) {
            $urlSetting = ".setChoices(function(callback) {
                      return fetch('{$this->fetchUrl}').then(function(res) {
                          return res.json();
                      }).then(function(data) {
                            return data.map(function(release) {
                                return { label: release.title, value: release.title };
                            });
                      });
                    });";
        }

        $js = "var choice_{$this->getId()} = new Choices(document.getElementById('{$this->getId()}'),$options)$urlSetting";
        $view->registerJs($js);
    }
}