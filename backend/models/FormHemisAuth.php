<?php

namespace backend\models;

use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiError;
use common\models\system\Admin;
use common\models\system\SystemLogin;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class FormHemisAuth extends Model
{
    public $login;
    public $password;
    public $reCaptcha;
    private $_user = false;

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['rememberMe', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'login' => __('Login for HEMIS'),
            'password' => __('Password for HEMIS'),
        ];
    }


    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Admin::findByLogin($this->login);
        }

        return $this->_user;
    }

    public function login()
    {
        $result = false;
        $this->password = strip_tags($this->password);
        $this->login = strip_tags($this->login);

        if ($this->validate()) {
            try {
                HemisApi::getApiClient()->apiLogin($this->login, $this->password);
                $result = true;
            } catch (HemisApiError $e) {
                $this->addError('password', $e->getMessage());
            }
        }

        return $result;
    }
}
