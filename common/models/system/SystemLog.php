<?php

namespace common\models\system;

use common\models\system\Admin;
use common\models\system\_BaseModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "system_log".
 * @property integer $id
 * @property integer $_admin
 * @property integer $_student
 * @property integer $admin_name
 * @property string $action
 * @property string $type
 * @property string $message
 * @property string $get
 * @property string $post
 * @property string $query
 * @property integer $model_id
 * @property string $ip
 * @property string $x_ip
 * @property string $created_at
 * @property string method
 */
class SystemLog extends _BaseModel
{
    public $search;
    public $user;

    const USER_ADMIN = 1;
    const USER_STUDENT = 2;

    public static function getUserOptions()
    {
        return [
            self::USER_ADMIN => __('Administrator'),
            self::USER_STUDENT => __('Student'),
        ];
    }


    public static function tableName()
    {
        return 'e_system_log';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['search', 'user'], 'safe'],
        ]);
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


    public static function captureAction($message = null)
    {
        if (!Yii::$app->user->isGuest) {
            $log = new SystemLog();
            $admin = Yii::$app->user->identity;
            if ($admin instanceof Admin) {
                $log->_admin = $admin->getId();
            } else {
                $log->_student = $admin->getId();
            }
            $log->admin_name = $admin ? $admin->getFullname() : "";


            $log->message = $message;

            $log->action = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

            $log->ip = Yii::$app->request->getUserIP();
            $log->query = mb_substr(Yii::$app->request->getQueryString(), 0, 2048);

            $get = Yii::$app->request->get();

            $post = Yii::$app->request->post();
            unset($post['_csrf-backend']);
            unset($post['_pjax']);
            unset($get['_pjax']);
            unset($get['_csrf-backend']);

            foreach ($post as $key => $item) {
                if ($key === 'password' || $key === 'confirmation') {
                    $post[$key] = '******';
                }

                if (is_array($item)) {
                    foreach ($item as $itemKey => $data) {
                        if ($itemKey === 'password' || $itemKey === 'confirmation') {
                            $post[$key][$itemKey] = '******';
                        }
                    }
                }
            }
            $log->post = $post;
            $log->get = $get;

            $log->x_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
            $log->save();
        }
    }

    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if ($this->search) {
            $query->orWhereLike('admin_name', $this->search);
            $query->orWhereLike('message', $this->search);
            $query->orWhereLike('action', $this->search);
            $query->orWhereLike('ip', $this->search);
        }

        if ($this->user == self::USER_ADMIN) {
            //@todo
        }

        return $dataProvider;
    }

    public function getShortTitle($len = 12)
    {
        $title = StringHelper::truncateWords($this->message, $len);

        if (strlen($title) > 120) {
            return StringHelper::truncate($title, 120);
        }
        return $title;
    }
}
