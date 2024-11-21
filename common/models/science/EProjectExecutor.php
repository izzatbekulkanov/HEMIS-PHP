<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\employee\EEmployee;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\ProjectExecutorType;
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
 * This is the model class for table "e_project_executor".
 *
 * @property int $id
 * @property int $_project
 * @property string $_project_executor_type
 * @property int $_executor_type
 * @property int|null $_id_number
 * @property string|null $outsider
 * @property string $start_date
 * @property string|null $end_date
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EProject $project
 * @property ProjectExecutorType $projectExecutorType
 */
class EProjectExecutor extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    const EXECUTOR_TYPE_LEADER = 11;
    const EXECUTOR_TYPE_EXECUTOR = 12;

    public static function tableName()
    {
        return 'e_project_executor';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getExecutorStatusOptions()
    {
        return [
            self::EXECUTOR_TYPE_LEADER => __('Project Leader'),
            self::EXECUTOR_TYPE_EXECUTOR => __('Project Executor'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_project_executor_type', '_executor_type', 'start_date', 'end_date'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_project', '_executor_type', '_id_number', 'position'], 'default', 'value' => null],
            [['_project', '_executor_type', '_id_number', 'position'], 'integer'],
            [['start_date', 'end_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_project_executor_type'], 'string', 'max' => 64],
            [['outsider'], 'string', 'max' => 255],
            [['_project'], 'exist', 'skipOnError' => true, 'targetClass' => EProject::className(), 'targetAttribute' => ['_project' => 'id']],
            [['_project_executor_type'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectExecutorType::className(), 'targetAttribute' => ['_project_executor_type' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_id_number' => __('Member Name'),
        ]);
    }

    public function getProject()
    {
        return $this->hasOne(EProject::className(), ['id' => '_project']);
    }

    public function getProjectExecutorType()
    {
        return $this->hasOne(ProjectExecutorType::className(), ['code' => '_project_executor_type']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_id_number']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_id_number']);
    }

    public function getDoctorateStudent()
    {
        return $this->hasOne(EDoctorateStudent::className(), ['id' => '_id_number']);
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
                    '_project',
                    '_project_executor_type',
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
        if ($this->_project) {
            $query->andFilterWhere(['_project' => $this->_project]);
        }
        if ($this->_project_executor_type) {
            $query->andFilterWhere(['_project_executor_type' => $this->_project_executor_type]);
        }
        return $dataProvider;
    }

    public function searchForProject(EProject $project)
    {
        $query = self::find()
            ->andFilterWhere(['_project' => $project->id])
            ->joinWith(['project']);
        $query->with(['projectExecutorType']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return $this->project->project_number . ' / Ex-' . $this->id;
    }


}
