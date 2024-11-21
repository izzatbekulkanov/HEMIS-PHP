<?php


namespace backend\widgets;


use common\components\Config;
use kartik\date\DatePicker;
use Yii;

class DatePickerDefault extends DatePicker
{
    const MODE_DATE = 'date';
    const MODE_DATETIME = 'datetime';
    public $layout = '{input}{picker}{remove}';
    public $mode = self::MODE_DATE;
    public $pickerIcon = '<i class="fa fa-calendar kv-dp-icon"></i>';
    public $removeIcon = '<i class="fa fa-times kv-dp-icon"></i>';

    public $pluginOptions = [
        'autoclose' => true,
        'weekStart' => '1',
        'todayHighlight' => true
    ];

    public function init()
    {
        if ($this->mode == self::MODE_DATE) {
            $this->pluginOptions['format'] = 'yyyy-mm-dd';
            $value = $this->model->{$this->attribute};
            if ($value)
                $this->model->{$this->attribute} = Yii::$app->formatter->asDate($value, 'php:Y-m-d');
        } else if ($this->mode == self::MODE_DATETIME) {
            /**
             * @todo  add time
             */
            $this->pluginOptions['format'] = 'yyyy-mm-dd';
            $value = $this->model->{$this->attribute};
            if ($value)
                $this->model->{$this->attribute} = Yii::$app->formatter->asDate($value, 'php:Y-m-d');
        }
        if (\Yii::$app->language == Config::LANGUAGE_CYRILLIC) {
            $this->language = 'uz-cyrl';
        } else if (\Yii::$app->language == Config::LANGUAGE_UZBEK) {
            $this->language = 'uz-latn';
        }
        parent::init();
    }


}