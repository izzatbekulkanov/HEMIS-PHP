<?php

namespace common\models\employee;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\EmployeeTrainingUpdater;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\Country;
use common\models\system\classifier\TrainingType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_employee_meta".
 *
 * @property int $id
 * @property int $_employee
 * @property int $_training_type
 * @property int $_education_year
 * @property int $_country
 * @property string $training_contract
 * @property string $specialty_name
 * @property string $university
 * @property DateTime $training_date_start
 * @property DateTime $training_date_end
 * @property bool|null $active
 *
 * @property EEmployee $employee
 * @property TrainingType $trainingType
 * @property EducationYear $educationYear
 * @property Country $country
 */
class EEmployeeTraining extends HemisApiSyncModel
{
    public static function tableName()
    {
        return 'e_employee_training';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_employee', '_education_year', '_training_type', '_country', 'university',
                'specialty_name', 'training_date_start', 'training_date_end', 'training_contract'], 'required'],
            [['training_date_end'], 'validateDate', 'message' => __('Tugash vaqati boshlanish vaqtidan katta bo\'lishi kerak')],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
        ]);
    }

    public function validateDate($attribute, $options)
    {
        if ($end = date_create_from_format('Y-m-d', $this->training_date_end, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
            if ($start = date_create_from_format('Y-m-d', $this->training_date_start, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                if ($end->getTimestamp() < $start->getTimestamp()) {
                    $this->addError($attribute, __('Tugash vaqati boshlanish vaqtidan katta bo\'lishi kerak'));
                }
            }
        }
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
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

        $query->with(['educationYear', 'trainingType', 'country']);

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
            $query->orWhereLike('e_employee_training.specialty_name', $this->search);
            $query->orWhereLike('e_employee_training.university', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        if ($this->_employee) {
            $query->andFilterWhere(['e_employee_training._employee' => $this->_employee]);
        }

        if ($this->_country) {
            $query->andFilterWhere(['e_employee_training._country' => $this->_country]);
        }

        if ($this->_training_type) {
            $query->andFilterWhere(['e_employee_training._training_type' => $this->_training_type]);
        }

        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf("%s / %s / %s", $this->trainingType->name, $this->country->name, $this->employee->getFullName());
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function checkToApi($updateIfDifferent = true)
    {
        $result = false;
        if (HEMIS_INTEGRATION) {
            $this->setAsShouldBeSynced();
            $result = EmployeeTrainingUpdater::checkModel($this, $updateIfDifferent);

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
            $result = EmployeeTrainingUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
