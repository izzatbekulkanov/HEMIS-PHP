<?php

namespace common\models\archive;

use common\components\db\PgQuery;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\curriculum\RatingGrade;
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
class EStudentAcademicInformationDataMeta extends EStudentMeta
{
    public function rules()
    {
        return [
            [
                [
                    '_department',
                    '_education_form',
                    '_education_type',
                    '_group',
                    '_curriculum',
                    'search',
                ],
                'safe'
            ]
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_specialty_id' => __('Specialty'),
        ]);
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForAcademicInformationData($params, $department = null, $asProvider = true)
    {
        $this->load($params);

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
        $query->andFilterWhere(['in', 'e_student_meta._student_status', [StudentStatus::STUDENT_TYPE_EXPEL, StudentStatus::STUDENT_TYPE_GRADUATED]]);
 //       $query->andFilterWhere(['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_GRADUATED]);
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
                        '_specialty_id',
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

    private function getSelectQueryFilters($col)
    {
        $query = self::find()->select([$col])
            ->andFilterWhere(['active' => true, '_student_status' => [StudentStatus::STUDENT_TYPE_EXPEL, StudentStatus::STUDENT_TYPE_GRADUATED]])
            ->distinct();

        foreach (['_department', '_education_form', '_education_type', '_group', '_curriculum'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }

    public function getDepartmentItems()
    {
        return ArrayHelper::map(
            EDepartment::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_department')])
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


    public function getEducationTypeItems()
    {
        return ArrayHelper::map(
            EducationType::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_type')])
                ->all(), 'code', 'name');
    }


    public function getEducationFormItems()
    {
        return ArrayHelper::map(
            EducationForm::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_education_form')])
                ->all(), 'code', 'name');
    }


    public function getCurriculumItems()
    {
        return ArrayHelper::map(
            ECurriculum::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_curriculum')])
                ->all(), 'id', 'name');
    }


    public function getGroupItems()
    {

        return ArrayHelper::map(
            EGroup::find()
                ->orderBy(['_department' => SORT_ASC, 'name' => SORT_ASC])
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_group')])
                ->all(), 'id', 'name');
    }

    /**
     * @return EAcademicInformationDataSubject[]
     */
    public function getStudentSubjectsWithAcademicInformationData($type = [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL])
    {
        $subjects = EAcademicInformationDataSubject::find()
            ->with(['curriculumSubject', 'curriculumSubjectExamType'])
            ->leftJoin('e_curriculum_subject', '
                e_curriculum_subject._subject = e_academic_information_data_subject._subject and 
                e_curriculum_subject._semester = e_academic_information_data_subject._semester and 
                e_curriculum_subject._curriculum = e_academic_information_data_subject._curriculum
            ')
            ->leftJoin('e_curriculum_subject_exam_type', '
                e_curriculum_subject_exam_type._subject = e_academic_information_data_subject._subject and 
                e_curriculum_subject_exam_type._semester = e_academic_information_data_subject._semester and 
                e_curriculum_subject_exam_type._curriculum = e_academic_information_data_subject._curriculum
            ')
            ->filterWhere([
                'e_academic_information_data_subject._curriculum' => $this->_curriculum,
                'e_academic_information_data_subject._student' => $this->_student,
                'e_curriculum_subject._rating_grade' => $type,
            ])
            ->orderBy([
                'e_academic_information_data_subject._semester' => SORT_ASC
            ]);


        return $subjects->all();
    }

}
