<?php

namespace common\models\system;

use common\components\Config;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\student\EGroup;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "admin".
 * @property string $full_name
 * @property string $login
 * @property string $password
 * @property string[] $image
 * @property string $description
 * @property string $email
 * @property string $telephone
 * @property string $status
 * @property string $auth_key
 * @property string $access_token
 * @property string $password_reset_token
 * @property DateTime $password_reset_date
 * @property DateTime $password_date
 * @property boolean $password_valid
 * @property string $resource
 * @property integer $_role
 * @property integer $_employee
 * @property string $language
 * @property AdminRole $role
 * @property Contact $contact
 * @property EEmployee $employee
 * @property AdminRole[] $roles
 * @property EGroup[] $tutorGroups
 *
 */
class Admin extends _BaseModel implements IdentityInterface
{
    const CACHE_KEY_ADMIN_MENU = 'admin_menu';

    protected $_searchableAttributes = ['full_name', 'login', 'email'];
    protected $_translatedAttributes = ['full_name', 'description'];
    const SCENARIO_PROFILE = 'profile';
    const PASSWORD_VALIDATOR = '/^(?=.*\d)(?=.*[A-Za-z]).{8,}$/';

    public $confirmation;
    public $change_password;

    const STATUS_ENABLE = 'enable';
    const STATUS_DISABLE = 'disable';
    const STATUS_BLOCKED = 'blocked';

    const SUPER_ADMIN_LOGIN = 'admin';
    const TECH_ADMIN_LOGIN = 'techadmin';
    const SUPER_ADMIN_EMAIL = 'admin@hemis.uz';

    const CACHE_TAG_ADMIN_MENU = 'admin_menu';

    public static function tableName()
    {
        return 'e_admin';
    }


    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enabled'),
            self::STATUS_DISABLE => __('Disabled'),
            self::STATUS_BLOCKED => __('Blocked'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->status]) ? $labels[$this->status] : '';
    }


    public static function getArrayOptions()
    {
        $data = self::find()
            ->orderBy(['full_name' => SORT_ASC])
            ->where(['status' => self::STATUS_ENABLE])
            ->andFilterWhere(['!=', 'login', self::TECH_ADMIN_LOGIN])
            ->all();

        return ArrayHelper::merge([], ArrayHelper::map($data, 'id', 'full_name'));
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['full_name', 'login', 'status'], 'required', 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],

            [['full_name', 'language'], 'required', 'on' => self::SCENARIO_PROFILE],

            [['password', 'confirmation'], 'required', 'on' => self::SCENARIO_INSERT],

            [['password', 'confirmation'], 'required', 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_PROFILE], 'when' => function ($model) {
                return $model->change_password == 1;
            }, 'whenClient' => "function (attribute, value) {return $('#change_password').is(':checked');}"],

            [['confirmation'], 'compare', 'on' => self::SCENARIO_INSERT, 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => __('Confirmation does not match')],

            [['confirmation'], 'compare', 'on' => [self::SCENARIO_INSERT, self::SCENARIO_PROFILE, self::SCENARIO_UPDATE], 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => __('Confirmation does not match'), 'when' => function ($model) {
                return $model->change_password == 1;
            }],

            [['language'], 'in', 'range' => array_keys(Config::getLanguageOptions())],
            [['_role'], 'in', 'range' => array_keys(AdminRole::getOptionsArray()), 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],
            [['_role'], 'in', 'range' => array_keys(AdminRole::getAllOptionsArray()), 'on' => [self::SCENARIO_SEARCH]],

            [['resource'], 'safe', 'on' => self::SCENARIO_UPDATE],

            [['login', 'email'], 'unique', 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],
            [['login'], 'match', 'pattern' => '/^[a-z0-9_]{3,255}$/', 'message' => __('Use only alpha-number characters and underscore'), 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],
            [['email'], 'email'],

            [['slug', 'image', 'description', 'change_password', '_department'], 'safe'],

            [['full_name', 'password'], 'string', 'max' => 128],
            [['password'], 'match', 'pattern' => self::PASSWORD_VALIDATOR, 'on' => [self::SCENARIO_INSERT], 'when' => function () {
                return true;
            }, 'message' => __('Kamida {length} ta belgi va raqamlardan tashkil topishi kerak', ['length' => 8])],
            [['password'], 'match', 'pattern' => self::PASSWORD_VALIDATOR, 'on' => [self::SCENARIO_PROFILE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->change_password;
            }, 'message' => __('Kamida {length} ta belgi va raqamlardan tashkil topishi kerak', ['length' => 8])],


            [['email'], 'string', 'max' => 64],
            [['telephone'], 'match', 'pattern' => '/^[\+\(]{0,2}[998]{0,3}[\)]{0,1}[ ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{3}[- ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{2}$/', 'message' => __('Wrong mobile phone number')],

            [['telephone'], 'string', 'max' => 32],
            [['image', 'roleIds'], 'safe'],
            [['telephone', 'email', 'full_name', 'login'], 'filter', 'filter' => function ($value) {
                return trim(strip_tags($value));
            }],
        ]);
    }

    public function getRole()
    {
        return $this->hasOne(AdminRole::className(), ['id' => '_role'])->with(['resources']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['_admin' => 'id']);
    }

    public function getRoles()
    {
        $field = _BaseModel::getLanguageAttributeCode('name');

        return $this->hasMany(AdminRole::className(), ['id' => '_role'])
            ->viaTable('e_admin_roles', ['_admin' => 'id'])
            ->orderBy(new Expression("e_admin_role._translations->>'$field' ASC, e_admin_role.name ASC"))
            ->with(['resources']);
    }

    public function getTutorGroups()
    {
        $field = _BaseModel::getLanguageAttributeCode('name');

        return $this->hasMany(EGroup::className(), ['id' => '_group'])
            ->viaTable('e_admin_group', ['_admin' => 'id'])
            ->orderBy(new Expression("e_group._translations->>'$field' ASC, e_group.name ASC"))
            ->indexBy('id');
    }

    /**
     * Finds user by login
     * @param string $login
     * @return Admin|null
     */
    public static function findByLogin($login)
    {
        return static::findOne(['login' => $login, 'status' => self::STATUS_ENABLE]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setPassword($password)
    {
        if ($this->hasAttribute('password_date')) {
            $this->password_date = $this->getTimestampValue();
            $this->password_valid = true;
        }

        $this->password = Yii::$app->security->generatePasswordHash($password);
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->access_token = Yii::$app->security->generateRandomString();

        $this->removePasswordResetToken();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ENABLE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ENABLE]);
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function afterFind()
    {
        return parent::afterFind();
    }

    public function beforeDelete()
    {
        if ($this->isSuperAdmin()) {
            throw new Exception(__('Can not delete supper admin'));
        }

        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        if ($this->change_password || $this->isNewRecord) $this->setPassword($this->confirmation);

        if ($this->isSuperAdmin()) {
            $this->status = self::STATUS_ENABLE;
            $this->login = self::SUPER_ADMIN_LOGIN;
            $this->roleIds[] = AdminRole::getSuperAdminRole()->id;
        }

        if ($this->isTechAdmin()) {
            $this->status = self::STATUS_ENABLE;
            $this->login = self::TECH_ADMIN_LOGIN;
            $this->roleIds[] = AdminRole::getSuperAdminRole()->id;
        }

        return parent::beforeSave($insert);
    }

    public $roleIds = [];

    public function afterSave($insert, $changedAttributes)
    {
        if (Yii::$app->has('request') && Yii::$app instanceof Application) {
            if ($this->scenario == self::SCENARIO_INSERT || $this->scenario == self::SCENARIO_UPDATE) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $this->unlinkAll('roles', true);
                    $roleId = null;

                    if (is_array($this->roleIds))
                        foreach ($this->roleIds as $id) {
                            if ($role = AdminRole::findOne($id)) {
                                $this->link('roles', $role);
                                $roleId = $role->id;
                            }
                        }
                    $this->updateAttributes(['_role' => $roleId]);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        if ($this->hasAttribute('_employee'))
            if ($this->employee) {
                $this->employee->updateAttributes(['_admin' => $this->id]);
            }

        if (Yii::$app->db->schema->getTableSchema(Contact::tableName(), true) != null)
            if ($this->contact == null) {
                Contact::indexAdmins($this);
            }

        parent::afterSave($insert, $changedAttributes);
    }

    public function isSuperAdmin()
    {
        return $this->login == self::SUPER_ADMIN_LOGIN;
    }

    public function isTechAdmin()
    {
        return $this->login == self::TECH_ADMIN_LOGIN;
    }

    public function hasRole(AdminRole $role)
    {
        return AdminRoles::find()->where(['_admin' => $this->id, '_role' => $role->id])->count() > 0;
    }

    public function canAccessToResource($path)
    {
        $path = trim($path, '/');

        if (strpos($path, 'ajax') === 0) {
            return true;
        }
        if ($this->isSuperAdmin() || $this->isTechAdmin() || $this->role->canAccessToResource($path)) {
            return true;
        }

        return false;
    }

    /**
     * @param $token
     * @return Admin
     */
    public static function findByPasswordResetToken($token)
    {
        $admin = static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ENABLE,
        ]);

        return $admin && $admin->isPasswordResetTokenValid() ? $admin : null;
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString();
        $this->password_reset_date = $this->getTimestampValue();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
        $this->password_reset_date = null;
    }

    public function isPasswordResetTokenValid()
    {
        /**
         * @todo
         */
        if ($this->password_reset_date instanceof \DateTime && $this->password_reset_token) {
            $expire = Yii::$app->params['passwordResetTokenExpire'];

            return $this->password_reset_date->getTimestamp() + $expire >= time();
        }

        return false;
    }

    public function getFullName()
    {
        return Html::encode($this->full_name ?: $this->login);
    }


    public function login()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->save();
        return $this;
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find()->with(['role']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['login' => SORT_ASC],
                'attributes' => $this->attributes()
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);


        if ($this->search) {
            $query->orWhereLike('login', $this->search);
            $query->orWhereLike('full_name', $this->search);
            $query->orWhereLike('email', $this->search);
            $query->orWhereLike('telephone', $this->search);
        }

        if ($this->_role) {
            $query->andFilterWhere(['id' => AdminRoles::find()->select(['_admin'])->where(['_role' => $this->_role])->column()]);
        }
        $query->andFilterWhere(['!=', 'login', self::TECH_ADMIN_LOGIN]);

        return $dataProvider;
    }


    public static function findAllAccounts($exclude = [])
    {
        $options = self::find()
            ->where(['status' => self::STATUS_ENABLE])
            ->orderBy(['full_name' => SORT_ASC]);

        if (count($exclude)) {
            //$options->andFilterWhere(['NOT IN' => ['id' => $exclude]]);
        }

        $options->andFilterWhere(['!=', 'login', self::TECH_ADMIN_LOGIN]);

        return ArrayHelper::map($options->all(), 'id', function ($item) {
            return $item->getFullName();
        });
    }

    public $_department;

    public function searchForTutor($params, $faculty = null)
    {
        $this->load($params);

        if ($this->_department == null) {
            $this->_department = $faculty;
        }

        $query = self::find();

        if ($this->search) {
            $query->orWhereLike('login', $this->search);
            $query->orWhereLike('full_name', $this->search);
            $query->orWhereLike('email', $this->search);
        }

        $ids = AdminRoles::find()
            ->select(['_admin'])
            ->andFilterWhere([
                '_role' => AdminRole::findOne(['code' => AdminRole::CODE_TUTOR])->id
            ])
            ->column();

        $query->andFilterWhere([
            'id' => count($ids) ? $ids : -1
        ]);

        if ($this->_department) {
            $ids = EEmployee::find()
                ->select(['_admin'])
                ->andFilterWhere([
                    'id' => EEmployeeMeta::find()->select(['_employee'])
                        ->andFilterWhere([
                            '_department' => $this->_department
                        ])
                        ->column()
                ])
                ->column();

            $query->andFilterWhere([
                'id' => count($ids) ? $ids : -1
            ]);
        }


        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['login' => SORT_ASC],
                'attributes' => $this->attributes()
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    public function canAccessToGroup(EGroup $group)
    {
        return isset($this->tutorGroups[$group->id]);
    }
}
