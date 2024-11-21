<?php

namespace common\models\student;

use common\models\academic\EDecreeStudent;
use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\Semester;
use common\models\performance\EStudentGpa;
use common\models\system\Admin;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\StudentStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * @property EStudentGpa $studentGpa
 *
 * Class EStudentGpaMeta
 * @property  Course $nextLevelItem
 * @package common\models\student
 */
class EStudentRestoreMeta extends EStudentMeta
{
    public $nextLevel;
    public $subjects_map;
    public $selectedStudents;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'nextLevel' => __('Next Level'),
            'selectedStudents' => __('Selected Students'),
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_decree'], 'required'],
            [['nextLevel'], 'required'],
            [['subjects_map'], 'safe'],
            [['selectedStudents'], 'number', 'min' => 1],
        ]);
    }

    public function searchForRestore($params, $department = null)
    {
        $this->load($params);
        $query = self::find();

        if ($department) {
            $this->_department = intval($department);
        }


        $query->joinWith(
            [
                'student', 'level', 'group',
                'educationType', 'educationForm', 'studentGpa', 'markingSystem'
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

        if ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => $this->_department]);
        }

        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }

        $statuses = array_keys(StudentStatus::getRestoreStatusOptions());
        if ($this->_student_status && in_array($this->_student_status, $statuses)) {
            $query->andFilterWhere(['e_student_meta._student_status' => $this->_student_status]);
        } else {
            $query->andFilterWhere(['e_student_meta._student_status' => $statuses]);
        };

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


    public function getEducationTypeItems()
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => self::find()
                    ->select(['_education_type'])
                    ->where([
                        '_department' => $this->_department ?: -1,
                        '_student_status' => array_keys(StudentStatus::getRestoreStatusOptions()),
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
                        '_student_status' => array_keys(StudentStatus::getRestoreStatusOptions()),
                    ])
                    ->distinct()
                    ->column()])
                ->all(), 'code', 'name');
    }


    public function getEducationTypeItemsForRestore(self $restoreModel)
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $restoreModel->_education_type])
                ->all(), 'code', 'name');
    }


    public function getEducationFormItemsForRestore(self $restoreModel)
    {
        return ArrayHelper::map(
            EducationForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $restoreModel->_education_form])
                ->all(), 'code', 'name');
    }


    public function getCurriculumItemsForRestore(self $restoreMeta)
    {
        $query = ECurriculum::find()
            ->orderByTranslationField('name')
            ->where([
                'active' => true,
                //  '_specialty_id' => $restoreMeta->curriculum->_specialty_id,
                '_education_type' => $this->_education_type ?: -1,
                '_education_form' => $this->_education_form ?: -1,
            ]);
        if ($this->_department) {
            $query->andFilterWhere([
                '_department' => $this->_department,
            ]);
        }

        if ($restoreMeta->_curriculum) {
            return ArrayHelper::map($query->all(), 'id', 'name');
        }

        return [];
    }


    public function getSemesterItemsForRestore(self $restoreMeta)
    {
        if ($this->_curriculum) {
            $current = Semester::findOne(['_curriculum' => $restoreMeta->_curriculum, 'code' => $restoreMeta->_semestr]);

            return ArrayHelper::map(Semester::find()
                ->where([
                    'active' => true,
                    '_curriculum' => $this->_curriculum
                ])
                ->andFilterWhere(['<=', 'position', $current->position])
                ->addOrderBy(['position' => SORT_ASC])
                ->all(), 'code', 'name');
        }

        return [];
    }


    public function getGroupItemsForRestore(self $restoreMeta)
    {
        if ($this->_curriculum) {
            return ArrayHelper::map(EGroup::find()
                ->where([
                    'active' => true,
                    '_curriculum' => $this->_curriculum,
                ])
                ->orderBy(['name' => SORT_ASC])
                ->all(), 'id', 'name');
        }

        return [];
    }


    public function loadRestoreParams($get, self $restoreModel)
    {
        $this->load($get);

        if ($restoreModel) {
            $this->setAttributes($restoreModel->getAttributes([
                '_education_type',
                '_education_form',
                '_student',
                '_level',
            ]));
            $this->updated_at = $this->getTimestampValue();
            $this->_student_status = StudentStatus::STUDENT_TYPE_STUDIED;
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

        if ($this->_group == null) {
            $this->_decree = null;
        }

    }

    public function restoreStudent(Admin $user, self $restoreModel)
    {
        /**
         * @var $meta self
         * @var $semester Semester
         * @var $level ECurriculumWeek
         */

        $semester = Semester::find()
            ->where([
                'code' => $this->_semestr,
                '_curriculum' => $this->_curriculum
            ])
            ->one();

        if ($restoreModel->canOperateRestore()) {
            $revertMeta = [];
            $newMeta = [];
            $oldMeta = [];
            $students = [];
            $decreeStudents = [];

            $time = (new \DateTime())->format('Y-m-d H:i:s');
            $data = [
                'active' => true,
                'subjects_map' => $this->subjects_map,
                '_restore_meta_id' => $restoreModel->id,
                '_payment_form' => PaymentForm::PAYMENT_FORM_CONTRACT,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                '_student' => $restoreModel->_student,
                'student_id_number' => $restoreModel->student_id_number,
                'created_at' => $time,
                'updated_at' => $time,

                '_education_form' => $this->curriculum->_education_form,
                '_education_type' => $this->curriculum->_education_type,
                '_curriculum' => $this->_curriculum,
                '_department' => $this->curriculum->_department,
                '_specialty_id' => $this->curriculum->_specialty_id,
                '_education_year' => $this->semester->_education_year,
                '_group' => $this->_group,
                '_semestr' => $semester->code,
                '_level' => $semester->_level,

            ];

            if ($this->_decree) {
                $data['_decree'] = $this->_decree;
                $data['order_number'] = $this->decree->number;
                $data['order_date'] = $this->decree->date->format('Y-m-d H:i:s');

                if (EDecreeStudent::findOne([
                        '_decree' => $this->_decree,
                        '_student' => $restoreModel->_student,
                    ]) == null)
                    $decreeStudents[] = [
                        '_decree' => $this->_decree,
                        '_student' => $restoreModel->_student,
                        '_admin' => $user->id,
                        '_student_meta' => $restoreModel->id,
                        'created_at' => $time,
                    ];
            }


            if ($m = EStudentMeta::findOne([
                '_curriculum' => $this->_curriculum,
                '_student' => $restoreModel->_student,
                '_education_type' => $this->curriculum->_education_type,
                '_education_year' => $this->semester->_education_year,
                '_semestr' => $this->_semestr,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            ])) {
                $revertMeta[] = $m->id;
            } else {
                $newMeta[] = $data;
            }
            $oldMeta[] = $restoreModel->id;
            $students[] = $restoreModel->_student;


            $transaction = Yii::$app->db->beginTransaction();

            try {
                EStudentMeta::updateAll(['active' => false], ['id' => $oldMeta]);
                EStudent::updateAll(['_sync' => false], ['id' => $students]);

                if (count($revertMeta)) {
                    EStudentMeta::updateAll(['active' => true, '_level' => $semester->_level], ['id' => $revertMeta]);
                }

                if (count($newMeta))
                    Yii::$app->db
                        ->createCommand()
                        ->batchInsert('e_student_meta', array_keys($newMeta[0]), $newMeta)
                        ->execute();

                if (count($decreeStudents))
                    Yii::$app->db
                        ->createCommand()
                        ->batchInsert('e_decree_student', array_keys($decreeStudents[0]), $decreeStudents)
                        ->execute();

                /**
                 * @var $academicRecord EAcademicRecord
                 * @var $curSubject ECurriculumSubject
                 */
                foreach (explode(',', $this->subjects_map) as $subject) {
                    $item = explode(':', $subject);
                    if (count($item) == 2) {
                        if ($academicRecord = EAcademicRecord::findOne($item[1])) {
                            if ($curSubject = ECurriculumSubject::findOne($item[0])) {
                                $academicRecord->updateAttributes([
                                    '_subject' => $curSubject->_subject,
                                    '_semester' => $curSubject->_semester,
                                    '_curriculum' => $curSubject->_curriculum,
                                    'total_acload' => $curSubject->total_acload,
                                    'credit' => $curSubject->credit,
                                    'updated_at' => $time,
                                ]);
                            }
                        }
                    }
                }

                $transaction->commit();

                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return false;
    }

    /**
     * @return ECurriculumSubject[]
     */
    public function getCurriculumSemesterSubjects()
    {
        $query = ECurriculumSubject::find()
            ->with(['subject', 'semester'])
            ->andFilterWhere([
                '_curriculum' => $this->_curriculum ?: -1,
                'active' => true,
            ])
            ->andFilterWhere(['<=', '_semester', $this->_semestr ?: 1000000])
            ->orderBy([
                '_semester' => SORT_ASC,
                'position' => SORT_ASC,
            ]);

        return $query->all();
    }
}
