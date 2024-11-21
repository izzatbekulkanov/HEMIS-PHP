<?php

namespace common\models\student;

use common\components\db\PgQuery;
use common\components\hemis\HemisApiSyncModel;
use common\models\academic\EDecreeStudent;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\performance\EPerformance;
use common\models\performance\EStudentGpa;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\StudentStatus;
use common\models\system\SystemMessageTranslation;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * @property EStudentGpa $studentGpa
 *
 * Class EStudentGpaMeta
 * @property  Course $nextLevelItem
 * @property  Semester $nextLevel
 * @package common\models\student
 */
class EStudentTransferMeta extends EStudentMeta
{
    const SCENARIO_TRANSFER = 'transfer';
    const SCENARIO_EXPEL = 'expel';

    public $_nextLevel;
    public $selectedStudents;

    public function getNextLevel()
    {
        if ($this->_nextLevel)
            return $this->hasOne(Semester::class, ['id' => '_nextLevel']);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_nextLevel' => __('Next Level'),
            'selectedStudents' => __('Selected Students'),
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_decree'], 'required', 'on' => self::SCENARIO_EXPEL],
            [['_nextLevel'], 'required', 'on' => self::SCENARIO_TRANSFER],
            [['selectedStudents'], 'number', 'min' => 1],
            [['_decree'], 'required', 'when' => function () {
                return $this->isCourseTransfer();
            }, 'on' => self::SCENARIO_TRANSFER],
        ]);
    }

    public function isCourseTransfer()
    {
        return $this->_nextLevel ? $this->nextLevel->_level != $this->_level : false;
    }

    public function getStudentGpa()
    {
        return $this->hasOne(EStudentGpa::className(), ['_education_year' => '_education_year', '_student' => '_student']);
    }

    /**
     * @var ECurriculumWeek
     */
    private $_nextLevelData;

    /**
     * @return null|Semester[]
     */
    public function getNextLevelData()
    {
        if ($this->_nextLevelData === null) {
            $this->_nextLevelData = [];
            if ($this->_level && $this->_semestr && $this->_curriculum) {
                /**
                 * @var $item Semester
                 */
                $items = Semester::find()
                    ->with(['level'])
                    ->where([
                        '_curriculum' => $this->_curriculum,
                        'active' => true,
                    ])
                    ->andWhere(new Expression('_level is not null'))
                    ->orderBy([
                        '_level' => SORT_ASC,
                        'code' => SORT_ASC,
                    ])->all();

                foreach ($items as $i => $item) {
                    if ($item->_level == $this->_level && $item->code == $this->_semestr) {
                        $this->_nextLevelData['before'] = isset($items[$i - 1]) ? $items[$i - 1] : null;
                        $this->_nextLevelData['next'] = isset($items[$i + 1]) ? $items[$i + 1] : null;
                        break;
                    }
                }
            }
        }
        return $this->_nextLevelData;
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForCourseTransfer($params, $department = null)
    {
        $this->load($params);

        if ($department) {
            $this->_department = intval($department);
        }

        if ($this->_curriculum == null) {
            $this->_education_year = null;
        }

        if ($this->_education_year == null) {
            $this->_level = null;
        }

        if ($this->_level == null) {
            $this->_semestr = null;
        }

        if ($this->_semestr == null) {
            $this->_group = null;
            $this->_nextLevel = null;
        }


        if ($nextLevel = $this->getNextLevelData()) {
            if (isset($nextLevel['next']) && $this->_nextLevel == null)
                $this->_nextLevel = $nextLevel['next']->id;
        }

        if (!$this->isCourseTransfer()) {
            $this->_decree = null;
        }

        if ($this->_decree) {
           // $this->order_date = $this->decree->getShortInformation();
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
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])
            ->distinct();

        foreach (['_department', '_curriculum', '_education_year', '_level', '_group', '_semestr'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    public function getCurriculumItems()
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), 'id', 'name');
    }


    public function getEducationYearItems()
    {
        return ArrayHelper::map(
            EducationYear::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_year')])
                ->all(), 'code', 'name');
    }

    public function getLevelItems()
    {
        return ArrayHelper::map(
            Course::find()
                ->orderBy(['position' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_level')])
                ->all(), 'code', 'name');
    }

    public function getNextLevelOptions()
    {
        $data = [];
        if ($items = $this->getNextLevelData()) {
            foreach ($items as $nextLevel) {
                if ($nextLevel) {
                    $data[$nextLevel->id] = $nextLevel->level->name . ' / ' . $nextLevel->name;
                }
            }
        }

        return $data;
    }


    public function getSemesterItems()
    {
        return ArrayHelper::map(
            Semester::find()
                ->orderBy(['position' => SORT_ASC])
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_semestr')])
                ->all(), 'code', 'name');
    }


    public function getGroupItems()
    {
        $query = EGroup::find()
            ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
            ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_group')]);

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    public function canTransferNextLevel(EStudentTransferMeta $meta)
    {
        /**
         * @todo check if applicable
         */
        if ($meta->_nextLevel) {
            if ($meta->isCourseTransfer()) {
                return $this->canOperateCourseTransfer();
            }
            return true;
        }
        return false;
    }


    public function expelItems(Admin $user, $items)
    {
        /**
         * @var $meta self
         * @var $semester Semester
         */

        $metas = self::find()
            ->with(['student', 'markingSystem', 'studentGpa'])
            ->where(['id' => $items])
            ->all();

        $success = 0;
        $newMeta = [];
        $students = [];
        $decreeStudents = [];
        $time = (new \DateTime())->format('Y-m-d H:i:s');

        foreach ($metas as $meta) {
            if ($meta->canOperateCourseExpel()) {
                $success++;
                $decreeStudents[] = [
                    '_decree' => $this->_decree,
                    '_student' => $meta->_student,
                    '_admin' => $user->id,
                    '_student_meta' => $meta->id,
                    'created_at' => $time,
                ];
                $newMeta[] = $meta->id;
                $students[] = $meta->_student;
            }
        }

        if ($success) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                EStudentMeta::updateAll([
                    'order_number' => $this->decree->number,
                    'order_date' => $this->decree->date->format('Y-m-d H:i:s'),
                    '_student_status' => StudentStatus::STUDENT_TYPE_COURSE_EXPEL,
                    'updated_at' => $time,
                ], ['id' => $newMeta]);

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

    public function transferItems(Admin $user, $items)
    {
        /**
         * @var $meta self
         * @var $semester Semester
         */
        $semester = $this->nextLevel;

        $metas = self::find()
            ->with(['student', 'markingSystem', 'studentGpa'])
            ->where(['id' => $items])
            ->all();

        $revertMeta = [];
        $newMeta = [];
        $oldMeta = [];
        $students = [];
        $decreeStudents = [];
        $success = 0;
        $time = (new \DateTime())->format('Y-m-d H:i:s');
        foreach ($metas as $meta) {
            if ($meta->canTransferNextLevel($this)) {
                $data = $meta->getAttributes([
                    'student_id_number',
                    '_student',
                    '_department',
                    '_education_type',
                    '_education_form',
                    '_curriculum',
                    '_group',
                    '_payment_form',
                    '_specialty_id',
                    '_student_status',
                ]);


                if ($this->_decree) {
                    $data['_decree'] = $this->_decree;
                    $data['order_number'] = $this->decree->number;
                    $data['order_date'] = $this->decree->date->format('Y-m-d H:i:s');
                }

                $data['active'] = true;
                $data['_level'] = $semester->_level;
                $data['_semestr'] = $semester->code;
                $data['_education_year'] = $semester->_education_year;
                $data['created_at'] = $time;
                $data['updated_at'] = $time;

                if ($m = EStudentMeta::findOne([
                    '_curriculum' => $meta->_curriculum,
                    '_student' => $meta->_student,
                    '_education_type' => $meta->_education_type,
                    '_education_year' => $semester->_education_year,
                    '_semestr' => $semester->code,
                    '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                ])) {
                    $revertMeta[] = $m->id;
                } else {
                    $newMeta[] = $data;
                }


                $oldMeta[] = $meta->id;
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

                $success++;
            }
        }

        if ($success) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                EStudentMeta::updateAll(['active' => false], ['id' => $oldMeta]);
                EStudent::updateAll(['_sync' => false], ['id' => $students]);

                if (count($revertMeta)) {
                    EStudentMeta::updateAll(['active' => true], ['id' => $revertMeta]);
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

                $transaction->commit();

                return $success;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return false;
    }
}
