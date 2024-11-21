<?php


namespace backend\widgets;


use backend\widgets\filekit\Upload;
use yii\helpers\Html;

class UploadDefault extends Upload
{
    public $accept;
    public $hiddenInputId = null;

    public function run()
    {
        $this->registerClientScript();
        $content = Html::beginTag('div');
        $content .= Html::hiddenInput($this->name, null, [
            'class' => 'empty-value',
            'id' => $this->hiddenInputId === null ? $this->options['id'] : $this->hiddenInputId
        ]);
        $inputOptions = [
            'name' => $this->getFileInputName(),
            'id' => $this->getId(),
            'multiple' => $this->multiple
        ];

        if ($this->accept) {
            $inputOptions['accept'] = $this->accept;
        }

        $content .= Html::fileInput($this->getFileInputName(), null, $inputOptions);
        $content .= Html::endTag('div');
        return $content;
    }
}