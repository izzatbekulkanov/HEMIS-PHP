<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\Locality;
use common\models\system\classifier\ProjectCurrency;
use common\models\system\classifier\ProjectType;
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
 * This is the model class for table "e_project".
 *
 * @property int $id
 * @property string $name
 * @property string $project_number
 * @property int $_department
 * @property string $_project_type
 * @property string $_locality
 * @property string $_project_currency
 * @property string $contract_number
 * @property string $contract_date
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EDepartment $department
 * @property Locality $locality
 * @property ProjectCurrency $projectCurrency
 * @property ProjectType $projectType
 * @property EProjectExecutor[] $eProjectExecutors
 * @property EProjectMetum[] $eProjectMeta
 */
class EProject extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    //protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_project';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'project_number', '_department', '_project_type', '_locality', '_project_currency', 'contract_number', 'contract_date', 'start_date', 'end_date',], 'required', 'on' => self::SCENARIO_CREATE],
            [['_department', 'position'], 'default', 'value' => null],
            [['_department', 'position'], 'integer'],
            [['contract_date', 'start_date', 'end_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['project_number'], 'unique'],
            [['name', 'project_number', 'contract_number'], 'string', 'max' => 255],
            [['_project_type', '_locality', '_project_currency'], 'string', 'max' => 64],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_locality'], 'exist', 'skipOnError' => true, 'targetClass' => Locality::className(), 'targetAttribute' => ['_locality' => 'code']],
            [['_project_currency'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectCurrency::className(), 'targetAttribute' => ['_project_currency' => 'code']],
            [['_project_type'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectType::className(), 'targetAttribute' => ['_project_type' => 'code']],
        ]);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getLocality()
    {
        return $this->hasOne(Locality::className(), ['code' => '_locality']);
    }

    public function getProjectCurrency()
    {
        return $this->hasOne(ProjectCurrency::className(), ['code' => '_project_currency']);
    }

    public function getProjectType()
    {
        return $this->hasOne(ProjectType::className(), ['code' => '_project_type']);
    }

    public function getEProjectExecutors()
    {
        return $this->hasMany(EProjectExecutor::className(), ['_project' => 'id']);
    }

    public function getEProjectMeta()
    {
        return $this->hasMany(EProjectMeta::className(), ['_project' => 'id']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'position',
                    '_department',
                    'project_number',
                    '_project_type',
                    '_locality',
                    '_project_currency',
                    'start_date',
                    'end_date',
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
            $query->orWhereLike('name', $this->search);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_project_type) {
            $query->andFilterWhere(['_project_type' => $this->_project_type]);
        }
        if ($this->_locality) {
            $query->andFilterWhere(['_locality' => $this->_locality]);
        }
        if ($this->_project_currency) {
            $query->andFilterWhere(['_project_currency' => $this->_project_currency]);
        }
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return $this->project_number;
    }

}
