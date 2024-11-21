<?php


namespace backend\widgets;


use kartik\depdrop\DepDrop;
use kartik\select2\Select2;

class DepDropDefault extends DepDrop
{

    public $type = DepDrop::TYPE_SELECT2;
    public $pluginLoading = false;
    public $placeholder;
    public $disabled = false;
    public $select2Options = ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT];

    public function init()
    {
        if ($this->placeholder == null) $this->placeholder = __('Choose {attribute}', ['attribute' => $this->model->getAttributeLabel($this->attribute)]);

        $this->options['placeholder'] = $this->placeholder;
        $this->options['disabled'] = $this->disabled;

        return parent::init();
    }
}