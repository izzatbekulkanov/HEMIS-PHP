<?php

namespace common\models\curriculum;

use common\models\archive\EAcademicRecord;
use common\models\curriculum\ESubject;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\ExamFinish;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\SubjectBlock;
use common\models\system\classifier\SubjectType;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\RatingGrade;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "e_curriculum_subject".
 *
 * @property int $id
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_curriculum_subject_block
 * @property string $_semester
 * @property string $_subject_type
 * @property string $_rating_grade
 * @property int $total_acload
 * @property int $credit
 * @property bool|null $reorder
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property ESubject $subject
 * @property RatingGrade $ratingGrade
 * @property Semester $semester
 * @property SubjectBlock $curriculumSubjectBlock
 * @property SubjectType $subjectType
 * @property ECurriculumSubjectDetail[] $subjectDetails
 * @property ECurriculumSubjectExamType[] $subjectExamTypes
 */
class ECurriculumSubject extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_EMPLOYEE = 'employee';

    protected $_translatedAttributes = ['name'];
    public $count_of_weeks;
    public $marking_system;
    public $_education_year;

    public static function tableName()
    {
        return 'e_curriculum_subject';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getSemesterPositions()
    {
        return [
            self::STATUS_ENABLE => 'SF1',
            self::STATUS_DISABLE => 'SF0',
        ];
    }

    public static function getAcceptedOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enabled'),
            self::STATUS_DISABLE => __('Disabled'),
        ];
    }

    public static function getSubjectByCurriculumSemester($curriculum = false, $semester = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                'active' => self::STATUS_ENABLE
            ])
            //    ->orderByTranslationField('position')
            ->all();
    }

    public static function getGraduateSubjects($curriculum = false)
    {
        return ArrayHelper::map(self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_rating_grade' => RatingGrade::RATING_GRADE_GRADUATE,
                'active' => self::STATUS_ENABLE
            ])
            //    ->orderByTranslationField('position')
            ->all(), '_subject', 'subject.name');
    }

    public static function getOtherSubjectByCurriculumSemester($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject_type' => SubjectType::SUBJECT_TYPE_SELECTION,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['not in', '_subject', $subject])
            //->orderByTranslationField('position')
            ->all();
    }

    /**
     * @param false $curriculum
     * @param false $semester
     * @param false $subject
     * @return self
     */
    public static function getByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            //    ->orderByTranslationField('position')
            ->one();
    }

    public static function getByCurriculumSubject($curriculum = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            //    ->orderByTranslationField('position')
            ->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_curriculum', '_subject', '_curriculum_subject_block', '_semester', '_subject_type', '_rating_grade', 'total_acload', 'credit'], 'required'],
            [['_curriculum_subject_block', '_subject_type', '_rating_grade', 'credit', '_exam_finish', '_department'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_employee'], 'safe', 'on' => self::SCENARIO_EMPLOYEE],
            [['_curriculum_subject_block'], 'safe', 'on' => self::SCENARIO_DELETE],
            [['_curriculum', '_subject', 'total_acload', 'credit', 'position', '_exam_finish', '_department', '_employee'], 'default', 'value' => null],
            [['_curriculum', '_subject', 'total_acload', 'position', 'in_group', '_department', '_employee'], 'integer'],
            [['credit'], 'number'],
            [['active', 'reorder', 'at_semester'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_curriculum_subject_block', '_semester', '_subject_type', '_rating_grade', '_education_year'], 'string', 'max' => 64],
            [['_curriculum', '_subject', '_semester'], 'unique', 'targetAttribute' => ['_curriculum', '_subject', '_semester']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_rating_grade'], 'exist', 'skipOnError' => true, 'targetClass' => RatingGrade::className(), 'targetAttribute' => ['_rating_grade' => 'code']],
            [['_semester'], 'exist', 'skipOnError' => true, 'targetClass' => Semester::className(), 'targetAttribute' => ['_semester' => 'code']],
            [['_curriculum_subject_block'], 'exist', 'skipOnError' => true, 'targetClass' => SubjectBlock::className(), 'targetAttribute' => ['_curriculum_subject_block' => 'code']],
            [['_subject_type'], 'exist', 'skipOnError' => true, 'targetClass' => SubjectType::className(), 'targetAttribute' => ['_subject_type' => 'code']],
            [['_exam_finish'], 'exist', 'skipOnError' => true, 'targetClass' => ExamFinish::className(), 'targetAttribute' => ['_exam_finish' => 'code']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
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

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getRatingGrade()
    {
        return $this->hasOne(RatingGrade::className(), ['code' => '_rating_grade']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester', '_curriculum' => '_curriculum']);
    }

    public function getMaxBall()
    {
        return $this->hasMany(ECurriculumSubjectExamType::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester'])->sum('max_ball');
    }

    /**
     * @param $studentId
     * @return EAcademicRecord | null
     */
    public function getStudentSubjectRecord($studentId)
    {
        return $this->hasMany(EAcademicRecord::class, [
            '_curriculum' => '_curriculum',
            '_subject' => '_subject',
            '_semester' => '_semester'])
            ->orderBy('e_academic_record.active DESC')
            ->andFilterWhere(['_student' => $studentId])
            ->limit(1)
            ->one();
    }

    public function getCurriculumSubjectBlock()
    {
        return $this->hasOne(SubjectBlock::className(), ['code' => '_curriculum_subject_block']);
    }

    public function getSubjectDetails()
    {
        return $this->hasMany(ECurriculumSubjectDetail::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester'])
            ->orderBy(['_training_type' => SORT_ASC])
            ->with(['trainingType']);
    }

    public function getSubjectExamTypes()
    {
        return $this->hasMany(ECurriculumSubjectExamType::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester'])
            ->andOnCondition(['>', 'max_ball', 0])
            //->with(['examType'])
            ->leftJoin('h_exam_type', 'h_exam_type.code=_exam_type')
            ->orderBy(['h_exam_type.position' => SORT_ASC]);
    }

    public function getSubjectExamType()
    {
        return $this->hasMany(ECurriculumSubjectExamType::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester'])
            //->with(['examType'])
            ->leftJoin('h_exam_type', 'h_exam_type.code=_exam_type')
            ->orderBy(['h_exam_type.position' => SORT_ASC]);
    }

    public function getSubjectExamTypeOther()
    {
        return $this->hasMany(ECurriculumSubjectExamType::className(), ['_curriculum' => '_curriculum', '_subject' => '_subject', '_semester' => '_semester'])
            ->andOnCondition(['<>', '_exam_type', ExamType::EXAM_TYPE_OVERALL])

            //->with(['examType'])
            ->leftJoin('h_exam_type', 'h_exam_type.code=_exam_type')
            ->orderBy(['h_exam_type.position' => SORT_ASC]);
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;
        foreach ($provider as $item) {
            $total += @$item[@$fieldName];
        }
        return @$total;
    }

    public function getSubjectType()
    {
        return $this->hasOne(SubjectType::className(), ['code' => '_subject_type']);
    }

    public function getExamFinish()
    {
        return $this->hasOne(ExamFinish::className(), ['code' => '_exam_finish']);
    }

    public function getSubjects()
    {
        return $this->hasMany(ESubject::className(), ['_subject' => '_id']);
    }

    public function getRealTrainingTypes()
    {
        return $this->hasMany(ECurriculumSubjectDetail::className(), [
            '_subject' => '_subject',
            '_curriculum' => '_curriculum',
            '_semester' => '_semester',
        ])
            ->andFilterWhere(['not in', '_training_type', [TrainingType::TRAINING_TYPE_INDEPENDENT, TrainingType::TRAINING_TYPE_COURSE_WORK]])
            ->orderBy(['position' => SORT_ASC]);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function search($params)
    {
        $this->load($params);
        //$query = self::find();
        $query = self::find()
            ->joinWith(['semester']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    'e_curriculum_subject._curriculum',
                    '_subject',
                    '_curriculum_subject_block',
                    '_department',
                    '_semester',
                    'code',
                    'e_curriculum_subject.active',
                    'position',
                    '_subject_group',
                    'h_semestr._education_year',
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
        $query->andFilterWhere(['e_curriculum_subject.active' => self::STATUS_ENABLE]);

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_curriculum_subject._curriculum' => $this->_curriculum]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['h_semestr._education_year' => $this->_education_year]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_curriculum_subject_block) {
            $query->andFilterWhere(['_curriculum_subject_block' => $this->_curriculum_subject_block]);
        }
        return $dataProvider;
    }

    public function search_subjects($params, $curriculum = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();
        $defaultOrder = ['_semester' => SORT_ASC, 'position' => SORT_ASC];


        if ($this->search) {
            //$query->orWhereLike('name_uz', $this->search, '_translations');
            //  $query->orWhereLike('name_oz', $this->search, '_translations');
            // $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
        }
        $query->andFilterWhere(['active' => self::STATUS_ENABLE]);
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($curriculum) {
            $query->andFilterWhere(['_curriculum' => $curriculum]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_curriculum_subject_block) {
            $query->andFilterWhere(['_curriculum_subject_block' => $this->_curriculum_subject_block]);
        }
        if ($asProvider) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => $defaultOrder,
                    'attributes' => [
                        '_curriculum',
                        '_subject',
                        '_curriculum_subject_block',
                        '_department',
                        '_semester',
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
        } else {
            $query->addOrderBy($defaultOrder);
        }

        return $query;
    }

    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Subjects'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Semester'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Subject'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Total Acload'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Credit'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Subject Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('At Semester'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Rating Grade'), DataType::TYPE_STRING);

        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                Semester::getByCurriculumSemester($model->_curriculum, $model->_semester)->name,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->subject ? $model->subject->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                (int)$model->total_acload,
                DataType::TYPE_NUMERIC
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                (float)$model->credit,
                DataType::TYPE_NUMERIC
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->subjectType ? $model->subjectType->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->at_semester == ECurriculum::STATUS_ENABLE ? 'Semestr fani' : 'Semestr fani emas',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->ratingGrade ? $model->ratingGrade->name : '',
                DataType::TYPE_STRING
            );

        }

        $name = 'Fanlar-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public function getShortInfo()
    {
        return implode(' / ', [@mb_substr($this->subjectType->name, 0, 1), $this->total_acload, $this->credit]);
    }

    public function getCurriculumItems($department)
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => self::find()
                    ->select(['_curriculum'])
                    ->where(['_department' => $department])
                    ->distinct()
                    ->column()])
                ->all(), 'id', 'name');
    }

    public function getSemesterItems()
    {
        return ArrayHelper::map(
            Semester::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'code' => self::find()
                        ->select(['_semester'])
                        ->distinct()
                        ->column()])
                ->all(), 'code', 'name');
    }

    public function getEducationYearItems()
    {
        //$education_year = self::find()
        return ArrayHelper::map(
            EducationYear::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'code' => self::find()
                        ->joinWith(['semester'])
                        ->select(['h_semestr._education_year'])
                        ->distinct()
                        ->column()])
                ->all(), 'code', 'name');
    }
}
