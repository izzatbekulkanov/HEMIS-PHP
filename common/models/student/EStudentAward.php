<?php

namespace common\models\student;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\system\_BaseModel;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentSuccess;
use yii\data\ActiveDataProvider;

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
class EStudentAward extends _BaseModel
{

    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';

    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return 'e_student_award';
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
                        '_student',
                        '_education_type',
                        '_education_form',
                        '_curriculum',
                        '_student_level',
                        '_student_group',
                        '_award_group',
                        '_award_category',
                        'award_document',
                        'award_year',
                    ],
                    'required',
                    'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE],
                ],
                [['active'], 'boolean'],
                [['award_year', '_award_category'], 'safe', 'on' => 'search'],
                [['_translations', 'updated_at', 'created_at'], 'safe'],
                [
                    [
                        '_award_category',
                        '_award_group',
                        '_student_level',
                    ],
                    'string',
                    'max' => 64,
                ],
                [['award_year'], 'integer'],
                [
                    ['_student'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EStudent::className(),
                    'targetAttribute' => ['_student' => 'id'],
                ],
                [
                    ['_student_level'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => Course::className(),
                    'targetAttribute' => ['_student_level' => 'code'],
                ],
                [
                    ['_student_group'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EGroup::className(),
                    'targetAttribute' => ['_student_group' => 'id'],
                ],
                [
                    ['_curriculum'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ECurriculum::className(),
                    'targetAttribute' => ['_curriculum' => 'id'],
                ],
                [
                    ['_education_type'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationType::className(),
                    'targetAttribute' => ['_education_type' => 'code'],
                ],
                [
                    ['_education_form'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationForm::className(),
                    'targetAttribute' => ['_education_form' => 'code'],
                ],
                [
                    ['_award_category'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => StudentSuccess::className(),
                    'targetAttribute' => ['_award_category' => 'code'],
                ],
                [
                    ['_award_group'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => StudentSuccess::className(),
                    'targetAttribute' => ['_award_group' => 'code'],
                ],
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_curriculum' => __('Curriculum Curriculum'),
            ]
        );
    }

    public function getStudentLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_student_level']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::class, ['id' => '_student']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::class, ['id' => '_curriculum']);
    }

    public function getStudentGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_student_group']);
    }

    public function getAwardCategory()
    {
        return $this->hasOne(StudentSuccess::className(), ['code' => '_award_category']);
    }

    public function getAwardGroup()
    {
        return $this->hasOne(StudentSuccess::className(), ['code' => '_award_group']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_specialty_id',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->award_year) {
            $query->andFilterWhere(['award_year' => $this->award_year]);
        }
        if ($this->_award_category) {
            $query->andFilterWhere(['_award_category' => $this->_award_category]);
        }

        return $dataProvider;
    }

    public function searchForMonitoring($params)
    {
        $this->load($params);

        $query = self::find();
        //$query->joinWith(['student']);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                    'attributes' => [
                        '_specialty_id',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }

        return $dataProvider;
    }

}
