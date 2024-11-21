<?php

namespace backend\widgets\colorpicker;

use yii\bootstrap\InputWidget;
use yii\helpers\Html;

class ColorPicker extends InputWidget
{
    public $clientOptions = [];

    public function run()
    {
        $view = $this->getView();
        ColorPickerAsset::register($view);
        $this->registerPlugin('colorpicker');
        echo $this->renderWidget();
    }


    /**
     * Renders the TagsInput widget.
     * @return string the rendering result.
     */
    public function renderWidget()
    {
        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            return Html::textInput($this->name, $this->value, $this->options);
        }
    }

}