<?php

namespace common\models\performance;

use common\components\db\PgQuery;
use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\Semester;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use DateTime;
use Mpdf\Tag\P;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_ptt".
 *
 * @property int $id
 * @property string $number
 * @property DateTime $date
 * @property int $_student
 * @property string $_education_type
 * @property string $_education_form
 * @property string $_education_year
 * @property string $_semester
 * @property string $_specialty
 * @property string[] $data
 * @property integer $_department
 * @property integer $_curriculum
 * @property integer $_group
 * @property integer $_decree
 * @property integer $subjects_count
 * @property integer $graded_count
 * @property EStudent $student
 * @property EducationForm $educationForm
 * @property EducationYear $educationYear
 * @property EducationType $educationType
 * @property EDepartment $department
 * @property ECurriculum $curriculum
 * @property Semester $semester
 * @property EGroup $group
 * @property ESpecialty $specialty
 * @property EDecree $decree
 * @property EStudentPttSubject[] $studentPttSubjects
 * @property MarkingSystem $markingSystem
 */
class EStudentPtt extends _BaseModel
{
    public static function tableName()
    {
        return 'e_student_ptt';
    }

    public $subjectIds = [];
    public $ar = [];

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_semester',
                        '_specialty',
                        '_student',
                        '_department',
                        '_curriculum',
                        '_group',
                        '_decree',
                        'number',
                        'date',
                    ],
                    'required',
                ],
                [
                    [
                        'subjectIds',
                        'ar'
                    ],
                    'safe'
                ],
                [
                    [
                        '_semester'
                    ],
                    'unique',
                    'filter' => function (Query $query) {
                        return $query->andFilterWhere(['_student' => $this->_student]);
                    },
                    'message' => __('Ushbu talaba uchun tanlangan semestrga shaxsiy jadval yaratilgan.')
                ],

                ['number', 'unique', 'message' => __('{value} raqamli jadval avval yaratilgan')],
                ['subjectIds', 'validateSubjectIds', 'message' => __('Shaxsiy jadvalga fanlar biriktirilishi kerak')],
            ]
        );
    }

    public function validateSubjectIds($attribute, $options)
    {
        if ($this->isNewRecord) {
            if (count($this->subjectIds) == 0) {
                $this->addError($attribute, $options['message']);
            }
        }
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'number' => __('Ptt Number'),
                'subjects_count' => __('Subject'),
                '_curriculum' => __('Curriculum Curriculum'),
                '_department' => __('Structure Faculty'),
            ]
        );
    }


    public function getDecree()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree']);
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
    }

    public function getStudentPttSubjects()
    {
        return $this->hasMany(EStudentPttSubject::className(), ['_student_ptt' => 'id'])
            ->with(['curriculumSubject'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['id' => '_semester']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getMarkingSystem()
    {
        return $this->hasOne(MarkingSystem::className(), ['code' => '_marking_system'])
            ->viaTable('e_curriculum', ['id' => '_curriculum']);
    }

    public function afterDelete()
    {
        EDecreeStudent::deleteAll([
            '_student' => $this->_student,
            '_decree' => $this->_decree,
        ]);
        parent::afterDelete();
    }

    public function beforeDelete()
    {
        if (!$this->canBeDeleted()) {
            throw new NotSupportedException(__('Ushbu shaxsiy grafikni o\'chirish mumkin emas.'));
        }

        return parent::beforeDelete();
    }

    public function canBeUpdated()
    {
        return $this->student->meta->studentStatus->isStudyingStatus();
    }

    public function beforeSave($insert)
    {
        if (!$this->canBeUpdated()) {
            throw new NotSupportedException(__('"{status}" holatidagi talabaning shaxsiy grafik malumotlarini o\'zgartirish mumkin emas!', ['status' => $this->student->meta->getStatusLabel()]));
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        /**
         * @var $subject EStudentPttCurriculumSubject
         * @var $user Admin
         */
        $user = \Yii::$app->user->identity;
        $graded = 0;

        if (count($this->subjectIds)) {
            $subjects = EStudentPttCurriculumSubject::find()
                ->with(['subject', 'semester'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    'active' => true,
                    'id' => $this->subjectIds
                ])
                ->orderBy([
                    '_semester' => SORT_ASC,
                    'position' => SORT_ASC,
                ])
                ->all();

            $data = [];
            foreach ($subjects as $subject) {
                $data[] = [
                    '_student_ptt' => $this->id,
                    '_curriculum_subject' => $subject->id,
                ];
            }

            if (count($data))
                \Yii::$app->db
                    ->createCommand()
                    ->batchInsert(EStudentPttSubject::tableName(), array_keys($data[0]), $data)
                    ->execute();

        } else {
            $five = GradeType::getGradeByCode(
                $this->curriculum->_marking_system,
                GradeType::GRADE_TYPE_FIVE
            );
            $four = GradeType::getGradeByCode(
                $this->curriculum->_marking_system,
                GradeType::GRADE_TYPE_FOUR
            );
            $three = GradeType::getGradeByCode(
                $this->curriculum->_marking_system,
                GradeType::GRADE_TYPE_THREE
            );

            $ar = $this->ar;
            if (isset($ar['total_point'])) {
                foreach ($this->studentPttSubjects as $subject) {
                    if (isset($ar['total_point'][$subject->id]) && $ar['total_point'][$subject->id]) {
                        $graded++;
                        $totalPoint = $ar['total_point'][$subject->id];
                        $grade = 0;

                        if ($this->curriculum->markingSystem->isFiveMarkSystem()) {
                            $grade = $totalPoint;
                        } else {
                            if ($totalPoint >= $five->min_border) {
                                $grade = $five->name;
                            } elseif ($totalPoint >= $four->min_border) {
                                $grade = $four->name;
                            } elseif ($totalPoint >= $three->min_border) {
                                $grade = $three->name;
                            }
                        }

                        $subject->updateAttributes([
                            'grade' => floatval($grade),
                            'total_point' => floatval($totalPoint),
                        ]);

                        if ($subject->academicRecord) {
                            $subject->academicRecord->updateAttributes([
                                    'grade' => floatval($grade),
                                    'total_point' => floatval($totalPoint),
                                ]
                            );
                        } else {
                            $record = new EAcademicRecord([
                                '_student' => $this->_student,
                                '_curriculum' => $this->_curriculum,
                                '_education_year' => $this->_education_year,
                                'curriculum_name' => $this->curriculum->name,
                                'education_year_name' => $this->educationYear->name,
                                'student_name' => $this->student->getFullName(),
                                'student_id_number' => $this->student->student_id_number,
                                '_subject' => $subject->curriculumSubject->subject->id,
                                '_semester' => $subject->curriculumSubject->semester->code,
                                'semester_name' => $subject->curriculumSubject->semester->name,
                                'subject_name' => $subject->curriculumSubject->subject->name,
                                'total_acload' => $subject->curriculumSubject->total_acload,
                                'credit' => $subject->curriculumSubject->credit,
                                'grade' => floatval($grade),
                                'total_point' => floatval($totalPoint),
                                'active' => true,
                                '_employee' => $user->employee ? $user->employee->id : null,
                                'employee_name' => $user->employee ? $user->employee->getFullName() : null,
                            ]);
                            if ($record->save()) {
                            } else {
                                throw new IntegrityException($record->getOneError());
                            }
                        }
                    }
                }
            }
        }

        $inserts = [];
        $date = (new DateTime('now'))->format('Y-m-d H:i:s');

        foreach ($this->studentPttSubjects as $subject) {
            $key = [
                '_student' => $this->_student,
                '_subject' => $subject->curriculumSubject->_subject,
                '_curriculum' => $subject->curriculumSubject->_curriculum,
                '_education_year' => $subject->curriculumSubject->semester->_education_year,
                '_semester' => $subject->curriculumSubject->_semester,
            ];
            $studentSubject = EStudentSubject::findOne($key);
            if ($studentSubject == null) {
                $key = array_merge($key, [
                    '_group' => $subject->studentPtt->student->meta->_group,
                    'position' => 0,
                    'active' => true,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
                $inserts[] = $key;
            } else {
                if (!$studentSubject->active)
                    $studentSubject->updateAttributes(['active' => true]);
            }
        }

        if (count($inserts)) {
            \Yii::$app->db
                ->createCommand()
                ->batchInsert(EStudentSubject::tableName(), array_keys($inserts[0]), $inserts)
                ->execute();
        }

        $this->updateAttributes([
            'graded_count' => $graded,
            'subjects_count' => count($this->studentPttSubjects),
        ]);

        $this->decree->registerStudent($this->student, \Yii::$app->user->identity);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchContingent($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        $query->joinWith(
            [
                'curriculum',
                'specialty',
                'group',
                'student',
                'department',
                'educationYear',
                'educationType',
                'educationForm'
            ]
        );


        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }

        if ($department) {
            $this->_department = $department;
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_student_ptt._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_ptt._education_type' => $this->_education_type]);
        }

        if ($this->_specialty) {
            $query->andFilterWhere(['e_student_ptt._specialty' => $this->_specialty]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_ptt._education_form' => $this->_education_form]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_student_ptt._group' => $this->_group]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_ptt._curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'date' => SORT_DESC,
                    ],
                    'attributes' => [
                        '_department',
                        '_curriculum',
                        'e_student.second_name',
                        'e_student.first_name',
                        'e_student.third_name',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        'debt_subjects',
                        '_group',
                        '_specialty',
                        'created_at',
                        'number',
                        'date',
                        'subjects_count',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }

    public function getDepartmentItems()
    {
        return ArrayHelper::map(
            EDepartment::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => self::find()
                    ->select(['_department'])
                    ->distinct()
                    ->column()])
                ->all(), 'id', 'name');
    }


    public function getEducationTypeItems()
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_type'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getEducationFormItems()
    {
        return ArrayHelper::map(
            EducationForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_form'])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getCurriculumItems()
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where([
                    'active' => true,
                    'id' => self::find()
                        ->select(['_curriculum'])
                        ->distinct()
                        ->column()])
                ->all(), 'id', 'name');
    }


    public function getGroupItems()
    {
        $query = EGroup::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_group'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function getSpecialtyItems()
    {
        $query = ESpecialty::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => self::find()
                ->select(['_specialty'])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }


    public function getSemestersByEducationYear()
    {
        $data = [];

        foreach ($this->curriculum->semesters as $semester) {
            if (!isset($data[$semester->educationYear->name]))
                $data[$semester->educationYear->name] = [];

            $data[$semester->educationYear->name][$semester->id] = $semester->name;
        }

        return $data;
    }

    public function getCurriculumSemesterSubjectsProvider()
    {
        $query = EStudentPttCurriculumSubject::find()
            ->with(['subject', 'semester'])
            ->andFilterWhere([
                '_curriculum' => $this->_curriculum,
                'active' => true,
            ])
            ->andFilterWhere(['_semester' => Semester::find()
                ->select(['code'])
                ->andFilterWhere([
                    '_curriculum' => $this->_curriculum,
                    'active' => true
                ])
                ->andFilterWhere(['<=', 'code', $this->semester->code])
                ->column()
            ])
            ->orderBy([
                '_semester' => SORT_ASC,
                'position' => SORT_ASC,
            ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function getStudentPttSubjectsProvider()
    {
        return new ActiveDataProvider(
            [
                'query' => $this
                    ->getStudentPttSubjects()
                    ->with(['curriculumSubject', 'curriculumSubject.subject', 'academicRecord']),
                'sort' => [
                    'attributes' => []
                ],
                'pagination' => [
                    'pageSize' => 1200,
                ],
            ]
        );
    }

    public function canBeDeleted()
    {
        return $this->getStudentPttSubjects()
                ->orFilterWhere(['>', 'total_point', 0])
                ->orFilterWhere(['>', 'grade', 0])
                ->count() == 0;
    }
}
