<?php
namespace backend\widgets;

use kotchuprik\medium\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

class Editable extends Widget
{
    public $theme    = 'flat';
    public $settings = [
        'buttons' => ['bolder', 'italic', 'underline', 'image'],

        'updateOnEmptySelection' => true,
        'paste'                  => [
            'cleanPastedHTML' => true,
        ],
    ];

    public $tagOptions = ['class' => 'editable'];

    public function run()
    {
        $this->settings['extensions']   = [
             'bolder' => new JsExpression("new MediumButton({label:'<b>B</b>', action:function(html, mark){ return html} , start:'<b>', end:'</b>'})"),
             'image'  => new JsExpression("new MediumButton({label:'<b>IMG</b>', action:function(html, mark){ return mediumInsertImage(html, mark)}})")
        ];
        $this->tagOptions['data-input'] = '#' . $this->options['id'];
        $this->options['style']         = 'display: none;';

        $this->registerClientScripts();
        echo Html::tag('div', Html::getAttributeValue($this->model, $this->attribute), $this->tagOptions);
        echo Html::activeTextarea($this->model, $this->attribute, $this->options);
    }

    protected function registerClientScripts()
    {
        $view = $this->getView();


        $js = [];
        if (empty($this->settings)) {
            $js[] = 'var editor = new MediumEditor(".editable");';
        } else {
            $js[] = 'var editor = new MediumEditor(".editable", ' . Json::encode($this->settings) . ');';
        }
        $js[] = '$(".editable").on("input", function() { var $this = $(this); $($this.data("input")).val($this.html()); renderMath(this)});';

        $view->registerJs(implode(PHP_EOL, $js));
    }
}