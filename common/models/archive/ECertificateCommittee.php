<?php

namespace common\models\archive;

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationType;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_certificate_committee".
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $_specialty
 * @property string $_education_type
 * @property string $_education_year
 * @property int $_department
 * @property int $_faculty
 * @property bool|null $active
 *
 * @property ESpecialty $specialty
 * @property EStudent $student
 */
class ECertificateCommittee extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    const TYPE_EXAM = 'exam';
    const TYPE_DEFEND = 'defend';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_certificate_committee';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_EXAM => __('Exam'),
            self::TYPE_DEFEND => __('Defend'),
        ];
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    ['name', 'type', '_education_type', '_education_year', '_specialty', '_faculty', '_department'],
                    'required',
                    'on' => self::SCENARIO_INSERT
                ],
                [['_department', '_faculty', '_specialty'], 'integer'],
                [['active'], 'boolean'],
                //[['_specialty'], 'string', 'max' => 64],
                [['name'], 'string', 'max' => 256],
                [
                    ['_specialty'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESpecialty::className(),
                    'targetAttribute' => ['_specialty' => 'id']
                ],
                [
                    ['_faculty'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EDepartment::className(),
                    'targetAttribute' => ['_faculty' => 'id']
                ],
                [
                    ['_department'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EDepartment::className(),
                    'targetAttribute' => ['_department' => 'id']
                ],
                [
                    ['_education_type'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationType::className(),
                    'targetAttribute' => ['_education_type' => 'code']
                ],
                [
                    ['_education_year'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationYear::className(),
                    'targetAttribute' => ['_education_year' => 'code']
                ],
            ]
        );
    }

    public function getTypeLabel()
    {
        return self::getTypeOptions()[$this->type] ?? $this->type;
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getFaculty()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_faculty']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getMembers()
    {
        return $this->hasMany(ECertificateCommitteeMember::className(), ['_certificate_committee' => 'id']);
    }

    public function getMembersCount()
    {
        return $this->getMembers()->select('id')->count();
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['lesson_date' => SORT_ASC],
                    'attributes' => [
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
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }

        return $dataProvider;
    }

    public static function getSelectOptions($faculty = "", $department = "", $eduYear = "", $type = "")
    {
        if ($faculty != "") {
            $query = self::find()->select(['id', '_faculty', '_translations', 'name', 'active', '_department'])->where(['active' => true, '_faculty' => $faculty]);
            if ($department != "") {
                $query->andWhere(['_department' => $department]);
            }
            if ($eduYear != "") {
                $query->andWhere(['_education_year' => $eduYear]);
            }
            if ($type != "") {
                $query->andWhere(['type' => $type]);
            }
            return ArrayHelper::map(
                $query->all(),
                'id',
                'name'
            );
        }
        return ArrayHelper::map(
            self::find()->select(['id', 'name', 'active'])->where(['active' => true])->all(),
            'id',
            'name'
        );
    }
}
