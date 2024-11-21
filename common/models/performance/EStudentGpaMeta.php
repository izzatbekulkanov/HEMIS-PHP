<?php

namespace common\models\performance;

use common\components\db\PgQuery;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 *
 * Class EStudentGpaMeta
 * @package common\models\student
 */
class EStudentGpaMeta extends EStudentMeta
{
    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForGpa($params, $department = null, $asProvider = true)
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
            ['student', 'level', 'department', 'group', 'specialty', 'educationYear', 'educationType', 'educationForm', 'studentGpa']
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
            $query->andFilterWhere(['e_student_meta._department' => $department]);
        } elseif ($this->_department) {
            $query->andFilterWhere(['e_student_meta._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_meta._education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_meta._education_year' => $this->_education_year]);
        }
        if ($this->_payment_form) {
            $query->andFilterWhere(['e_student_meta._payment_form' => $this->_payment_form]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_meta._education_form' => $this->_education_form]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['e_student_meta._specialty_id' => $this->_specialty_id]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_meta._curriculum' => $this->_curriculum]);
        }
        if ($this->_semestr) {
            $query->andFilterWhere(['e_student_meta._semestr' => $this->_semestr]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_meta._group' => $this->_group]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['e_student_meta._level' => $this->_level]);
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
                    ->where([
                        '_department' => $this->_department ?: -1
                    ])
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

}
