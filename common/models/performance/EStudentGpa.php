<?php

namespace common\models\performance;

use common\components\db\PgQuery;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\RatingGrade;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_student_gpa".
 *
 * @property int $id
 * @property int $_student
 * @property int $_student_meta
 * @property string $_education_type
 * @property string $_education_form
 * @property string $_education_year
 * @property string $_level
 * @property string[] $data
 * @property integer $_department
 * @property integer $_curriculum
 * @property integer $_group
 * @property integer $debt_subjects
 * @property float $gpa
 * @property boolean $can_transfer
 * @property float $credit_sum
 * @property integer $subjects
 * @property Course $level
 * @property EStudent $student
 * @property EStudentMeta $studentMeta
 * @property EducationForm $educationForm
 * @property EducationYear $educationYear
 * @property EducationType $educationType
 * @property EDepartment $department
 * @property ECurriculum $curriculum
 * @property EGroup $group
 * @property MarkingSystem $markingSystem
 */
class EStudentGpa extends _BaseModel
{
    public static function tableName()
    {
        return 'e_student_gpa';
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    ['_education_type', '_education_year', '_education_form', '_level', '_student', '_department', '_curriculum', '_group'],
                    'required',
                ],
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                '_level' => __('Course'),
                'subjects' => __('Subjects Count'),
                'debt_subjects' => __('Debt Subjects'),
                '_curriculum' => __('Curriculum Curriculum'),
                '_department' => __('Structure Faculty'),
            ]
        );
    }

    public function getEducationForm()
    {
        return $this->hasOne(EducationForm::className(), ['code' => '_education_form']);
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

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['id' => '_student_meta']);
    }

    public function getLevel()
    {
        return $this->hasOne(Course::className(), ['code' => '_level']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
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


    public function search($params)
    {
        $this->load($params);

        $query = self::find()
            ->joinWith(['student', 'markingSystem']);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
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

        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['_education_form' => $this->_education_form]);
        }

        return $dataProvider;
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
        if ($this->_department == null) {
            $this->_education_year = null;
        }
        if ($this->_education_year == null) {
            $this->_education_type = null;
        }
        if ($this->_education_type == null) {
            $this->_education_form = null;
        }
        if ($this->_education_form == null) {
            $this->_curriculum = null;
        }
        if ($this->_curriculum == null) {
            $this->_group = null;
        }

        $query = self::find();

        $query->joinWith(
            [
                'curriculum',
                'group',
                'student',
                'level',
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
            $query->andFilterWhere(['e_student_gpa._department' => $department]);
        } elseif ($this->_department) {
            $query->andFilterWhere(['e_student_gpa._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_gpa._education_type' => $this->_education_type]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_gpa._education_year' => $this->_education_year]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_gpa._education_form' => $this->_education_form]);
        }

        if ($this->_level) {
            $query->andFilterWhere(['e_student_gpa._level' => $this->_level]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_student_gpa._group' => $this->_group]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_gpa._curriculum' => $this->_curriculum]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['_department' => SORT_ASC, '_curriculum' => SORT_ASC, '_group' => SORT_ASC, 'e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC],
                    'attributes' => [
                        '_department',
                        '_curriculum',
                        'e_student.second_name',
                        'e_student.first_name',
                        'e_student.third_name',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_level',
                        'debt_subjects',
                        '_group',
                        'gpa',
                        'subjects',
                        'credit_sum',
                        'updated_at',
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


    public function getEducationYearItems()
    {
        return ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => self::find()->select(['_education_year'])
                    ->where(['_department' => $this->_department ?: -1])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getEducationTypeItems()
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_type'])
                    ->where([
                        '_department' => $this->_department ?: -1,
                        '_education_year' => $this->_education_year ?: -1
                    ])
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
                    ->where([
                        '_department' => $this->_department ?: -1,
                        '_education_year' => $this->_education_year ?: -1,
                        '_education_type' => $this->_education_type ?: -1,
                    ])
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
                        ->where([
                            '_department' => $this->_department ?: -1,
                            '_education_year' => $this->_education_year ?: -1,
                            '_education_type' => $this->_education_type ?: -1,
                            '_education_form' => $this->_education_form ?: -1,
                        ])
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
                ->where([
                    '_department' => $this->_department ?: -1,
                    '_education_year' => $this->_education_year ?: -1,
                    '_education_type' => $this->_education_type ?: -1,
                    '_education_form' => $this->_education_form ?: -1,
                    '_curriculum' => $this->_curriculum ?: -1,
                ])
                ->distinct()
                ->column()]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }


    public function reCalculateGpa($update = false)
    {
        $type = [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL,RatingGrade::RATING_GRADE_COURSE, RatingGrade::RATING_GRADE_PRACTICUM];

        $subjectIds = EStudentSubject::find()
            ->select([
                'e_curriculum_subject._semester',
                'e_curriculum_subject._subject',
                'e_curriculum_subject.total_acload',
                'e_curriculum_subject.credit',
                'e_academic_record.total_point',
                'e_academic_record.grade',
            ])
            ->leftJoin('e_curriculum_subject', '
            e_curriculum_subject._subject = e_student_subject._subject and 
            e_curriculum_subject._semester = e_student_subject._semester and 
            e_curriculum_subject._curriculum = e_student_subject._curriculum
            ')
            ->leftJoin('e_academic_record', '
            e_academic_record._student = e_student_subject._student and 
            e_academic_record._subject = e_student_subject._subject and 
            e_academic_record._semester = e_student_subject._semester and 
            e_academic_record._curriculum = e_student_subject._curriculum
            ')
            ->filterWhere([
                'e_student_subject._curriculum' => $this->_curriculum,
                'e_student_subject._student' => $this->_student,
                'e_student_subject._education_year' => $this->_education_year,
                'e_curriculum_subject._rating_grade' => $type,
                'e_curriculum_subject.active' => true,
            ])
            ->orderBy(['e_curriculum_subject._semester' => SORT_ASC])
            ->asArray()
            ->all();

        $acLoad = 0;
        $totalCredits = 0;
        $subjects = 0;
        $studentCredits = 0;
        $minLimit = 0;
        $debts = 0;


        if ($this->markingSystem->isCreditMarkingSystem()) {
            $minLimit = $this->markingSystem->gpa_limit;
        } else if ($this->markingSystem->isRatingSystem()) {
            $minLimit = intval(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_RATING, GradeType::GRADE_TYPE_THREE)->name);
        } else if ($this->markingSystem->isFiveMarkSystem()) {
            $minLimit = round(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border, 0);
        }

        foreach ($subjectIds as $item) {
            $totalCredits += intval($item['credit']);
            $studentCredits += intval($item['credit']) * intval($item['grade']);
            $subjects += 1;
            if (intval($item['grade']) < $minLimit) {
                $debts++;
            }
        }

        $data = [
            'data' => $subjectIds,
            'debt_subjects' => $debts,
            'subjects' => $subjects,
            'updated_at' => $this->getTimestampValue()
        ];

        if ($this->markingSystem->isCreditMarkingSystem()) {
            $data['gpa'] = $totalCredits > 0 ? round($studentCredits / $totalCredits, 1) : 0;
            $data['credit_sum'] = round($totalCredits, 1);
            $data['can_transfer'] = $data['gpa'] >= $this->markingSystem->gpa_limit;
        } else {
            $data['can_transfer'] = $debts == 0;
        }

        if ($update) {
            return $this->updateAttributes($data);
        }
        $this->setAttributes($data, false);

        return true;
    }

    public static function calculateGpa($items)
    {

        $success = 0;
        /**
         * @var $gpa self
         * @var $metas EStudentGpaMeta[]
         */
        $updates = [];

        $metas = EStudentGpaMeta::find()
            ->with(['studentGpa', 'markingSystem'])
            ->where(['id' => $items])
            ->all();

        foreach ($metas as $meta) {
            if ($gpa = EStudentGpa::findOne(['_education_year' => $meta->_education_year, '_student' => $meta->_student])) {
                $gpa->_student_meta = $meta->id;
            } else {
                $gpa = new EStudentGpa();
            }
            $gpa->_student_meta = $meta->id;

            $gpa->setAttributes($meta->getAttributes([
                '_student',
                '_education_year',
                '_education_form',
                '_education_type',
                '_department',
                '_group',
                '_curriculum',
                '_level',
            ]), false);

            if ($gpa->reCalculateGpa(false)) {
                $updates[] = $gpa;
            };
        }

        if (count($updates)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($updates as $gpa) {
                    if ($gpa->save()) {
                        $success++;
                    }
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }


        return $success;
    }

}
