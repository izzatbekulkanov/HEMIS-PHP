<?php

namespace common\models\employee;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\EmployeeAcademicDegreeUpdater;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use common\models\system\classifier\Country;
use DateTime;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_employee_meta".
 *
 * @property int $id
 * @property int $_employee
 * @property int $_academic_degree
 * @property int $_academic_rank
 * @property int $_education_year
 * @property int $_country
 * @property string $diploma_type
 * @property string $specialty_name
 * @property string $specialty_code
 * @property string $diploma_number
 * @property string $council_number
 * @property string $university
 * @property DateTime $council_date
 * @property DateTime $diploma_date
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEmployee $employee
 * @property AcademicDegree $academicDegree
 * @property AcademicRank $academicRank
 * @property EducationYear $educationYear
 * @property Country $country
 */
class EEmployeeAcademicDegree extends HemisApiSyncModel
{
    const TYPE_DEGREE = 'degree';
    const TYPE_RANK = 'rank';

    public static function tableName()
    {
        return 'e_employee_academic_degree';
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_DEGREE => __('Academic Degree'),
            self::TYPE_RANK => __('Academic Rank'),
        ];
    }

    public function diplomaTypeIsRank()
    {
        return $this->diploma_type == self::TYPE_RANK;
    }

    public function getTypeLabel()
    {
        $labels = self::getTypeOptions();
        return isset($labels[$this->diploma_type]) ? $labels[$this->diploma_type] : '';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_employee', '_education_year', 'diploma_type', 'diploma_number', 'diploma_date',
                'council_date', 'council_number', 'specialty_code', 'specialty_name', '_country', 'university'], 'required'],
            [['diploma_number', 'council_number'], 'string', 'max' => 40],
            [['diploma_type'], 'in', 'range' => array_keys(self::getTypeOptions())],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_academic_degree'], 'exist', 'skipOnError' => true, 'targetClass' => AcademicDegree::className(), 'targetAttribute' => ['_academic_degree' => 'code']],
            [['_academic_rank'], 'exist', 'skipOnError' => true, 'targetClass' => AcademicRank::className(), 'targetAttribute' => ['_academic_rank' => 'code']],
        ]);
    }


    public function getAcademicDegree()
    {
        return $this->hasOne(AcademicDegree::className(), ['code' => '_academic_degree']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getAcademicRank()
    {
        return $this->hasOne(AcademicRank::className(), ['code' => '_academic_rank']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()
            ->joinWith(['employee']);

        $query->with(['educationYear', 'academicDegree', 'academicRank', 'country']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['_education_year' => SORT_DESC, 'updated_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_employee_academic_degree.specialty_code', $this->search);
            $query->orWhereLike('e_employee_academic_degree.specialty_name', $this->search);
            $query->orWhereLike('e_employee_academic_degree.diploma_number', $this->search);
            $query->orWhereLike('e_employee_academic_degree.council_number', $this->search);
            $query->orWhereLike('e_employee_academic_degree.university', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        if ($this->_employee) {
            $query->andFilterWhere(['e_employee_academic_degree._employee' => $this->_employee]);
        }
        if ($this->diploma_type) {
            $query->andFilterWhere(['e_employee_academic_degree.diploma_type' => $this->diploma_type]);

            if ($this->diplomaTypeIsRank()) {
                $query->andFilterWhere(['e_employee_academic_degree._academic_rank' => $this->_academic_rank]);
            } else {
                $query->andFilterWhere(['e_employee_academic_degree._academic_degree' => $this->_academic_degree]);
            }
        }

        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf("%s / %s", $this->academicRank ? $this->academicRank->name : $this->academicDegree->name, $this->employee->getFullName());
    }

    public function beforeSave($insert)
    {
        if ($this->diplomaTypeIsRank()) {
            $this->_academic_degree = null;
        } else {
            $this->_academic_rank = null;
        }

        return parent::beforeSave($insert);
    }

    public function checkToApi($updateIfDifferent = true)
    {
        $result = false;
        if (HEMIS_INTEGRATION) {
            $this->setAsShouldBeSynced();
            $result = EmployeeAcademicDegreeUpdater::checkModel($this, $updateIfDifferent);

            if ($this->_sync_status != self::STATUS_ERROR)
                $this->setAsSyncPerformed();
        }
        return $result;
    }


    public function syncToApi($delete = false)
    {
        $result = false;

        if (HEMIS_INTEGRATION) {
            $this->setAsShouldBeSynced();
            $this->refresh();
            $result = EmployeeAcademicDegreeUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
