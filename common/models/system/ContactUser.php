<?php

namespace common\models\system;

use common\models\structure\EDepartment;
use common\models\system\classifier\StructureType;
use DateTime;
use frontend\models\system\Student;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 *
 * @property int $_contact
 * @property int $_user
 *
 * @property Contact $contact
 * @property Contact $user
 */
class ContactUser extends _BaseModel
{
    public static function tableName()
    {
        return 'e_admin_message_contact_user';
    }

    public function getContact()
    {
        return $this->hasOne(Contact::class, ['id' => '_contact']);
    }

    public function getUser()
    {
        return $this->hasOne(Contact::class, ['id' => '_user']);
    }
}
