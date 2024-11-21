<?php

namespace common\models\student;

use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\sync\EmployeeForeignUpdater;
use common\components\hemis\sync\StudentSportUpdater;
use common\models\curriculum\EducationYear;
use common\models\student\EStudent;
use common\models\system\classifier\Country;
use common\models\system\classifier\SportType;
use DateTime;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_employee_meta".
 *
 * @property int $id
 * @property int $_education_year
 * @property int $_student
 * @property int $_sport_type
 * @property string $record_type
 * @property DateTime $sport_date
 * @property string $sport_rank
 * @property string $sport_rank_document
 *
 * @property EducationYear $educationYear
 * @property SportType $sportType
 * @property EStudent $student
 */
class EStudentSport extends HemisApiSyncModel
{
    const TYPE_RANK = 'rank';
    const TYPE_SECTION = 'section';

    public static function tableName()
    {
        return 'e_student_sport';
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_RANK => __('Sport Rank'),
            self::TYPE_SECTION => __('Sport Section'),
        ];
    }

    public function getTypeLabel()
    {
        return @self::getTypeOptions()[$this->record_type];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [[
                '_sport_type', '_student', '_education_year'
            ], 'required'],
            [['sport_rank', 'sport_rank_document', 'sport_date'], 'safe'],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_sport_type'], 'exist', 'skipOnError' => true, 'targetClass' => SportType::className(), 'targetAttribute' => ['_sport_type' => 'code']],
        ]);
    }

    public function getSportType()
    {
        return $this->hasOne(SportType::className(), ['code' => '_sport_type']);
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

        $query->with(['educationYear', 'sportType', 'student']);

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
            $query->orWhereLike('sport_rank_document', $this->search);
        }

        if ($this->_sport_type) {
            $query->andFilterWhere(['_sport_type' => $this->_sport_type]);
        }

        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return sprintf("%s / %s ", $this->student->getFullName(), $this->sportType->name);
    }

    public function beforeSave($insert)
    {
        if ($this->sport_date == null) $this->sport_date = null;

        return parent::beforeSave($insert);
    }

    public function checkToApi($updateIfDifferent = true)
    {
        $result = false;
        if (HEMIS_INTEGRATION) {
            $this->setAsShouldBeSynced();
            $result = StudentSportUpdater::checkModel($this, $updateIfDifferent);

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
            $result = StudentSportUpdater::updateModel($this);

            $this->setAsSyncPerformed();
        }

        return $result;
    }

}
