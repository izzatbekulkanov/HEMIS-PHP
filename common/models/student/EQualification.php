<?php

namespace common\models\student;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_award".
 *
 * @property int $id
 * @property int $_specialty_id
 * @property string $_education_type
 * @property string|null $_education_form
 * @property string $_specialty
 * @property string $_education_year
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property EducationForm $educationForm
 * @property EducationYear $educationYear
 * @property EducationType $educationType
 */
class EQualification extends _BaseModel
{

    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';

    public $_education_type;
    public $_faculty;

    protected $_translatedAttributes = ['name', 'description'];

    public static function tableName()
    {
        return 'e_qualification';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->active]) ? $labels[$this->active] : '';
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        '_specialty',
                        'name',
                        'description',
                    ],
                    'required',
                    'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE],
                ],
                [['active'], 'boolean'],
                [['award_year', '_award_category'], 'safe', 'on' => 'search'],
                [['_faculty', '_education_type', '_translations', 'updated_at', 'created_at'], 'safe'],
                [
                    [
                        'name',
                    ],
                    'string',
                    'max' => 128,
                ],
                [
                    [
                        'description',
                    ],
                    'string',
                    'max' => 1700,
                ],
                [['_specialty'], 'integer'],
                [
                    ['_specialty'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESpecialty::className(),
                    'targetAttribute' => ['_specialty' => 'id'],
                ],
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_education_type' => __('Education Type'),
            ]
        );
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()->joinWith('specialty');

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_specialty',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_specialty._education_type' => $this->_education_type]);
        }

        if ($this->_faculty) {
            $query->andFilterWhere(['e_specialty._department' => $this->_faculty]);
        }

        return $dataProvider;
    }

    public static function getSelectOptions($specialty = "", $faculty = "")
    {
        if ($specialty != "") {
            $query = self::find()->joinWith('specialty')->where(['e_qualification.active' => true, '_specialty' => $specialty]);
            if ($faculty !== "") {
                $query->andFilterWhere(['e_specialty._department' => $faculty]);
            }
            return ArrayHelper::map(
                $query->all(),
            'id',
            'name'
            );
        }
        return ArrayHelper::map(
            self::find()->where(['active' => true])->all(),
            'id',
            'name'
        );
    }

}
