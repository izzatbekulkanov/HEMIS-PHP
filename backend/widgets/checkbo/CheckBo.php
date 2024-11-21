<?php

namespace backend\widgets\checkbo;


use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\InputWidget;

class CheckBo extends InputWidget
{
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO    = 'radio';
    const TYPE_SWITCH   = 'switch';

    public $clientOptions = [];
    public $type = 'checkbox';
    public $labelClass = 'switch switch-xs';
    public $typeArray = [
        self::TYPE_CHECKBOX,
        self::TYPE_RADIO,
        self::TYPE_SWITCH,
    ];

    public function init()
    {
        if (!in_array($this->type, $this->typeArray)) {
            throw new InvalidParamException(__("Undefined type: {$this->type}"));
        }
        if ($this->hasModel()) {
            $this->name = Html::getInputName($this->model, $this->attribute);
            $this->value = Html::getAttributeValue($this->model, $this->attribute);
        }
        parent::init();
    }

    public function run()
    {
        if ($this->hasModel() && $this->field) {
            $this->field->label(false);
        }
        $this->id = "checkBo_{$this->id}";
        $view = $this->getView();
        CheckBoAsset::register($view);

        if ($this->type == self::TYPE_SWITCH) {
            Html::addCssClass($this->options['labelOptions'], $this->labelClass);
        } else {
            Html::addCssClass($this->options['labelOptions'], ['class' => "cb-{$this->type}"]);
        }
        if ($this->value) {
            Html::addCssClass($this->options['labelOptions'], 'checked');
        }
        $view->registerJs("jQuery('#{$this->id}').checkBo();");
        echo $this->renderInputHtml($this->type);
    }

    protected function renderInputHtml($type)
    {
        $renderer = "render" . ucfirst($type);
        return $this->$renderer();
    }

    protected function renderCheckbox()
    {
        $hidden = Html::hiddenInput($this->name, 0);
        $input  = Html::checkbox($this->name, $this->value, $this->options);
        $label  = Inflector::titleize($this->attribute);
        $label  = Html::label($input . $label, null, $this->options['labelOptions']);
        if ($this->hasModel()) {
            $input = Html::activeCheckbox($this->model, $this->attribute, ArrayHelper::merge($this->options, $this->clientOptions));
            return Html::tag('div', $input, ['id' => $this->id]);
        }
        return Html::tag('div', $hidden . $label, ['id' => $this->id]);
    }

    protected function renderRadio()
    {
        $hidden = Html::hiddenInput($this->name, 0);
        $input  = Html::radio($this->name, $this->value);
        $label  = Inflector::titleize($this->attribute);
        $label  = Html::label($input . $label, null, $this->options['labelOptions']);
        if ($this->hasModel()) {
            $input = Html::activeRadio($this->model, $this->attribute, $this->options);
            return Html::tag('div', $input, ['id' => $this->id]);
        }
        return Html::tag('div', $hidden . $label, ['id' => $this->id]);
    }

    protected function renderSwitch()
    {
        $labelOptions = $this->options['labelOptions'];
        unset($this->options['labelOptions']);
        $labelOptions['id'] = $this->id;
        $hidden             = $this->hasModel() ? Html::hiddenInput($this->name, 0) : '';
        $input              = Html::checkbox($this->name, $this->value, $this->options);
        $input              .= Html::tag('span', "<i class='handle'></i>");
        $label              = $this->hasModel()
            ? Html::tag('p', $this->model->getAttributeLabel($this->attribute))
            : '';
        return  $hidden . Html::label($input, null, $labelOptions);
    }
}