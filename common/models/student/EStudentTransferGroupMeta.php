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
 * @property  Semester $nextSemesterItem
 * @package common\models\student
 */
class EStudentTransferGroupMeta extends EStudentMeta
{
    const SCENARIO_TRANSFER = 'transfer';
    const SCENARIO_EXPEL = 'expel';

    public $nextEducationForm;
    public $nextDepartment;
    public $nextCurriculum;
    public $nextGroup;
    public $nextSemester;
    public $selectedStudents;
    public $subjectsMap;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'nextDepartment' => __('Next Department'),
            'nextEducationForm' => __('Next Education Form'),
            'nextGroup' => __('Next Group'),
            'nextCurriculum' => __('Next Curriculum'),
            'nextSemester' => __('Next Semester'),
            'selectedStudents' => __('Selected Students'),
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_group'], 'required'],
            [['_decree'], 'required'],
            [['nextDepartment'], 'required'],
            [['nextSemester'], 'required'],
            [['nextGroup'], 'required'],
            [['nextEducationForm'], 'required'],
            [['nextCurriculum'], 'required'],
            [['subjectsMap'], 'safe'],
            [['selectedStudents'], 'number', 'min' => 1],
        ]);
    }

    public function getNextGroupItem()
    {
        return $this->nextGroup ? $this->hasOne(EGroup::className(), ['id' => 'nextGroup'])->with(['department', 'curriculum', 'specialty', 'educationType', 'educationForm']) : null;
    }

    public function getNextSemesterItem()
    {
        return $this->nextSemester ? $this->hasOne(Semester::className(), ['id' => 'nextSemester'])->with(['level', 'educationYear']) : null;
    }


    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForGroupTransfer($params, $department = null)
    {
        $this->load($params);

        if ($department) {
            $this->_department = intval($department);
            $this->nextDepartment = intval($department);
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
            $this->_education_year = null;
        }

        if ($this->_education_year == null) {
            $this->_semestr = null;
        }

        if ($this->_semestr == null) {
            $this->_group = null;
        }

        if ($this->nextEducationForm == null) {
            $this->nextEducationForm = $this->_education_form;
        }
        if ($this->nextDepartment == null) {
            $this->nextDepartment = $this->_department;
        }
        if ($this->nextCurriculum == null) {
            $this->nextCurriculum = $this->_curriculum;
        }
        if ($this->nextGroup == null) {
            $this->nextGroup = $this->_group;
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

        $query->andFilterWhere(['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => true]);

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
                ->orderBy([
                    'position' => SORT_ASC
                ])
                ->where([
                    '_curriculum' => $this->_curriculum,
                    'active' => true,
                    'code' => $this->getSelectQueryFilters('_semestr'),
                ])
                ->all(), 'code', function (Semester $item) {
            return sprintf('%s / %s / %s', $item->educationYear->name, $item->level ? $item->level->name : '--', $item->name);
        });

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

    public function getNextSemesterOptions()
    {
        $items = [];

        if ($this->nextCurriculum && $this->nextGroup && $this->semester) {
            $semesterCodes = self::find()
                ->select(['_semestr'])
                ->where([
                    '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                    'active' => true,
                    '_group' => $this->nextGroup,
                    '_curriculum' => $this->nextCurriculum
                ])
                ->column();

            $query = Semester::find()
                ->where([
                    'active' => EGroup::STATUS_ENABLE,
                    '_curriculum' => $this->nextCurriculum,
                ])
                ->andFilterWhere([
                    '<=', 'code', $this->semester->code
                ])
                ->orderBy(['position' => SORT_DESC]);

            if (count($semesterCodes)) {
                $query->andFilterWhere(['code' => $semesterCodes]);
            }

            $items = ArrayHelper::map($query->all(), 'id', function (Semester $item) {
                return sprintf('%s / %s / %s', $item->educationYear->name, $item->level ? $item->level->name : '--', $item->name);
            });
        }

        if (!isset($items[$this->nextSemester])) {
            $this->nextSemester = null;
        }

        return $items;
    }


    public function getDecreeOptions()
    {
        $items = [];
        if ($this->nextGroup) {
            $items = ArrayHelper::map(EDecree::find()
                ->andFilterWhere([
                    'status' => EDecree::STATUS_ENABLE,
                    '_department' => $this->_department,
                    '_decree_type' => DecreeType::TYPE_TRANSFER,
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

    public function canTransferToGroup(EGroup $group = null)
    {
        if ($group) {
            /*$semester = $group->getSemesterObject();
            return $semester == null || $semester->code == $this->_semestr;*/
        }

        return true;
    }

    public function transferItems(Admin $user, $items)
    {
        /**
         * @var $meta self
         * @var $semester Semester
         */

        if ($group = $this->nextGroupItem) {
            if ($semester = $this->nextSemesterItem) {

                /**
                 * @var $academicRecord EAcademicRecord
                 * @var $curSubject ECurriculumSubject
                 * @var $subjects ECurriculumSubject[]
                 */
                $subjectsMap = [];
                foreach (explode(',', $this->subjectsMap) as $subject) {
                    $item = explode(':', $subject);
                    if (count($item) == 2) {
                        $subjectsMap[$item[0]] = $item[1];
                    }
                }

                if (count($subjectsMap)) {
                    $subjects = ECurriculumSubject::find()
                        ->with(['semester', 'subject'])
                        ->where(['id' => array_merge(array_values($subjectsMap), array_keys($subjectsMap))])
                        ->indexBy('id')
                        ->all();
                } else {
                    $subjects = [];
                }

                $metas = self::find()
                    ->with(['student', 'markingSystem'])
                    ->where(['id' => $items])
                    ->all();

                $newMeta = [];
                $updatedMeta = [];
                $oldMeta = [];
                $students = [];
                $decreeStudents = [];
                $success = 0;
                $time = (new \DateTime())->format('Y-m-d H:i:s');

                $newARecords = [];
                $updateARecords = [];
                foreach ($metas as $meta) {
                    if ($meta->canTransferToGroup($group)) {
                        $data = $meta->getAttributes([
                            'student_id_number',
                            '_student',
                            '_payment_form',
                            '_student_status',
                        ]);


                        if ($this->_decree) {
                            $data['_decree'] = $this->_decree;
                            $data['order_number'] = $this->decree->number;
                            $data['order_date'] = $this->decree->date->format('Y-m-d H:i:s');
                        }

                        $data['active'] = true;
                        $data['subjects_map'] = $this->subjectsMap;
                        $data['_education_type'] = $group->_education_type;
                        $data['_education_form'] = $group->_education_form;
                        $data['_department'] = $group->_department;
                        $data['_curriculum'] = $group->_curriculum;
                        $data['_group'] = $group->id;
                        $data['_level'] = $semester->_level;
                        $data['_semestr'] = $semester->code;
                        $data['_specialty_id'] = $group->curriculum->_specialty_id;
                        $data['_education_year'] = $semester->_education_year;
                        $data['updated_at'] = $time;

                        if ($m = EStudentMeta::findOne([
                            '_curriculum' => $data['_curriculum'],
                            '_student' => $data['_student'],
                            '_education_type' => $data['_education_type'],
                            '_education_year' => $data['_education_year'],
                            '_semestr' => $data['_semestr'],
                            '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        ])) {
                            $updatedMeta[] = [$m, $data];
                            if ($meta->id != $m->id)
                                $oldMeta[] = $meta->id;
                        } else {
                            $data['created_at'] = $time;
                            $newMeta[] = $data;
                            $oldMeta[] = $meta->id;
                        }

                        $students[] = $meta->_student;

                        if ($this->_decree) {
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


                        foreach ($subjectsMap as $nextId => $thisId) {
                            if ($oldRecord = $subjects[$thisId]->getStudentSubjectRecord($meta->_student)) {
                                $nextSubject = $subjects[$nextId];
                                $newARecords[] = array_merge($oldRecord->getAttributes([
                                    '_student',
                                    'student_name',
                                    'student_id_number',
                                    'total_point',
                                    'grade',
                                    'position',
                                ]), [
                                    'updated_at' => $time,
                                    'created_at' => $time,
                                    'active' => true,
                                    'credit' => $nextSubject->credit,
                                    'total_acload' => $nextSubject->total_acload,
                                    '_subject' => $nextSubject->_subject,
                                    'subject_name' => $nextSubject->subject->name,
                                    '_curriculum' => $group->_curriculum,
                                    'curriculum_name' => $group->curriculum->name,
                                    '_semester' => $nextSubject->_semester,
                                    '_education_year' => $nextSubject->semester->_education_year,
                                    'education_year_name' => $nextSubject->semester->educationYear->name,
                                ]);
                            }
                        }

                        $success++;
                    }
                }

                if ($success) {
                    $transaction = Yii::$app->db->beginTransaction();

                    try {
                        EStudentMeta::updateAll(['active' => false], ['id' => $oldMeta]);
                        EStudent::updateAll(['_sync' => false], ['id' => $students]);

                        if (count($updatedMeta)) {
                            foreach ($updatedMeta as $itemData)
                                $itemData[0]->updateAttributes($itemData[1]);
                        }

                        if (count($newMeta))
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert('e_student_meta', array_keys($newMeta[0]), $newMeta)
                                ->execute();

                        if (count($newARecords))
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert('e_academic_record', array_keys($newARecords[0]), $newARecords)
                                ->execute();

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
        }

        return false;
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
