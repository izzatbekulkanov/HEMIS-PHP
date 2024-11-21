<?php

namespace common\models\structure;

use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\SyncableTrait;
use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\classifier\LocalityType;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\TeacherPositionType;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "e_department".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $_university
 * @property string $_structure_type
 * @property int|null $parent
 * @property int|null $position
 * @property bool|null $active
 * @property bool $sync
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EUniversity $university
 * @property StructureType $structureType
 * @property EDepartment $parentDepartment
 * @property EEmployee $dean
 */
class EDepartment extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_FACULTY = 'faculty';
    const SCENARIO_DEPARTMENT = 'department';
    const SCENARIO_SECTION = 'section';

    protected $_translatedAttributes = ['name'];
    protected $_searchableAttributes = ['name'];

    public static function tableName()
    {
        return 'e_department';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getFaculties()
    {
        return ArrayHelper::map(self::find()
            ->where(['_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY, 'active' => self::STATUS_ENABLE])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getFacultiesCode()
    {
        return ArrayHelper::map(self::find()
            ->where(['_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY, 'active' => self::STATUS_ENABLE])
            ->orderByTranslationField('name')
            ->all(), 'id', 'code');
    }

    public static function getDirections()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->andWhere(['not in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getDepartments()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getDepartmentOptions()
    {
        return ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
            ->orderByTranslationField('name')
            ->all(), 'id', 'name');
    }

    public static function getDepartmentList($faculty = false)
    {
        if ($faculty != "") {
            return self::find()
                ->where(['active' => self::STATUS_ENABLE, 'parent' => $faculty])
                ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
                ->orderByTranslationField('name')
                ->all();
        } else {
            return self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->andWhere(['in', '_structure_type', [StructureType::STRUCTURE_TYPE_DEPARTMENT]])
                ->orderByTranslationField('name')
                ->all();
        }
    }


    public function rules()
    {
        return array_merge(parent::rules(), [
            [['code', 'name', '_type'], 'required', 'on' => self::SCENARIO_FACULTY],
            [['code', 'name', '_structure_type'], 'required', 'on' => self::SCENARIO_SECTION],
            [['code', 'name', 'parent'], 'required', 'on' => self::SCENARIO_DEPARTMENT],
            [['_university', 'parent', 'position'], 'default', 'value' => null],
            [['_university', 'parent', 'position'], 'integer'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['name'], 'string', 'max' => 256],
            [['code'], 'unique'],
            [['_sync_status'], 'safe'],
            [['code'], 'validateCode', 'message' => __('Invalid Code')],
            [['_university'], 'exist', 'skipOnError' => true, 'targetClass' => EUniversity::className(), 'targetAttribute' => ['_university' => 'id']],
            [['_type'], 'exist', 'skipOnError' => true, 'targetClass' => LocalityType::className(), 'targetAttribute' => ['_type' => 'code']],
            [['_structure_type'], 'exist', 'skipOnError' => true, 'targetClass' => StructureType::className(), 'targetAttribute' => ['_structure_type' => 'code']],
            [['code'], 'match', 'pattern' => '/^[0-9\-]{2,64}$/', 'message' => __('Use only alpha-number characters and underscore')],
        ]);
    }

    public function validateCode($attribute, $options)
    {
        $prefix = $this->university->code;
        $pattern = "/^$prefix\-2[0-9]{2}$/";

        if ($this->structureType->code == StructureType::STRUCTURE_TYPE_FACULTY) {
            $pattern = "/^$prefix\-1[0-9]{2}$/";
        } else if ($this->structureType->code == StructureType::STRUCTURE_TYPE_DEPARTMENT) {
            $faculty = explode('-', $this->parentDepartment->code);
            if (count($faculty) == 2) {
                $faculty = $faculty[1];
                $pattern = "/^$prefix\-$faculty\-[0-9]{2}$/";
            }
        }

        if (!preg_match($pattern, $this->$attribute)) {
            $this->addError($attribute, __('Invalid code'));
        }
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'parent' => __('Faculty'),
            '_section_type' => __('Type'),
        ]);
    }

    public function getUniversity()
    {
        return $this->hasOne(EUniversity::className(), ['id' => '_university']);
    }

    public function getStructureType()
    {
        return $this->hasOne(StructureType::className(), ['code' => '_structure_type']);
    }

    public function getLocalityType()
    {
        return $this->hasOne(LocalityType::className(), ['code' => '_type']);
    }

    public function getParentDepartment()
    {
        return $this->hasOne(self::className(), ['id' => 'parent']);
    }

    public function searchByType($structureType, $params = [])
    {
        $this->load($params);

        $query = self::find()->with(['parentDepartment', 'structureType']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'position',
                    'parent',
                    '_type',
                    '_structure_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
            $query->orWhereLike('name', $this->search);
        }

        if ($structureType) {
            $query->andFilterWhere(['_structure_type' => $structureType]);
        } else {
            $query->andFilterWhere(['not in', '_structure_type', [StructureType::STRUCTURE_TYPE_FACULTY, StructureType::STRUCTURE_TYPE_DEPARTMENT]]);
        }

        if ($this->parent) {
            $query->andFilterWhere(['parent' => $this->parent]);
        }
        if ($this->_structure_type) {
            $query->andFilterWhere(['_structure_type' => $this->_structure_type]);
        }
        if ($this->_type) {
            $query->andFilterWhere(['_type' => $this->_type]);
        }

        return $dataProvider;
    }

    public function search($params = [])
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'position',
                    'parent',
                    '_type',
                    '_structure_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
            $query->orWhereLike('name', $this->search);
        }

        if ($this->_sync_status) {
            $query->andFilterWhere(['_sync_status' => $this->_sync_status]);
        }

        return $dataProvider;
    }

    public function beforeSave($insert)
    {
        $this->_university = EUniversity::findCurrentUniversity()->id;
        return parent::beforeSave($insert);
    }

    public function getDescriptionForSync()
    {
        return $this->getTranslation('name', Config::LANGUAGE_UZBEK);
    }

    public function getIdForSync()
    {
        return $this->code;
    }

    public static function getModel($id)
    {
        return self::findOne(['code' => $id]);
    }


    public function getDean()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee'])
            ->viaTable('e_employee_meta', ['_department' => 'id'], function ($query) {
                $ids = EDepartment::find()
                    ->select(['id'])
                    ->where(['active' => self::STATUS_ENABLE, '_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY])
                    ->column();

                $query->andFilterWhere(['e_employee_meta._department' => $ids, 'e_employee_meta._position' => TeacherPositionType::TEACHER_POSITION_TYPE_DEAN]);

            });
    }

}
