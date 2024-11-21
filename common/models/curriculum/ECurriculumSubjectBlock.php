<?php

namespace common\models\curriculum;
use common\models\system\_BaseModel;
use common\models\system\classifier\SubjectBlock;
use common\models\curriculum\ECurriculum;
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
 * This is the model class for table "e_curriculum_subject_block".
 *
 * @property int $id
 * @property string $code
 * @property int $_curriculum
 * @property string $_subject_block
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property HSubjectBlock $subjectBlock
 */
class ECurriculumSubjectBlock extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_curriculum_subject_block';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getBlockByCurriculum($curriculum = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                'active' => self::STATUS_ENABLE
            ])
                ->orderByTranslationField('position')
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['code', '_curriculum', '_subject_block'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_curriculum', 'position'], 'default', 'value' => null],
            [['_curriculum', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['code', '_subject_block'], 'string', 'max' => 64],
            //[['code'], 'unique'],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_subject_block'], 'exist', 'skipOnError' => true, 'targetClass' => SubjectBlock::className(), 'targetAttribute' => ['_subject_block' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSubjectBlock()
    {
        return $this->hasOne(SubjectBlock::className(), ['code' => '_subject_block']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['code' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject_block',
                    'code',
                    'position',
                    '_subject_group',
                    '_education_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            //$query->orWhereLike('name_uz', $this->search, '_translations');
          //  $query->orWhereLike('name_oz', $this->search, '_translations');
           // $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_subject_block) {
            $query->andFilterWhere(['_subject_block' => $this->_subject_block]);
        }

        return $dataProvider;
    }
}
