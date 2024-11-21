<?php


namespace backend\widgets;


use common\components\Config;
use DateTime;
use kartik\datetime\DateTimePicker;
use Yii;

class DateTimePickerDefault extends DateTimePicker
{
    const MODE_DATE = 'date';
    const MODE_DATETIME = 'datetime';
    public $layout = '{input}{picker}{remove}';
    public $mode = self::MODE_DATE;
    public $pickerIcon = '<i class="fa fa-calendar kv-dp-icon"></i>';
    public $removeIcon = '<i class="fa fa-times kv-dp-icon"></i>';
    public $autoDefaultTimezone = false;

    public $pluginOptions = [
        'todayHighlight' => true,
        'bsVersion' => '3.4',
    ];

    public function init()
    {
        $this->pluginOptions['format'] = 'yyyy-mm-dd hh:ii';
        $this->pluginOptions['timezone'] = Yii::$app->formatter->timeZone;
        $value = $this->model->{$this->attribute};
        if ($value instanceof DateTime)
            $this->model->{$this->attribute} = Yii::$app->formatter->asDate($value, 'php:Y-m-d H:i');

        if (\Yii::$app->language == Config::LANGUAGE_CYRILLIC) {
           // $this->language = 'uz-cyrl';
        } else if (\Yii::$app->language == Config::LANGUAGE_UZBEK) {
           // $this->language = 'uz-latn';
        }
        $this->language = 'ru';
        parent::init();
    }
}