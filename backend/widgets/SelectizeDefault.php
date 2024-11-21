<?php


namespace backend\widgets;


use dosamigos\selectize\InputWidget;
use dosamigos\selectize\SelectizeAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

class SelectizeDefault extends InputWidget
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }

        parent::run();
    }

    public function registerClientScript()
    {
        $id = $this->options['id'];
        $name = 'select_' . crc32($id);

        if ($this->loadUrl !== null) {
            $url = Url::to($this->loadUrl);
            $this->clientOptions['load'] = new JsExpression("function (query, callback) { if (!query.length) return callback(); $.getJSON('$url', { {$this->queryParam}: query }, function (data) { 
                if(data.hasOwnProperty('optgroups')){
                    if (data.optgroups && data.optgroups.length) {
                        data.optgroups.forEach(function (group) {
                            {$name}[0].selectize.addOptionGroup(group.id, group);
                        });
                    }
                    callback(data.options); 
                }else{
                    callback(data); 
                }
            }).fail(function () { callback(); }); }");
        }

        $options = Json::encode($this->clientOptions);
        $view = $this->getView();
        SelectizeAsset::register($view);
        $view->registerJs("var $name=jQuery('#$id').selectize($options);");
    }
}