<?php

namespace common\models\student;

use common\components\db\PgQuery;
use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\performance\EStudentGpa;
use common\models\system\Admin;
use common\models\system\classifier\Course;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\StudentStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property EStudentGpa $studentGpa
 *
 * Class EStudentGpaMeta
 * @property  Course $nextLevelItem
 * @package common\models\student
 */
class EStudentDecreeMeta extends EStudentMeta
{
    const SCENARIO_TRANSFER = 'transfer';

    public $selectedStudents;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'selectedStudents' => __('Selected Students'),
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_decree', 'selectedStudents'], 'required'],
            [['selectedStudents'], 'number', 'min' => 1],
        ]);
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForDecreeApply($params, $department = null)
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

        if ($this->_decree) {
            $this->order_date = Yii::$app->formatter->asDate($this->decree->date->getTimestamp());
        }

        $handlers = self::getDecreeTypeApplyHandlers();

        if ($this->_decree) {
            if (isset($handlers[$this->decree->decreeType->code])) {
                call_user_func_array($handlers[$this->decree->decreeType->code]['filter'], [&$query]);
            }
        }
        $query->andFilterWhere([
            'e_student_meta.active' => true
        ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        '_department' => SORT_ASC,
                        '_group' => SORT_ASC,
                        'e_student.second_name' => SORT_ASC,
                        'e_student.first_name' => SORT_ASC,
                        'e_student.third_name' => SORT_ASC
                    ],
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
                    'pageSize' => 200,
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

    public function applyItems(Admin $user, $items)
    {
        $handlers = self::getDecreeTypeApplyHandlers();

        if ($this->_decree) {
            if (isset($handlers[$this->decree->decreeType->code])) {
                return call_user_func_array($handlers[$this->decree->decreeType->code]['handler'], [$user, $this->decree, $items]);
            }
        }

        return false;
    }


    public function canOperateDecreeApply(EDecree $decree = null)
    {
        $handlers = self::getDecreeTypeApplyHandlers();
        if ($decree) {
            if (isset($handlers[$decree->decreeType->code])) {
                return call_user_func_array($handlers[$decree->decreeType->code]['validator'], [$this, $decree]);
            }
        }

        return false;
    }


    public static function getDecreeTypeApplyHandlers()
    {
        return [
            DecreeType::TYPE_STUDENT_ENROLL => [
                'validator' => function (self $meta, EDecree $decree) {
                    return
                        $meta->active &&
                        $meta->studentStatus->isStudyingStatus() &&
                        $meta->student->_decree_enroll != $decree->id;
                },
                'handler' => function (Admin $user, EDecree $decree, $items = []) {

                    /**
                     * @var $meta self
                     * @var $semester Semester
                     */

                    $metas = self::find()
                        ->with(['student', 'markingSystem', 'studentGpa'])
                        ->where(['id' => $items])
                        ->all();

                    $success = 0;
                    $students = [];
                    $decreeStudents = [];
                    $time = (new \DateTime())->format('Y-m-d H:i:s');

                    foreach ($metas as $meta) {
                        if ($meta->canOperateDecreeApply($decree)) {
                            $success++;
                            $decreeStudents[] = [
                                '_decree' => $decree->id,
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
                            //update student decree
                            EStudent::updateAll(['_decree_enroll' => $decree->id], ['id' => $students]);

                            //delete student decrees applied before
                            EDecreeStudent::deleteAll([
                                '_student' => $students,
                                '_decree' => EDecree::find()
                                    ->select(['id'])
                                    ->where(['_decree_type' => DecreeType::TYPE_STUDENT_ENROLL])
                                    ->column()
                            ]);

                            //register student decree
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert(EDecreeStudent::tableName(), array_keys($decreeStudents[0]), $decreeStudents)
                                ->execute();


                            $transaction->commit();

                            return $success;
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                    }
                },
                'filter' => function (PgQuery &$query) {
                    $query->andFilterWhere([
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                    ]);
                }
            ]
        ];
    }

}
