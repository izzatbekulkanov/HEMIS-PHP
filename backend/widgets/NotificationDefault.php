<?php

namespace backend\widgets;

use lavrentiev\widgets\toastr\NotificationBase;
use lavrentiev\widgets\toastr\ToastrAsset;
use yii\helpers\Html;
use yii\helpers\Json;

class NotificationDefault extends NotificationBase
{
    public function init()
    {
        $this->view->registerAssetBundle(ToastrAsset::className());

        $this->title = is_string($this->title) ? Json::encode(['m' => $this->title]) : Json::encode(['m' => null]);

        $this->message = is_string($this->message) ?
            Json::encode(['m' => $this->message]) : Json::encode(['m' => $this->messageDefault]);

        $this->options = is_array($this->options) ?
            Json::encode($this->options) : Json::encode([]);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (in_array($this->type, $this->types)) {
            return $this->view->registerJs("toastr.{$this->type}({$this->message}.m, {$this->title}.m, {$this->options});");
        }

        return $this->view->registerJs("toastr.{$this->typeDefault}({$this->message}.m, {$this->title}.m, {$this->options});");
    }
}
