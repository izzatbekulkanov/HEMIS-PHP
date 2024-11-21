<?php

namespace common\models\science;

use common\components\hemis\HemisApiSyncModel;
use common\models\student\ESpecialty;
use common\models\system\_BaseModel;
use common\models\system\classifier\ScienceBranch;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "e_dissertation_defense".
 *
 * @property int $id
 * @property int $_doctorate_student
 * @property string $_science_branch_id
 * @property int $_specialty_id
 * @property string $defense_date
 * @property string $defense_place
 * @property string $diploma_given_by_whom
 * @property string $approved_date
 * @property string $diploma_number
 * @property string $diploma_given_date
 * @property string $register_number
 * @property string|null $filename
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EDoctorateStudent $doctorateStudent
 * @property ESpecialty $specialty
 * @property ScienceBranch $scienceBranch
 */
class EDissertationDefense extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    protected $_translatedAttributes = [];

    // public $diploma_given_by_whom = "";

    public static function tableName()
    {
        return 'e_dissertation_defense';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function findCurrentDissertationDefense($id)
    {
        return self::find()->where(['_doctorate_student' => $id])->one();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['defense_date', 'defense_place', 'approved_date', 'diploma_number', 'register_number', 'diploma_given_by_whom', 'scientific_council'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_doctorate_student', '_specialty_id', 'position'], 'default', 'value' => null],
            [['_doctorate_student', '_specialty_id', 'position'], 'integer'],
            [['defense_date', 'approved_date', 'diploma_given_date', 'filename', '_translations', 'updated_at', 'created_at'], 'safe'],
            [['active'], 'boolean'],
            [['_science_branch_id'], 'string', 'max' => 36],
            [['defense_place', 'diploma_given_by_whom'], 'string', 'max' => 500],
            [['scientific_council'], 'string', 'max' => 1000],
            [['diploma_number'], 'string', 'max' => 20],
            [['register_number'], 'string', 'max' => 30],
            [['register_number'], 'match', 'pattern' => '/^[\ 0-9\/]+$/i', 'message' => __('Qayd raqamiga faqat raqam kiritilsin')],

            [['_doctorate_student'], 'exist', 'skipOnError' => true, 'targetClass' => EDoctorateStudent::className(), 'targetAttribute' => ['_doctorate_student' => 'id']],
            [['_specialty_id'], 'exist', 'skipOnError' => true, 'targetClass' => ESpecialty::className(), 'targetAttribute' => ['_specialty_id' => 'id']],
            [['_science_branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScienceBranch::className(), 'targetAttribute' => ['_science_branch_id' => 'id']],
        ]);
    }

    public function getDoctorateStudent()
    {
        return $this->hasOne(EDoctorateStudent::className(), ['id' => '_doctorate_student']);
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty_id']);
    }

    public function getScienceBranch()
    {
        return $this->hasOne(ScienceBranch::className(), ['id' => '_science_branch_id']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
                'attributes' => [
                    'id',
                    '_doctorate_student',
                    'position',
                    '_science_branch_id',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);
        /*if ($this->search) {
            $query->orWhereLike('first_name', $this->search);
            $query->orWhereLike('second_name', $this->search);
            $query->orWhereLike('third_name', $this->search);
            $query->orWhereLike('passport_number', $this->search);
            $query->orWhereLike('passport_pin', $this->search);
            $query->orWhereLike('e_student.student_id_number', $this->search);
        }*/

        if ($this->_doctorate_student) {
            $query->andFilterWhere(['_doctorate_student' => $this->_doctorate_student]);
        }
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return $this->diploma_number . ' (' . $this->register_number . ')';
    }


}
