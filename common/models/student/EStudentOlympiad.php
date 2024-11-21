<?php

namespace common\models\student;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\EmployeeForeignUpdater;
use common\components\hemis\sync\StudentOlympiadUpdater;
use common\models\curriculum\EducationYear;
use common\models\student\EStudent;
use common\models\system\classifier\Country;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_employee_meta".
 *
 * @property int $id
 * @property int $_education_year
 * @property int $_country
 * @property int $_student
 * @property string $olympiad_type
 * @property string $olympiad_name
 * @property string $olympiad_section_name
 * @property string $olympiad_place
 * @property \DateTime $olympiad_date
 * @property string $diploma_serial
 * @property integer $diploma_number
 * @property integer $student_place
 * @property bool|null $active
 *
 * @property EducationYear $educationYear
 * @property Country $country
 * @property EStudent $student
 */
class EStudentOlympiad extends HemisApiSyncModel
{
    const TYPE_REPUBLIC = 'republic';
    const TYPE_INTERNATIONAL = 'international';

    public static function tableName()
    {
        return 'e_student_olympiad';
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_REPUBLIC => __('Republic Olympiad'),
            self::TYPE_INTERNATIONAL => __('International Olympiad'),
        ];
    }

    public function getTypeLabel()
    {
        return @self::getTypeOptions()[$this->olympiad_type];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [[
                'olympiad_name', 'olympiad_date',
                'olympiad_place', 'student_place', 'olympiad_type', 'diploma_serial',
                'diploma_number', '_student', '_education_year', '_country'], 'required'],
            ['olympiad_section_name', 'safe'],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
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

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()->joinWith(['student']);;

        $query->with(['educationYear', 'country', 'student']);

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
            $query->orWhereLike('e_student.first_name', $this->search);
            $query->orWhereLike('e_student.second_name', $this->search);
            $query->orWhereLike('e_student.third_name', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
            $query->orWhereLike('e_student.passport_number', $this->search);
            $query->orWhereLike('olympiad_name', $this->search);
            $query->orWhereLike('olympiad_section_name', $this->search);
        }

        if ($this->_country) {
            $query->andFilterWhere(['e_student_olympiad._country' => $this->_country]);
        }
        if ($this->olympiad_type) {
            $query->andFilterWhere(['olympiad_type' => $this->olympiad_type]);
        }

        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf("%s / %s / %s", $this->student->getFullName(), $this->country->name, $this->olympiad_name);
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
            $result = StudentOlympiadUpdater::checkModel($this, $updateIfDifferent);

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
            $result = StudentOlympiadUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
