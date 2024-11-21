<?php

namespace common\models\curriculum;

use common\models\attendance\EAttendanceControl;
use common\models\employee\EEmployee;
use common\models\infrastructure\EAuditorium;
use common\models\student\EGroup;
use common\models\system\_BaseModel;
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
 * This is the model class for table "e_subject_schedule".
 *
 * @property int $id
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_education_year
 * @property string $_semester
 * @property int $_group
 * @property string $_training_type
 * @property int $_auditorium
 * @property int $_subject_topic
 * @property int $_week
 * @property int $_employee
 * @property string $_lesson_pair
 * @property DateTime $lesson_date
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EAuditorium $auditorium
 * @property ECurriculum $curriculum
 * @property ECurriculumSubjectTopic $subjectTopic
 * @property ECurriculumWeek $week
 * @property EEmployee $employee
 * @property Semester $semester
 * @property EGroup $group
 * @property ESubject $subject
 * @property EducationYear $educationYear
 * @property TrainingType $trainingType
 * @property LessonPair $lessonPair
 * @property EAttendanceControl attendanceControl
 * @property ESubjectResource[] subjectResources
 */
class ESubjectSchedule extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SCENARIO_TEACHER = 'teacher';

    protected $_translatedAttributes = ['name'];
    public $groups;
    public $count_lesson;
    public $_education_lang;
    public $_department;


    public static function tableName()
    {
        return 'e_subject_schedule';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getWeeksByCurriculumGroup($week = false, $curriculum = false, $semester = false, $group = false)
    {
        return self::find()
            ->where([
                '_week' => $week,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            //    ->orderByTranslationField('position')
            ->count();
    }

    public static function getScheduleByCurriculumGroup($week = false, $curriculum = false, $semester = false, $group = false)
    {
        return self::find()
            ->where([
                '_week' => $week,
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            ->all();
    }

    public static function getTeacherByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            // ->groupBy(['_employee'])
            ->all();
    }

    public static function getTeacherByCurriculumSemesterSubjectTrainingGroup($curriculum = false, $semester = false, $subject = false, $training_type = false, $group = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_training_type' => $training_type,
                '_group' => $group,
                'active' => self::STATUS_ENABLE
            ])
            ->one();
    }
    public static function getTopicByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false, $training_type = false, $employee = false, $group = false, $topic = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_training_type' => $training_type,
                '_employee' => $employee,
                '_group' => $group,
                '_subject_topic' => $topic,
                //'active' => self::STATUS_ENABLE
            ])
            ->one();
    }

    public static function getTeachersByCurriculumSemesterSubjectTraining($curriculum = false, $semester = false, $subject = false, $training_type)
    {
        if (is_array($training_type)) {
            return self::find()
                ->where([
                    '_curriculum' => $curriculum,
                    '_semester' => $semester,
                    '_subject' => $subject,
                    'active' => self::STATUS_ENABLE
                ])
                ->andWhere(['in', '_training_type', $training_type])
                ->all();
        } else {
            return self::find()
                ->where([
                    '_curriculum' => $curriculum,
                    '_semester' => $semester,
                    '_subject' => $subject,
                    '_training_type' => $training_type,
                    'active' => self::STATUS_ENABLE
                ])
                ->all();
        }
    }

    public static function getGroupByCurriculumSemesterSubjectTrainingLanguage($curriculum = false, $semester = false, $subject = false, $training_type = false, $education_lang = false, $employee = false, $asLabel = false)
    {
        $groups = self::find()
            ->joinWith(['group'])
            ->select('_group')
            ->where([
                'e_subject_schedule._curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_training_type' => $training_type,
                '_education_lang' => $education_lang,
                '_employee' => $employee,
                'e_subject_schedule.active' => self::STATUS_ENABLE
            ])
            ->groupBy(['_group'])
            ->all();

        if ($asLabel) {
            return implode(', ', ArrayHelper::getColumn($groups, function ($group) {
                return $group->group->name;
            }));
        }
        return $groups;
    }

    public static function getAttendanceJournalCount()
    {
        return self::find()
            ->select('_education_year,_subject,_group,_semester,_training_type')
            ->where([
                '_education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                'active' => self::STATUS_ENABLE
            ])
            ->groupBy(['_education_year', '_subject', '_group', '_semester', '_training_type'])
            ->count();
    }

    public static function getAttendanceLessonCount()
    {
        return self::find()
            ->where([
                '_education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                'active' => self::STATUS_ENABLE
            ])
            ->count();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_subject', 'groups', '_training_type', '_auditorium', '_employee', '_lesson_pair', 'lesson_date'], 'required', 'on' => self::SCENARIO_INSERT],
            [['_subject_topic'], 'required', 'on' => self::SCENARIO_TEACHER],
//            [['_curriculum', '_subject', '_education_year', '_semester', '_group', '_training_type', '_auditorium', '_subject_topic', '_week', '_employee', '_lesson_pair', 'lesson_date'], 'required'],
            [['_curriculum', '_subject', '_group', '_auditorium', '_subject_topic', '_week', '_employee', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', '_group', '_auditorium', '_subject_topic', '_week', '_employee', 'position', '_department'], 'integer'],
            [['lesson_date', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_education_year', '_semester', '_training_type', '_lesson_pair'], 'string', 'max' => 64],
            [['additional'], 'string', 'max' => 512],
            // [['lesson_date', '_auditorium', '_lesson_pair', '_group'], 'unique', 'targetAttribute' => ['lesson_date', '_auditorium', '_lesson_pair', '_group'], 'message'=>__('The combination has already been taken.')],
            [['lesson_date', '_employee', '_lesson_pair', '_group'], 'unique', 'targetAttribute' => ['lesson_date', '_employee', '_lesson_pair', '_group'], 'message' => __('The combination has already been taken.')],
            //[['lesson_date', '_employee', '_lesson_pair', '_training_type'], 'unique', 'targetAttribute' => ['lesson_date', '_employee', '_lesson_pair', '_training_type']],

            [['_auditorium'], 'exist', 'skipOnError' => true, 'targetClass' => EAuditorium::className(), 'targetAttribute' => ['_auditorium' => 'code']],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            //[['_subject_topic'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumSubjectTopic::className(), 'targetAttribute' => ['_subject_topic' => 'id']],
            [['_week'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumWeek::className(), 'targetAttribute' => ['_week' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_group'], 'exist', 'skipOnError' => true, 'targetClass' => EGroup::className(), 'targetAttribute' => ['_group' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
        ]);
    }

    public function getAuditorium()
    {
        return $this->hasOne(EAuditorium::className(), ['code' => '_auditorium']);
    }

    public function getAttendanceControl()
    {
        return $this->hasOne(EAttendanceControl::className(), ['_subject_schedule' => 'id']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSubjectTopic()
    {
        return $this->hasOne(ECurriculumSubjectTopic::className(), ['id' => '_subject_topic']);
    }

    public function getSubjectResources()
    {
        return $this->hasMany(ESubjectResource::className(), [
            '_curriculum' => '_curriculum',
            '_training_type' => '_training_type',
            '_subject' => '_subject',
            '_employee' => '_employee'
        ])->filterWhere([
            'resource_type' => ESubjectResource::RESOURCE_TYPE_RESOURCE,
        ]);
    }

    public function getSubjectTasks()
    {
        return $this->hasMany(ESubjectTask::className(), [
            '_curriculum' => '_curriculum',
            '_training_type' => '_training_type',
            '_subject' => '_subject',
            '_employee' => '_employee'
        ]);
    }

    public function getWeek()
    {
        return $this->hasOne(ECurriculumWeek::className(), ['id' => '_week']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function getLessonPair()
    {
        return $this->hasOne(LessonPair::className(), ['code' => '_lesson_pair', '_education_year' => '_education_year']);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
            '_department' => __('Faculty'),
        ]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['curriculum']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    'e_subject_schedule._education_year',
                    '_semester',
                    'lesson_date',
                    '_subject',
                    '_group',
                    '_training_type',
                    '_lesson_pair',
                    '_auditorium',
                    '_employee',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        /*if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }*/


        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_schedule._education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_training_type) {
            $query->andFilterWhere(['_training_type' => $this->_training_type]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }
        return $dataProvider;
    }

    public function search_info($params, $asProvider = true)
    {
        $this->load($params);
        $query = self::find();
        $query->joinWith(['curriculum']);

        $defaultOrder = ['_group' => SORT_ASC];

        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_schedule._education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_training_type) {
            $query->andFilterWhere(['_training_type' => $this->_training_type]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
                            '_curriculum',
                            'e_subject_schedule._education_year',
                            '_semester',
                            'lesson_date',
                            '_subject',
                            '_group',
                            '_training_type',
                            '_lesson_pair',
                            '_auditorium',
                            '_employee',
                            'position',
                            'updated_at',
                            'created_at',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]
            );
        }
        else {
            $query->addOrderBy($defaultOrder);
        }
        return $query;
    }

    public function search_group($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['group']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    'e_subject_schedule._curriculum',
                    'e_subject_schedule._education_year',
                    'e_group._education_lang',
                    '_semester',
                    'lesson_date',
                    '_subject',
                    '_group',
                    '_training_type',
                    '_lesson_pair',
                    '_auditorium',
                    '_employee',

                    'position',
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


        if ($this->_curriculum) {
            $query->andFilterWhere(['e_subject_schedule._curriculum' => $this->_curriculum]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_subject_schedule._education_year' => $this->_education_year]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_training_type) {
            $query->andFilterWhere(['_training_type' => $this->_training_type]);
        }
        if ($this->_employee) {
            $query->andFilterWhere(['_employee' => $this->_employee]);
        }
        return $dataProvider;
    }

    private function getSelectQueryFilters($col)
    {
        $query = self::find()->select([$col])
            ->andFilterWhere(['active' => true])
            ->distinct();

        foreach (['_education_year', '_semester', '_group', '_subject'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    private function getSelectEmployeeQueryFilters($col)
    {
        $query = self::find()->select([$col])
            ->andFilterWhere(['active' => true])
            ->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee])
            ->distinct();

        foreach (['_education_year', '_semester', '_group', '_subject'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    public function getEducationYearItems()
    {
        return ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_year')])
                ->all(), 'code', 'name');
    }

    public function getSemesterItems()
    {
        return ArrayHelper::map(
            \common\models\system\classifier\Semester::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_semester')])
                ->all(), 'code', 'name');
    }

    public function getGroupItems()
    {
        return ArrayHelper::map(
            EGroup::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_group')])
                ->all(), 'id', 'name');
    }

    public function getSubjectItems()
    {
        return ArrayHelper::map(
            ECurriculumSubject::find()
                //->orderBy(['subject.name' => SORT_ASC])
                ->where(['active' => true, '_subject' => $this->getSelectQueryFilters('_subject'), '_semester' => $this->getSelectQueryFilters('_semester')])
                ->all(), '_subject', 'subject.name');
    }

    public function getEmployeeEducationYearItems()
    {
        return ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectEmployeeQueryFilters('_education_year')])
                ->all(), 'code', 'name');
    }

    public function getEmployeeSemesterItems()
    {
        return ArrayHelper::map(
            \common\models\system\classifier\Semester::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectEmployeeQueryFilters('_semester')])
                ->all(), 'code', 'name');
    }

    public function getEmployeeGroupItems()
    {
        return ArrayHelper::map(
            EGroup::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => $this->getSelectEmployeeQueryFilters('_group')])
                ->all(), 'id', 'name');
    }

    public function getEmployeeSubjectItems()
    {
        return ArrayHelper::map(
            ECurriculumSubject::find()
                //->orderBy(['subject.name' => SORT_ASC])
                ->where(['active' => true, '_subject' => $this->getSelectEmployeeQueryFilters('_subject'), '_semester' => $this->getSelectQueryFilters('_semester')])
                ->all(), '_subject', 'subject.name');
    }

    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Schedule List'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('T/R'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Curriculum Curriculum'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Year'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Semester'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Count of Weeks'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Weeks [lessons]'), DataType::TYPE_STRING);

        $sheet->getStyle("A$row:G$row")->getFont()->setBold(true);
        foreach ($query->all() as $i => $model) {
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
                $model->curriculum ? $model->curriculum->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationYear ? $model->educationYear->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                Semester::getByCurriculumSemester($model->_curriculum, $model->_semester) ? Semester::getByCurriculumSemester($model->_curriculum, $model->_semester)->name : '',
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->group ? $model->group->name : '',
                DataType::TYPE_STRING
            );
            $weekCount = ECurriculumWeek::getWeekCountByCurriculum($model->_curriculum, $model->_semester);
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $weekCount,
                DataType::TYPE_STRING
            );
            $result = "";
            $curriculum_weeks = ECurriculumWeek::getWeekByCurriculum($model->_curriculum, $model->_semester);
            foreach ($curriculum_weeks as $key=>$item){
                $lessons = ESubjectSchedule::getWeeksByCurriculumGroup($item->id, $model->_curriculum, $model->_semester, $model->_group);
                $result .= '№'. ($key+1). ' ['.$lessons.'] '. '  ';
            }
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $result,
                DataType::TYPE_STRING
            );

        }
        foreach (range('A', 'G') as $columnDimension) {
            $sheet->getColumnDimension($columnDimension)->setAutoSize(true);
            $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        }

        $sheet->calculateColumnWidths();

        $name = 'Schedule_lists-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }
}
