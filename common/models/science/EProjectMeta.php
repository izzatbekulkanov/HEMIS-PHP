<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\system\_BaseModel;
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
 * This is the model class for table "e_project_meta".
 *
 * @property int $id
 * @property int $_project
 * @property int $fiscal_year
 * @property float $budget
 * @property int $quantity_members
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EProject $project
 */
class EProjectMeta extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    //protected $_translatedAttributes = ['name'];
    public static function tableName()
    {
        return 'e_project_meta';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getYearOptions()
    {
        $years = [];

        for ($i = date('Y'); $i > (date('Y') - 5); $i--)
            $years [$i] = $i;

        return $years;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['fiscal_year', 'budget', 'quantity_members'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_project', 'fiscal_year', 'quantity_members', 'position'], 'default', 'value' => null],
            [['_project', 'fiscal_year', 'quantity_members', 'position'], 'integer'],
            [['budget'], 'number'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_project'], 'exist', 'skipOnError' => true, 'targetClass' => EProject::className(), 'targetAttribute' => ['_project' => 'id']],
        ]);
    }

    public function getProject()
    {
        return $this->hasOne(EProject::className(), ['id' => '_project']);
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
                    'fiscal_year',
                    'position',
                    '_project',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        /*if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }*/
        if ($this->_project) {
            $query->andFilterWhere(['_project' => $this->_project]);
        }
        return $dataProvider;
    }

    public function searchForProject(EProject $project)
    {
        $query = self::find()
            ->andFilterWhere(['_project' => $project->id])
            ->joinWith(['project']);
        //$query->with(['staffPosition', 'department', 'employeeStatus', 'employmentForm', 'employmentStaff']);

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
        return $this->project->project_number . ' / Fin-' . $this->id;
    }

}
