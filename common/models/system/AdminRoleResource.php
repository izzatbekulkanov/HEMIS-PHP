<?php

namespace common\models\system;

use yii\behaviors\TimestampBehavior;

/**
 * @property integer $_role
 * @property integer $_resource
 */
class AdminRoleResource extends _BaseModel
{
    public static function tableName()
    {
        return 'e_admin_role_resource';
    }
    public function behaviors()
    {
        return [
        ];
    }
}
