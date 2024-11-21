<?php


namespace backend\widgets;


use kartik\select2\Select2;
use yii\widgets\MaskedInput;

class MaskedInputDefault extends MaskedInput
{
    public $prefix;

    public function init()
    {
        $this->clientOptions = [
            'clearIncomplete' => false,
            'escapeChar' => '|',
            'greedy' => true
        ];
        $prefix = '';
        if ($this->prefix) {
            $prefix = '|' . implode('|', str_split($this->prefix));
        }
        $this->mask = $prefix . $this->mask;
        return parent::init();
    }
}