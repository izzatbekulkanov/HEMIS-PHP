<?php

namespace common\models\archive;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\student\EStudent;
use Yii;
use common\models\system\_BaseModel;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_transcript_subject".
 *
 * @property int $id
 * @property int $_academic_information
 * @property int $_student
 * @property int $_curriculum
 * @property string $_education_year
 * @property string $_semester
 * @property int $_subject
 * @property string $curriculum_name
 * @property string $education_year_name
 * @property string $semester_name
 * @property string $student_name
 * @property string $subject_name
 * @property int|null $total_acload
 * @property float|null $credit
 * @property float|null $total_point
 * @property float|null $grade
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EAcademicInformation $academicInformation
 * @property ECurriculum $curriculum
 * @property EEducationYear $educationYear
 * @property EStudent $student
 * @property ESubject $subject
 */
class ETranscriptSubject extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'insert';
    public $subjectIds = [];
    public static function tableName()
    {
        return 'e_transcript_subject';
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
            //[['_academic_information', '_student', '_curriculum', '_education_year', '_semester', '_subject', 'curriculum_name', 'education_year_name', 'semester_name', 'student_name', 'subject_name', 'updated_at', 'created_at'], 'required'],
            [['curriculum_name', 'education_year_name', 'semester_name', 'student_name', 'subject_name', 'updated_at', 'created_at'], 'required'],
            [['_academic_information', '_student', '_curriculum', '_subject', 'total_acload', 'position'], 'default', 'value' => null],
            [['_academic_information', '_student', '_curriculum', '_subject', 'total_acload', 'position'], 'integer'],
            [['credit', 'total_point', 'grade'], 'number'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_education_year', '_semester'], 'string', 'max' => 64],
            [['curriculum_name'], 'string', 'max' => 256],
            [['education_year_name', 'semester_name', 'student_name', 'subject_name'], 'string', 'max' => 255],
            [['_academic_information'], 'exist', 'skipOnError' => true, 'targetClass' => EAcademicInformation::className(), 'targetAttribute' => ['_academic_information' => 'id']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
        ]);
    }

    public function getAcademicInformation()
    {
        return $this->hasOne(EAcademicInformation::className(), ['id' => '_academic_information']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

}
