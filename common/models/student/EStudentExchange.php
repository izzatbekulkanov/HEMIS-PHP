<?php

namespace common\models\student;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\StudentExchangeUpdater;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\Country;
use common\models\system\classifier\EducationType;
use yii\data\ActiveDataProvider;

/**
 *
 * @property int $id
 * @property int $_education_year
 * @property int $_education_type
 * @property int $_country
 * @property string $full_name
 * @property string $exchange_document
 * @property string $specialty_name
 * @property string $university
 * @property string $exchange_type
 * @property bool|null $active
 *
 * @property EducationYear $educationYear
 * @property EducationType $educationType
 * @property Country $country
 */
class EStudentExchange extends HemisApiSyncModel
{
    const TYPE_INCOME = 'income';
    const TYPE_OUTGOING = 'outgoing';

    public static function tableName()
    {
        return 'e_student_exchange';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['full_name', '_education_type', '_education_year',
                '_country', 'university', 'specialty_name', 'exchange_document', 'exchange_type'], 'required'],

            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['_country' => 'code']],
        ]);
    }

    public static function getExchangeTypeOptions()
    {
        return [
            self::TYPE_INCOME => __('Income Exchange'),
            self::TYPE_OUTGOING => __('Outgoing Exchange'),
        ];
    }

    public function getExchangeTypeLabel()
    {
        return @self::getExchangeTypeOptions()[$this->exchange_type];
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => '_country']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $query->with(['educationYear', 'country', 'educationType']);

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
            $query->orWhereLike('university', $this->search);
            $query->orWhereLike('specialty_name', $this->search);
            $query->orWhereLike('exchange_document', $this->search);
        }

        if ($this->_country) {
            $query->andFilterWhere(['_country' => $this->_country]);
        }

        if ($this->exchange_type) {
            $query->andFilterWhere(['exchange_type' => $this->exchange_type]);
        }

        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf("%s / %s / %s", $this->getExchangeTypeLabel(), $this->full_name, $this->country->name);
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
            $result = StudentExchangeUpdater::checkModel($this, $updateIfDifferent);

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
            $result = StudentExchangeUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
