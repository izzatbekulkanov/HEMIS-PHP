<?php

namespace common\models\system;

use DateInterval;
use DateTime;
use frontend\models\system\Student;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "login".
 * @property string $id
 * @property string $status
 * @property string $type
 * @property string $user
 * @property string $ip
 * @property string $login
 * @property string $created_at
 */
class SystemLogin extends _BaseModel
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';

    const TYPE_LOGIN = 'login';
    const TYPE_RESET = 'reset';
    const USER_ADMIN = 1;
    const USER_STUDENT = 2;

    public static function tableName()
    {
        return 'e_system_login';
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_LOGIN => __('Login'),
            self::TYPE_RESET => __('Reset'),
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_SUCCESS => __('Success'),
            self::STATUS_FAIL => __('Fail'),
        ];
    }

    public static function getUserOptions()
    {
        return [
            self::USER_ADMIN => __('Administrator'),
            self::USER_STUDENT => __('Student'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->status]) ? $labels[$this->status] : '';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => $this->getTimestampValue(),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['search', 'type', 'status', 'user'], 'safe'],
        ];
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'login',
                    'ip',
                    'status',
                    'created_at',
                    'admin',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhere(['like', 'login', $this->search]);
            $query->orFilterWhere(['like', 'ip', $this->search]);
        }

        if ($this->status) {
            $query->andFilterWhere(['status' => $this->status]);
        }

        if ($this->type) {
            $query->andFilterWhere(['type' => $this->type]);
        }

        if ($this->user) {
            $query->andFilterWhere(['user' => $this->user]);
        }


        return $dataProvider;
    }

    public function searchForStudent(Student $student, $params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'login',
                    'ip',
                    'status',
                    'created_at',
                    'admin',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orFilterWhere(['like', 'ip', $this->search]);
        }

        if ($this->status) {
            $query->andFilterWhere(['status' => $this->status]);
        }
        $query->andFilterWhere(['user' => self::USER_STUDENT, 'login' => $student->getLoginParam()]);


        return $dataProvider;
    }

    public static function captureSuccess($login, $type = self::TYPE_LOGIN, $user = self::USER_ADMIN)
    {
        $login = new self(
            [
                'ip' => Yii::$app->request->getUserIP(),
                'login' => $login,
                'status' => self::STATUS_SUCCESS,
                'type' => $type,
                'user' => $user,
            ]
        );

        return $login->save();
    }

    public static function captureFail($login, $type = self::TYPE_LOGIN, $user = self::USER_ADMIN)
    {
        $login = new self(
            [
                'ip' => Yii::$app->request->getUserIP(),
                'login' => $login,
                'status' => self::STATUS_FAIL,
                'type' => $type,
                'user' => $user,
            ]
        );

        return $login->save();
    }


    public static function getIsLoginActionLimited($ip)
    {
        $date = (new DateTime())->add(DateInterval::createFromDateString('-5 minutes'));

        return self::find()
                ->where(['ip' => $ip, 'status' => self::STATUS_FAIL, 'type' => self::TYPE_LOGIN])
                ->afterDate($date)
                ->count() > 50;
    }
}
