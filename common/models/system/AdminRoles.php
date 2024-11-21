<?php

namespace common\models\system;

use common\models\system\Admin;
use common\models\system\_BaseModel;
use DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\helpers\StringHelper;

class AdminRoles extends ActiveRecord
{
    public static function tableName()
    {
        return 'e_admin_roles';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function () {
                    return $this->getTimestampValue();
                },
            ],
        ];
    }

    public function getTimestampValue()
    {
        return new DateTime('now');
    }
}
