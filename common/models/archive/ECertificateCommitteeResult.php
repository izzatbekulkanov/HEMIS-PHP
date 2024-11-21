<?php

namespace common\models\archive;

use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\MarkingSystem;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationType;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_certificate_committee_result".
 *
 * @property int $id
 * @property string $name
 * @property string $_specialty
 * @property string $_education_type
 * @property string $_education_year
 * @property \DateTime $order_date
 * @property int $_department
 * @property int $ball
 * @property int $grade
 * @property int $_faculty
 * @property bool|null $active
 *
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EGroup $group
 * @property ESubject $subject
 */
class ECertificateCommitteeResult extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return 'e_certificate_committee_result';
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'order_number' => __('DAK qarori raqami'),
            'order_date' => __('DAK qarori sanasi'),
        ]);
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
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'order_number',
                        'order_date',
                        'grade',
                        'ball',
                        '_student',
                        '_group',
                        '_education_type',
                        '_education_year',
                        '_specialty',
                        '_faculty',
                        '_department',
                        '_subject',
                        '_certificate_committee',
                        '_graduate_work'
                    ],
                    'required',
                    'on' => self::SCENARIO_INSERT
                ],
                [
                    [
                        '_department',
                        '_faculty',
                        '_group',
                        '_student',
                        '_specialty',
                        '_certificate_committee',
                        '_graduate_work'
                    ],
                    'integer'
                ],
                [['active'], 'boolean'],
                //[['_specialty'], 'string', 'max' => 64],
                //[['name'], 'string', 'max' => 256],
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
                [
                    ['_certificate_committee'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ECertificateCommittee::className(),
                    'targetAttribute' => ['_certificate_committee' => 'id']
                ],
                [
                    ['_graduate_work'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EGraduateQualifyingWork::className(),
                    'targetAttribute' => ['_graduate_work' => 'id']
                ],
                [
                    ['_subject'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESubject::className(),
                    'targetAttribute' => ['_subject' => 'id']
                ],
            ]
        );
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getFaculty()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_faculty']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
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

    public function getCertificateCommittee()
    {
        return $this->hasOne(ECertificateCommittee::className(), ['id' => '_certificate_committee']);
    }

    public function getGraduateQualifyingWork()
    {
        return $this->hasOne(EGraduateQualifyingWork::className(), ['id' => '_graduate_work']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
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
        if ($this->_specialty) {
            $query->andFilterWhere(['_specialty' => $this->_specialty]);
        }
        if ($this->_faculty) {
            $query->andFilterWhere(['_faculty' => $this->_faculty]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }

        return $dataProvider;
    }

    public static function getSelectOptions()
    {
        return ArrayHelper::map(
            self::find()->select(['id', 'name', 'active'])->where(['active' => true])->all(),
            'id',
            'name'
        );
    }

    public function beforeSave($insert)
    {
        $curriculumSubject = ECurriculumSubject::getByCurriculumSubject($this->group->_curriculum, $this->_subject);
        $record = EAcademicRecord::find()
            ->where(
                [
                    '_curriculum' => $this->group->_curriculum,
                    '_subject' => $this->_subject,
                    '_education_year' => $this->_education_year,
                    '_semester' => $curriculumSubject->_semester,
                    '_student' => $this->_student
                ]
            )
            ->one();
        if ($record === null) {
            $record = new EAcademicRecord();
            $record->subject_name = $curriculumSubject->subject->name;
            $record->total_acload = $curriculumSubject->total_acload;
            $record->credit = $curriculumSubject->credit;
            $record->_curriculum = $this->group->_curriculum;
            $record->_education_year = $this->_education_year;
            $record->_student = $this->_student;
            $record->_subject = $this->_subject;
            $record->_semester = $curriculumSubject->_semester;
            $record->_employee = $curriculumSubject->_employee;
            $record->curriculum_name = $this->group->curriculum->name;
            $record->education_year_name = $this->educationYear->name;
            $record->semester_name = $curriculumSubject->semester->name;
            $record->student_name = $this->student->fullName;
            $record->student_id_number = $this->student->student_id_number;
            $record->employee_name = $curriculumSubject->employee->fullName ?? '';
        }
        $record->grade = $this->grade;
        $record->total_point = $this->ball;
//        $record->validate();
//        print_r($record->getAttributes());die;
        $record->save();
        return parent::beforeSave($insert);
    }
}
