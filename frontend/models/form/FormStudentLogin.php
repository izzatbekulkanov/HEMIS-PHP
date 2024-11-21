<?php

namespace frontend\models\form;

use common\components\Config;
use common\models\student\EStudent;
use common\models\system\Admin;
use common\models\system\SystemLogin;
use frontend\models\system\Student;
use himiklab\yii2\recaptcha\ReCaptchaValidator3;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class FormStudentLogin extends Model
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
            getenv('RECAPTCHA_ENABLE') ?
                ['reCaptcha',
                    \himiklab\yii2\recaptcha\ReCaptchaValidator3::className(),
                    'threshold' => 0.3,
                    'action' => 'studentLogin',
                    'message' => __('Invalid recaptcha verify response'),
                    'when' => function () {
                        return $this->fails >= $this->failLimit;
                    }
                ] :
                ['reCaptcha', 'captcha', 'captchaAction' => 'dashboard/captcha', 'when' => function () {
                    return $this->fails >= $this->failLimit;
                }],
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
                SystemLogin::captureFail($this->login, SystemLogin::TYPE_LOGIN, SystemLogin::USER_STUDENT);
            } else {
                if ($user->meta == null || $user->meta->curriculum == null) {
                    $this->addError($attribute, __('Student does not have curriculum'));
                }
            }
        }
    }

    /**
     * @return Student
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Student::findByLogin($this->login);
        }

        return $this->_user;
    }

    public function login()
    {
        $result = false;

        try {
            if ($this->validate()) {
                $user = $this->getUser();

                if (!preg_match(EStudent::PASSWORD_VALIDATOR, $this->password)) {
                    $user->updateAttributes(['password_valid' => false]);
                }

                $result = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 6 : 3600);
                SystemLogin::captureSuccess($this->login, SystemLogin::TYPE_LOGIN, SystemLogin::USER_STUDENT);
            }
        } catch (\Exception $exception) {
            $this->addError('password', $exception->getMessage());
        }

        return $result;
    }
}
