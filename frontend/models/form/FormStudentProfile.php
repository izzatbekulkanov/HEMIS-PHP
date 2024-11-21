<?php

namespace frontend\models\form;

use common\models\student\EStudent;
use common\models\system\classifier\Accommodation;
use common\models\system\classifier\Soato;
use yii\base\Model;
use yii\helpers\Inflector;

class FormStudentProfile extends EStudent
{
    public function rules()
    {
        return [
            [['image'], 'safe'],
            //[['current_address'], 'string', 'max' => 255],
            //[['current_address'], 'match', 'pattern' => '/^[A-Za-z\ 0-9\(\)\,\.\-\_\"\'‘’`\/]+$/i', 'message' => __('Manzil ma\'lumotlari lotinda kiritilsin')],

           // [['_current_district', '_current_province'], 'exist', 'skipOnError' => true, 'targetClass' => Soato::className(), 'targetAttribute' => ['_province' => 'code']],
           // [['_accommodation'], 'exist', 'skipOnError' => true, 'targetClass' => Accommodation::className(), 'targetAttribute' => ['_accommodation' => 'code']],

            [['change_password'], 'safe'],

            [['password', 'confirmation'], 'required', 'when' => function ($model) {
                return $model->change_password == 1;
            }, 'whenClient' => "function (attribute, value) {return $('#change_password').is(':checked');}"],

            [['confirmation'], 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => __('Confirmation does not match'), 'when' => function ($model) {
                return $model->change_password == 1;
            }],
           // [['phone'], 'match', 'pattern' => '/^[0-9]{7,12}$/', 'message' => __('Wrong mobile phone number')],

            [['password'], 'match', 'pattern' => self::PASSWORD_VALIDATOR, 'when' => function () {
                return true;
            }, 'message' => __('Kamida {length} ta belgi va raqamlardan tashkil topishi kerak', ['length' => 8])],
        ];
    }

    public function updateProfile(EStudent $_user)
    {

    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->setAsShouldBeSynced();
        parent::afterSave($insert, $changedAttributes);
    }
}