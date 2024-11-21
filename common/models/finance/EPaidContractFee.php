<?php

namespace common\models\finance;

use common\models\curriculum\EducationYear;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\AdminRole;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_paid_contract_fee".
 *
 * @property int $id
 * @property int|null $_student_contract
 * @property string|null $_education_year
 * @property int $_student
 * @property string|null $payment_number
 * @property string $payment_date
 * @property string|null $payment_type
 * @property float $summa
 * @property int|null $position
 * @property bool|null $active
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEducationYear $educationYear
 * @property EStudent $student
 * @property EStudentContract $studentContract
 */
class EPaidContractFee extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';

    public static function tableName()
    {
        return 'e_paid_contract_fee';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_student_contract', '_student', 'position'], 'default', 'value' => null],
            [['_student_contract', '_student', 'position'], 'integer'],
            [['payment_number', 'payment_date', 'summa', 'payment_comment'], 'required', 'on' => self::SCENARIO_CREATE],
            [['payment_date', 'payment_type', 'updated_at', 'created_at'], 'safe'],
            [['summa'], 'number'],
            [['active'], 'boolean'],
            [['_education_year'], 'string', 'max' => 64],
            [['payment_number', 'payment_comment'], 'string', 'max' => 255],
            ['summa', 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number'],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_student'], 'exist', 'skipOnError' => true, 'targetClass' => EStudent::className(), 'targetAttribute' => ['_student' => 'id']],
            [['_student_contract'], 'exist', 'skipOnError' => true, 'targetClass' => EStudentContract::className(), 'targetAttribute' => ['_student_contract' => 'id']],
        ]);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getStudentContract()
    {
        return $this->hasOne(EStudentContract::className(), ['id' => '_student_contract']);
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += @$item[@$fieldName];
        }

        return @$total;
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'payment_number',
                    'payment_date',
                    'summa',
                    '_student_contract',
                    '_education_year',
                    '_student',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->_student_contract) {
            $query->andFilterWhere(['_student_contract' => $this->_student_contract]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        if ($this->_student) {
            $query->andFilterWhere(['_student' => $this->_student]);
        }
        return $dataProvider;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $contract = $this->studentContract;
      //  $contract = EStudentContract::findOne(['id' => $this->_student_contract]);
        if($contract !== null){
            $contract->different = $contract->summa - EStudentContract::getTotal($contract->paidContractFee, 'summa');
            if($contract->different > 0)
                $contract->different_status = EStudentContract::DIFFERENT_DEBTOR_STATUS;
            elseif($contract->different == 0)
                $contract->different_status = EStudentContract::DIFFERENT_EQUAL_STATUS;
            else
                $contract->different_status = EStudentContract::DIFFERENT_NOT_DEBTOR_STATUS;
            if ($contract->save()) {
                return $contract;
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /*public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $contract = $this->studentContract;
//        $contract = EStudentContract::findOne($this->_student_contract);
        if ($contract !== null) {
            $contract->different = $contract->summa - EStudentContract::getTotal($contract->paidContractFee, 'summa');
            if($contract->different > 0)
                $contract->different_status = EStudentContract::DIFFERENT_DEBTOR_STATUS;
            elseif($contract->different == 0)
                $contract->different_status = EStudentContract::DIFFERENT_EQUAL_STATUS;
            else
                $contract->different_status = EStudentContract::DIFFERENT_NOT_DEBTOR_STATUS;
            $contract->save(false);

        }
        return parent::beforeDelete();
    }*/
}
