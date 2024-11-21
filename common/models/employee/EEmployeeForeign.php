<?php

namespace common\models\employee;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\EmployeeForeignUpdater;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\Country;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_employee_meta".
 *
 * @property int $id
 * @property int $_education_year
 * @property int $_country
 * @property string $full_name
 * @property string $contract_data
 * @property string $specialty_name
 * @property string $work_place
 * @property string $subject
 * @property bool|null $active
 *
 * @property EducationYear $educationYear
 * @property Country $country
 */
class EEmployeeForeign extends HemisApiSyncModel
{
    public static function tableName()
    {
        return 'e_employee_foreign';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['full_name', 'contract_data', '_education_year',
                '_country', 'work_place', 'specialty_name', 'subject'], 'required'],

            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
        ]);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $query->with(['educationYear', 'country']);

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
            $query->orWhereLike('full_name', $this->search);
            $query->orWhereLike('work_place', $this->search);
            $query->orWhereLike('specialty_name', $this->search);
            $query->orWhereLike('subject', $this->search);
        }

        if ($this->_country) {
            $query->andFilterWhere(['_country' => $this->_country]);
        }

        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf("%s / %s / %s", $this->full_name, $this->country->name, $this->subject);
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
            $result = EmployeeForeignUpdater::checkModel($this, $updateIfDifferent);

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
            $result = EmployeeForeignUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
