<?php

namespace common\models\finance;

use common\components\db\PgQuery;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 *
 * Class EStudentContractInvoiceMeta
 * @package common\models\finance
 */
class EStudentContractInvoiceMeta extends EStudentContract
{
    public function rules()
    {
        return [
            [
                [
                    '_department',
                    '_specialty',
                    '_education_form',
                    '_education_type',
                    '_group',
                    '_level',
                    '_curriculum',
                    '_student_contract',
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
            '_student_contract' => __('Student Contract'),

        ]);
    }

    /**
     * @param $params
     * @param null $department
     * @param bool $asProvider
     * @return PgQuery | ActiveDataProvider
     */
    public function searchForInvoice($params, $department = null, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();

        $query->joinWith(
            ['student', 'department', 'group', 'specialty', 'educationYear', 'educationType', 'educationForm']
        );

        if ($this->search) {
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('e_student.passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('e_student.uzasbo_id_number', $this->search);
            $query->orWhereLike('e_student_contract.number', $this->search);
        }

        if ($department) {
            $this->_department = $department;
            $query->andFilterWhere(['e_student_contract._department' => $department]);
        } elseif ($this->_department) {
            $query->andFilterWhere(['e_student_contract._department' => intval($this->_department)]);
        }

        if ($this->_education_type) {
            $query->andFilterWhere(['e_student_contract._education_type' => $this->_education_type]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['e_student_contract._education_year' => $this->_education_year]);
        }
        if ($this->_education_form) {
            $query->andFilterWhere(['e_student_contract._education_form' => $this->_education_form]);
        }
        if ($this->_specialty) {
            $query->andFilterWhere(['e_student_contract._specialty' => $this->_specialty]);
        }
        if ($this->_level) {
            $query->andFilterWhere(['e_student_contract._level' => $this->_level]);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['e_student_contract._curriculum' => $this->_curriculum]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['e_student_contract._group' => $this->_group]);
        }

        $query->andFilterWhere(['e_student_contract.contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED]);
        $query->andFilterWhere(['e_student_contract.accepted' => true]);
        $query->andFilterWhere(['e_student_contract.active' => true]);

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
                        '_group',
                        '_level',
                        '_specialty_id',
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
            ->andFilterWhere(['active' => true, 'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED])
            ->distinct();

        foreach (['_department', '_education_form', '_education_type', '_group', '_level'] as $attribute) {
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

    public function getLevelItems()
    {
        return ArrayHelper::map(
            Course::find()
                ->orderByTranslationField('name')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_level')])
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

}
