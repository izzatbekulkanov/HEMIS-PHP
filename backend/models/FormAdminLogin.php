<?php

namespace backend\models;

use common\components\Config;
use common\models\employee\EEmployee;
use common\models\system\Admin;
use common\models\system\SystemLogin;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class FormAdminLogin extends Model
{
    public $login;
    public $fails;
    public $failLimit = 2;
    public $password;
    public $rememberMe = false;
    public $reCaptcha;
    private $_user = false;

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['rememberMe', 'safe'],
            getenv('RECAPTCHA_DISABLE_BACKEND') ? ['reCaptcha', 'captcha', 'captchaAction' => 'ajax/captcha'] : ['reCaptcha',
                \himiklab\yii2\recaptcha\ReCaptchaValidator3::className(),
                'threshold' => 0.5,
                'action' => 'adminLogin',
                'message' => __('Invalid recaptcha verify response'),
            ],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'login' => __('Login'),
            'password' => __('Password'),
            'reCaptcha' => __('Tekshiruv kodi'),
        ];
    }


    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addError($attribute, __('Invalid Login or Password'));
            } else if (!$user->validatePassword($this->password)) {
                $this->addError($attribute, __('Invalid Login or Password'));
                SystemLogin::captureFail($this->login);
            } else if ($user && $user->role == null) {
                $this->addError($attribute, __('User has no roles'));
            }
        }
    }

    public function getUser()
    {
        /**
         * @var $emp EEmployee
         */
        if ($this->_user === false) {
            if (intval($this->login) == $this->login && strlen($this->login) > 9) {
                if ($emp = EEmployee::findOne(['employee_id_number' => $this->login])) {
                    $this->_user = $emp->admin;
                }
            }

            if ($this->_user == null) {
                $this->_user = Admin::findByLogin($this->login);
            }

        }

        return $this->_user;
    }

    public function login()
    {
        $result = false;
        $message = __('Tizim serverida internetga chiqish mavjud emas');
        try {
            if ($this->validate()) {
                $user = $this->getUser();
                if (!preg_match(Admin::PASSWORD_VALIDATOR, $this->password)) {
                    $user->updateAttributes(['password_valid' => false]);
                }

                $result = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 6 : 3600);
                SystemLogin::captureSuccess($this->login);
            }
        } catch (\Exception $exception) {
            if ($exception->getCode() == 2) {
                $this->addError('password', $message);
            } else {
                $this->addError('password', $exception->getMessage());
            }
        }

        return $result;
    }
}
