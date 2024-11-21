<?php


namespace backend\widgets;


use kartik\select2\Select2;

class Select2Default extends Select2
{
    public $options = ['class' => 'select2'];
    public $theme = Select2::THEME_DEFAULT;
    public $hideSearch = true;
    public $pluginLoading = false;
    public $placeholder = null;
    public $allowClear = true;
    public $disabled = false;
    public $showToggleAll = false;
    public $pluginOptions = [];

    public function init()
    {
        if ($this->placeholder === null) $this->placeholder = __('Choose {attribute}', ['attribute' => $this->model->getAttributeLabel($this->attribute)]);
        $this->pluginOptions = array_merge($this->pluginOptions, [
            'allowClear' => $this->allowClear,
            'placeholder' => $this->placeholder,
        ]);
        $this->options['disabled'] = $this->disabled;

        return parent::init();
    }
}