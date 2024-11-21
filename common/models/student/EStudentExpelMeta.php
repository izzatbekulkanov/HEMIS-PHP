<?php

namespace common\models\student;

use common\components\db\PgQuery;
use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\structure\EDepartment;
use common\models\system\Admin;
use common\models\system\classifier\Course;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 *
 * Class EStudentGpaMeta
 * @property  EGroup $nextGroupItem
 * @package common\models\student
 */
class EStudentExpelMeta extends EStudentMeta
{
    public $selectedStudents;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'selectedStudents' => __('Selected Students'),
            '_status_change_reason' => __('Status Change Reason'),
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['selectedStudents'], 'number', 'min' => 1],
            [['_status_change_reason'], 'safe'],
        ]);
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForExpel($params, $department = null)
    {
        $this->load($params);

        if ($department) {
            $this->_department = intval($department);
        }

        if ($this->_department == null) {
            $this->_education_type = null;
        }

        if ($this->_education_type == null) {
            $this->_education_form = null;
        }

        if ($this->_education_form == null) {
            $this->_curriculum = null;
        }

        if ($this->_curriculum == null) {
            $this->_semestr = null;
        }

        if ($this->_semestr == null) {
            $this->_group = null;
        }

        $query = self::find();

        $query->joinWith(
            [
                'student', 'level', 'group', 'semester',
                'educationType', 'educationForm', 'studentGpa', 'markingSystem']
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
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }

        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_meta._education_year' => $this->_education_year]);
        }

        if ($this->level) {
            $query->andFilterWhere(['e_student_meta._level' => $this->_level]);
        }

        if ($this->_semestr) {
            $query->andFilterWhere(['e_student_meta._semestr' => $this->_semestr]);
        }

        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }

        $query->andFilterWhere([
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            'e_student_meta.active' => true
        ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['_department' => SORT_ASC, '_group' => SORT_ASC, 'e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC],
                    'attributes' => [
                        '_department',
                        'e_student.second_name',
                        'e_student.first_name',
                        'e_student.third_name',
                        '_education_year',
                        '_education_type',
                        '_education_form',
                        '_level',
                        '_semestr',
                        '_group',
                        'gpa',
                        'debt_subjects',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }

    private function getSelectQueryFilters($col)
    {
        $query = self::find()->select([$col])
            ->andFilterWhere([
                'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])
            ->distinct();

        foreach ([
                     '_education_type',
                     '_education_form',
                     '_department',
                     '_curriculum',
                     '_education_year',
                     '_level',
                     '_semestr',
                     '_group'
                 ] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    public function getCurriculumItems()
    {
        $items = ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), 'id', 'name');

        if (!isset($items[$this->_curriculum]))
            $this->_curriculum = null;

        return $items;
    }

    public function getDepartmentItems()
    {
        $items = ArrayHelper::map(
            EDepartment::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_department')])
                ->all(), 'id', 'name');

        if (!isset($items[$this->_department]))
            $this->_department = null;

        return $items;
    }

    public function getEducationTypeItems()
    {
        $items = ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_type')])
                ->all(), 'code', 'name');

        if (!isset($items[$this->_education_type]))
            $this->_education_type = null;

        return $items;
    }

    public function getEducationFormItems()
    {
        $items = ArrayHelper::map(
            EducationForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_form')])
                ->all(), 'code', 'name');

        if (!isset($items[$this->_education_form]))
            $this->_education_form = null;

        return $items;
    }

    public function getEducationYearItems()
    {
        $items = ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_year')])
                ->all(), 'code', 'name');

        if (!isset($items[$this->_education_year]))
            $this->_education_year = null;

        return $items;
    }


    public function getSemesterItems()
    {
        $items = ArrayHelper::map(
            Semester::find()
                ->orderBy(['position' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_semestr')])
                ->all(), 'code', 'name');

        if (!isset($items[$this->_semestr]))
            $this->_semestr = null;

        return $items;
    }


    public function getGroupItems()
    {
        $items = ArrayHelper::map(EGroup::find()
            ->orderByTranslationField('name', 'ASC')
            ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_group')])
            ->all(), 'id', 'name');

        if (!isset($items[$this->_group]))
            $this->_group = null;

        return $items;
    }

    public function getNextEducationFormOptions()
    {
        $items = [];
        if ($this->nextDepartment) {
            $items = EducationForm::getClassifierOptions();
        }

        if (!isset($items[$this->nextEducationForm])) {
            $this->nextEducationForm = null;
        }

        return $items;
    }

    public function getNextDepartmentOptions($faculty)
    {
        $items = [];
        if ($this->_group || $faculty) {
            $items = EDepartment::getFaculties();
        }

        if (!isset($items[$this->nextDepartment])) {
            $this->nextDepartment = null;
        }

        return $items;
    }

    public function getNextCurriculumOptions()
    {
        $items = [];
        if ($this->nextEducationForm) {
            $items = ArrayHelper::map(ECurriculum::find()
                ->where([
                    'active' => ECurriculum::STATUS_ENABLE,
                    '_department' => $this->nextDepartment,
                    '_education_form' => $this->nextEducationForm,
                    '_education_type' => $this->_education_type,
                ])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }

        if (!isset($items[$this->nextCurriculum])) {
            $this->nextCurriculum = null;
        }

        return $items;
    }

    public function getNextGroupOptions()
    {
        $items = [];
        if ($this->nextCurriculum) {
            $items = ArrayHelper::map(EGroup::find()
                ->where([
                    'active' => EGroup::STATUS_ENABLE,
                    '_curriculum' => $this->nextCurriculum,
                    '_department' => $this->nextDepartment,
                    '_education_form' => $this->nextEducationForm,
                    '_education_type' => $this->_education_type,
                ])
                ->andFilterWhere([
                    '!=', 'id', $this->_group
                ])
                ->orderByTranslationField('name')
                ->all(), 'id', 'name');
        }

        if (!isset($items[$this->nextGroup])) {
            $this->nextGroup = null;
        }

        return $items;
    }


    public function getDecreeOptions()
    {
        $items = [];
        if ($this->_group) {
            $items = ArrayHelper::map(EDecree::find()
                ->andFilterWhere([
                    'status' => EDecree::STATUS_ENABLE,
                    '_department' => $this->_department,
                    '_decree_type' => DecreeType::TYPE_EXPEL,
                ])
                ->orderBy([
                    'date' => SORT_DESC
                ])
                ->all(),
                'id',
                function (EDecree $item) {
                    return $item->getFullInformation();
                });
        }

        if (!isset($items[$this->_decree])) {
            $this->_decree = null;
        }

        return $items;
    }

    public function canExpel()
    {
        return true;
    }

    public function expelItems(Admin $user, $items)
    {
        /**
         * @var $meta self
         * @var $semester Semester
         */

        $metas = self::find()
            ->with(['student', 'markingSystem'])
            ->where(['id' => $items])
            ->all();

        $success = 0;
        $noActiveMeta = [];
        $newMeta = [];
        $students = [];
        $decreeStudents = [];
        $time = (new \DateTime())->format('Y-m-d H:i:s');

        foreach ($metas as $meta) {
            if ($meta->canOperateExpel()) {
                if ($m = EStudentMeta::findOne([
                    '_curriculum' => $meta->_curriculum,
                    '_student' => $meta->_student,
                    '_education_type' => $meta->_education_type,
                    '_education_year' => $meta->_education_year,
                    '_semestr' => $meta->_semestr,
                    '_student_status' => StudentStatus::STUDENT_TYPE_EXPEL,
                ])) {
                    $newMeta[] = $m->id;
                    $noActiveMeta[] = $meta->id;
                } else {
                    $newMeta[] = $meta->id;
                }

                $students[] = $meta->_student;

                $success++;
                if (EDecreeStudent::findOne([
                        '_decree' => $this->_decree,
                        '_student' => $meta->_student,
                    ]) == null)
                    $decreeStudents[] = [
                        '_decree' => $this->_decree,
                        '_student' => $meta->_student,
                        '_admin' => $user->id,
                        '_student_meta' => $meta->id,
                        'created_at' => $time,
                    ];
            }
        }

        if ($success) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                EStudentMeta::updateAll([
                    'order_number' => $this->decree->number,
                    '_status_change_reason' => $this->_status_change_reason,
                    'order_date' => $this->decree->date->format('Y-m-d H:i:s'),
                    '_student_status' => StudentStatus::STUDENT_TYPE_EXPEL,
                    'updated_at' => $time,
                    'active' => true,
                ], ['id' => $newMeta]);

                if (count($noActiveMeta)) {
                    EStudentMeta::updateAll(['active' => false, 'updated_at' => $time], ['id' => $noActiveMeta]);
                }

                EStudent::updateAll(['_sync' => false], ['id' => $students]);

                if (count($decreeStudents))
                    Yii::$app->db
                        ->createCommand()
                        ->batchInsert('e_decree_student', array_keys($decreeStudents[0]), $decreeStudents)
                        ->execute();

                $transaction->commit();

                return $success;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * @return ECurriculumSubject[]
     */
    public function getCurriculumSemesterSubjects($curriculum = null, $semester = null)
    {
        $items = [];

        if ($curriculum && $semester) {
            $items = ECurriculumSubject::find()
                ->with(['subject', 'semester', 'subjectType'])
                ->andFilterWhere([
                    '_curriculum' => $curriculum,
                    'active' => true,
                ])
                ->andFilterWhere(['<=', '_semester', $semester])
                ->orderBy([
                    '_semester' => SORT_ASC,
                    'position' => SORT_ASC,
                ])
                ->all();
        }


        return $items;
    }
}
