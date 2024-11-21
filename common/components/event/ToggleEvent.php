<?php

namespace common\components\event;

use yii\base\Event;

class ToggleEvent extends Event
{
    public $table;
    public $attribute;
    public $id;
    public $primaryKey;
    public $hasUpdate;

}