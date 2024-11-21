<?php

namespace common\models\performance;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\classifier\ExamType;

use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\StudentStatus;
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
use Imagine\Image\ManipulatorInterface;

/**
 * This is the model class for table "e_performance".
 *
 * @property int $id
 * @property int $_exam_schedule
 * @property int $_student
 * @property string $_education_year
 * @property string $_semester
 * @property int $_subject
 * @property int $_employee
 * @property string $_exam_type
 * @property string $exam_name
 * @property string $exam_date
 * @property float $grade
 * @property float|null $regrade
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEmployee $employee
 * @property EStudent $student
 * @property ESubject $subject
 * @property Semester $semester
 * @property ESubjectExamSchedule $examSchedule
 * @property HEducationYear $educationYear
 * @property HExamType $examType
 */
class EPerformance extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SEND_RECORD_PASSIVE = 0;
    const SEND_RECORD_ACTIVE = 1;

    protected $_translatedAttributes = ['name'];
    public $_curriculum;
    public static function tableName()
    {
        return 'e_performance';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getMarksByCurriculumSemester($education_year = false, $semester = false, $subject = false, $students, $exam_types, $group= false) {
        $ratings = EPerformance::find();
        //$ratings->select('e_performance._student, e_student.second_name, e_student.first_name, e_student.third_name, e_performance.grade');
        $ratings->leftJoin('e_student', 'e_student.id=_student');
        $ratings->leftJoin('e_student_meta', 'e_student_meta._student=e_performance._student AND e_student_meta._education_year=e_performance._education_year AND e_student_meta._semestr=e_performance._semester');
        $ratings->where([
            'e_performance._education_year' => $education_year,
            '_semester' => $semester,
            '_subject' => $subject,
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            'e_student_meta._semestr' => $semester,
            'e_student_meta._education_year' => $education_year,
            'e_student_meta._group' => $group,

        ]);
        $ratings->andFilterWhere(['in', 'e_performance._student', $students]);
        if(is_array($exam_types)){
            $ratings->andFilterWhere(['in', '_exam_type', $exam_types]);
        }
        $ratings->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC ]);
        //$ratings->distinct();
        $ratings = $ratings->all();
        return $ratings;
    }

    public static function getMarksByCurriculumSemesterFinalExam($education_year = false, $semester = false, $subject = false, $students, $exam_types, $final_exam_types, $group= false) {
        $ratings = EPerformance::find();
        $ratings->leftJoin('e_student', 'e_student.id=_student');
        $ratings->leftJoin('e_student_meta', 'e_student_meta._student=e_performance._student AND e_student_meta._education_year=e_performance._education_year AND e_student_meta._semestr=e_performance._semester');
        $ratings->where([
            'e_performance._education_year' => $education_year,
            '_semester' => $semester,
            '_subject' => $subject,
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            'e_student_meta._semestr' => $semester,
            'e_student_meta._education_year' => $education_year,
            'e_student_meta._group' => $group,

        ]);
        $ratings->andFilterWhere(['in', 'e_performance._student', $students]);

        $ratings->andFilterWhere(['in', '_exam_type', $exam_types]);
        $ratings->andFilterWhere(['in','_final_exam_type', $final_exam_types]);
        $ratings->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC ]);
        //$ratings->distinct();
        $ratings = $ratings->all();
        return $ratings;
    }

    public static function getPassedStudentsByCurriculumSemester($education_year = false, $semester = false, $subject = false, $students, $final_exam_types) {
        $ratings_passed = EPerformance::find();
        $ratings_passed->where([
            '_education_year' => $education_year,
            '_semester' => $semester,
            '_subject' => $subject,
            '_exam_type' => ExamType::EXAM_TYPE_OVERALL,
            'passed_status' => 1,
        ]);
        $ratings_passed->andFilterWhere(['in','_final_exam_type', $final_exam_types]);
        $ratings_passed->andFilterWhere(['in', '_student', $students]);
        $ratings_passed = $ratings_passed->all();
        return $ratings_passed;
    }

    public static function getPassedStudentByCurriculumSemester($education_year = false, $semester = false, $subject = false, $student = false) {
        $ratings_passed = EPerformance::find();
        $ratings_passed->where([
            '_education_year' => $education_year,
            '_semester' => $semester,
            '_subject' => $subject,
            '_student' => $student,
            '_exam_type' => ExamType::EXAM_TYPE_OVERALL,
            'passed_status' => 1,
        ]);
        $ratings_passed->andFilterWhere(['>', 'grade', 0]);
        $ratings_passed = $ratings_passed->one();
        return $ratings_passed;
    }

    public static function getMarkByCurriculumSemesterSubject($student = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_student' => $student,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            // ->groupBy(['_employee'])
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_exam_schedule', '_student', '_education_year', '_semester', '_subject', '_employee', '_exam_type', 'exam_name', 'exam_date', 'grade', 'updated_at', 'created_at'], 'required'],
            [['_exam_schedule', '_student', '_subject', '_employee'], 'default', 'value' => null],
            [['_exam_schedule', '_student', '_subject', '_employee', 'passed_status', '_curriculum'], 'integer'],
            [['exam_date', 'updated_at', 'created_at'], 'safe'],
            [['grade', 'regrade'], 'number'],
            [['active'], 'boolean'],
            [['_education_year', '_semester', '_exam_type', 'exam_name', '_final_exam_type'], 'string', 'max' => 64],
            [['_student', '_education_year', '_semester', '_subject', '_exam_type', '_final_exam_type'], 'unique', 'targetAttribute' => ['_student', '_education_year', '_semester', '_subject', '_exam_type', '_final_exam_type']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_exam_schedule'], 'exist', 'skipOnError' => true, 'targetClass' => ESubjectExamSchedule::className(), 'targetAttribute' => ['_exam_schedule' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => ExamType::className(), 'targetAttribute' => ['_exam_type' => 'code']],
        ]);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getExamSchedule()
    {
        return $this->hasOne(ESubjectExamSchedule::className(), ['id' => '_exam_schedule']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getExamType()
    {
        return $this->hasOne(ExamType::className(), ['code' => '_exam_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['_student' => '_student', '_education_year'=>'_education_year', '_semestr'=>'_semester']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_education_year',
                    '_semester',
                    'exam_date',
                    '_subject',
                    '_group',
                    '_exam_type',
                    '_lesson_pair',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }

        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        return $dataProvider;
    }

    public function search_student($params)
    {
        $this->load($params);
        if ($this->_education_year == null) {
            $this->_curriculum = null;
        }
        if ($this->_curriculum == null) {
            $this->_semester = null;
        }
        if ($this->_semester == null) {
            $this->_subject = null;
        }
        $query = self::find();
        $query->joinWith (['studentMeta']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_education_year',
                    '_semester',
                    'exam_date',
                    '_subject',
                    '_group',
                    '_exam_type',
                    'e_student_meta._curriculum',
                    '_lesson_pair',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_education_year) {
            $query->andFilterWhere(['e_performance._education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['e_performance._semester' => $this->_semester]);
        }
        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        return $dataProvider;
    }

    public static function generateDownloadFile($students, $list_subjects, $searchModel, $balls, $final_exam)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Summary Ball'));

        $row = 1;
        $col = 0;
        @$group =EGroup::findOne($searchModel->_group);
        $sheet->setCellValueExplicitByColumnAndRow($col+1, $row, $searchModel->getAttributeLabel('_curriculum'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+2, $row++, @ECurriculum::findOne(@$searchModel->_curriculum)->name, DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+1, $row, $searchModel->getAttributeLabel('_education_year'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+2, $row++, @EducationYear::findOne($searchModel->_education_year)->name, DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+1, $row, $searchModel->getAttributeLabel('_semester'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+2, $row++, @Semester::getByCurriculumSemester($searchModel->_curriculum, $searchModel->_semester)->name, DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+1, $row, $searchModel->getAttributeLabel('_group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col+2, $row++, @$group->name, DataType::TYPE_STRING);



        $col = 1;
        $row++;
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('№'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Fullname of Student'), DataType::TYPE_STRING);
        $sheet->getStyle("A$row:G$row")->getFont()->setBold(true);

        foreach($list_subjects as $key=>$item){
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item, DataType::TYPE_STRING);
        }
        $i=1;
        foreach ($students as $item) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $i++,
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                @$item->student->fullName,
                DataType::TYPE_STRING
            );
            foreach($list_subjects as $key=>$subject){
                //if(isset($balls[$key][$item->_student])) {
                    $sheet->setCellValueExplicitByColumnAndRow(
                        $col++,
                        $row,
                        isset($balls[$key][$item->_student]) ? $balls[$key][$item->_student] .' ['.($final_exam[$key][$item->_student]-10).']' : '',
                        DataType::TYPE_STRING
                    );
                //}
            }
        }

        $name =  Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public static function generateAcademicRecordFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__(' Debtor List'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('T/R'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Year'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Semester'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Subject'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employee'), DataType::TYPE_STRING);

        $sheet->getStyle("A$row:G$row")->getFont()->setBold(true);
        foreach ($query as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $row-1,
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model['name'],
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model['group'],
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model['education_year'],
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model['semester'],
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model['subject'],
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model['teacher'],
                DataType::TYPE_STRING
            );

        }
        foreach (range('A', 'G') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
        }

        $sheet->calculateColumnWidths();

        $name = 'Debtor_lists-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public function getEducationYearItems()
    {
        $query = EducationYear::find()
            ->orderBy(['name' => SORT_ASC])
            ->where(['active' => true, 'code' => self::find()
                ->select(['_education_year'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'code', 'name');
    }

    public function getCurriculumItems()
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'id' => self::find()
                        ->joinWith(['studentMeta'])
                        ->select(['e_student_meta._curriculum'])
                        ->distinct()
                        ->column()])
                ->all(), 'id', 'name');
    }

    public function getSemesterItems($education_year = false, $curriculum= false)
    {
        $query = Semester::find()
            ->orderBy(['name' => SORT_ASC])
            ->where([
                'active' => true,
                '_curriculum' => $curriculum,
                '_education_year' => $education_year,
                'code' => self::find()
                ->select(['_semester'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'code', 'name');

        /*$query = Semester::find()
            ->orderBy(['name' => SORT_ASC])
            ->where(['active' => true, 'code' => self::find()
                ->select(['_semester'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'code', 'name');*/
    }

    public function getSubjectItems($curriculum = false, $semester= false)
    {
        $query = ECurriculumSubject::find()
            //->orderBy(['subject.name' => SORT_ASC])
            ->where([
                'active' => true,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                /*'id' => self::find()
                ->select(['_subject'])
                ->distinct()
                ->column()*/]);

        return ArrayHelper::map($query->all(), '_subject', 'subject.name');
        /*$query = ESubject::find()
            ->orderBy(['name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_subject'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');*/
    }
}
