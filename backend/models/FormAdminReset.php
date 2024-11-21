<?php

namespace backend\models;

use common\models\system\Admin;
use yii\base\InvalidParamException;
use yii\base\Model;

/**
 * Password reset request form
 */
class FormAdminReset extends Model
{
    public $email;
    public $password;
    public $confirmation;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'confirmation'], 'required', 'on' => ['resetPassword']],
            ['password', 'string', 'min' => 6],
            [['confirmation'], 'compare', 'on' => ['resetPassword'],
                'compareAttribute' => 'password',
                'skipOnEmpty' => false,
                'message' => __('Confirmation does not match')],

            [['email', 'password'], 'filter', 'filter' => 'trim'],
            ['email', 'required', 'on' => ['resetRequest']],
            ['email', 'email'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => __('Email'),
        ];
    }

    public function resetAdminPassword($token)
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Password reset token cannot be blank.');
        }

        if ($admin = Admin::findByPasswordResetToken($token)) {
            $admin->setPassword($this->password);
            return $admin->save();
        }

        throw new InvalidParamException('Wrong password reset token.');
    }

    public function sendEmail()
    {
        /**
         * @var $admin Admin
         */
        $admin = Admin::findOne([
            'status' => Admin::STATUS_ENABLE,
            'email' => $this->email,
        ]);

        if ($admin) {
            if (!$admin->isPasswordResetTokenValid()) {
                $admin->generatePasswordResetToken();
            }

            if ($admin->save()) {
                return \Yii::$app->mailer->compose(['html' => 'backend/passwordResetToken-html', 'text' => 'backend/passwordResetToken-text'], ['user' => $admin])
                    ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])
                    ->setTo($this->email)
                    ->setSubject('Password reset for ' . \Yii::$app->name)
                    ->send();
            }
        }

        return false;
    }
}
