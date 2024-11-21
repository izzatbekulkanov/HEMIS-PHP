<?php

namespace frontend\models\form;

use common\components\Config;
use common\models\archive\EStudentDiploma;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use common\models\system\SystemLogin;
use frontend\models\system\Student;
use himiklab\yii2\recaptcha\ReCaptchaValidator3;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class FormStudentPin extends Model
{
    public $pin;
    public $reCaptcha;
    private $_user = false;

    public function rules()
    {
        return [
            [['pin'], 'required'],
            getenv('RECAPTCHA_ENABLE') ?
                ['reCaptcha',
                    \himiklab\yii2\recaptcha\ReCaptchaValidator3::className(),
                    'threshold' => 0.3,
                    'action' => 'diploma',
                    'message' => __('Invalid recaptcha verify response'),
                ] :
                ['reCaptcha', 'captcha', 'captchaAction' => 'dashboard/captcha'],
            [['pin'], 'validateUser'],
        ];
    }

    public function validateUser($attribute, $options)
    {
        if (!$this->getUser()) {
            $this->addError($attribute, __('Invalid Passport Pin'));
        }
    }

    public function attributeLabels()
    {
        return [
            'pin' => __('Passport Pin'),
        ];
    }

    /**
     * @return Student
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Student::findOne(['passport_pin' => $this->pin]);
        }

        return $this->_user;
    }

    /**
     * @return EStudentDiploma | null
     */
    public function findStudentDiploma()
    {
        if ($this->validate()) {
            if ($student = $this->getUser()) {
                if ($diploma = EStudentDiploma::findOne(['_student' => $student->id, 'accepted' => true])) {
                    return $diploma;
                } else {
                    $this->addError('pin', __('Diploma not found'));
                }
            }
        }
    }

    /**
     * @return EStudentContract[] | null
     */
    public function findStudentContracts()
    {
        if ($this->validate()) {
            if ($student = $this->getUser()) {
                $contracts = EStudentContract::find()
                    ->where([
                        '_student' => $student->id,
                        'active' => true,
                        'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED,
                    ])
                    ->orderBy(['date' => SORT_DESC])
                    ->all();
                if (count($contracts)) {
                    return $contracts;
                } else {
                    $this->addError('pin', __('Contract not found'));
                }
            }
        }
    }
}
