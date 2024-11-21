<?php

namespace common\models\archive;

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationType;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "e_certificate_committee_member".
 *
 * @property int $id
 * @property string $name
 * @property int $_certificate_committee
 * @property string $work_place
 * @property string $position
 * @property string $role
 * @property bool|null $active
 *
 * @property ECertificateCommittee $certificateCommittee
 */
class ECertificateCommitteeMember extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    protected $_translatedAttributes = ['name'];
    public $_faculty;
    public $_department;

    public static function tableName()
    {
        return 'e_certificate_committee_member';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), ['name' => __('Member Name')]);
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    ['name', '_certificate_committee', 'work_place', 'position', 'role'],
                    'required',
                    'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]
                ],
                [['_certificate_committee'], 'integer'],
                [['active'], 'boolean'],
                //[['_specialty'], 'string', 'max' => 64],
                [['name'], 'string', 'max' => 256],
                [
                    ['_certificate_committee'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ECertificateCommittee::className(),
                    'targetAttribute' => ['_certificate_committee' => 'id']
                ],
                [['_faculty', '_department'], 'safe']
            ]
        );
    }

    public function getCertificateCommittee()
    {
        return $this->hasOne(ECertificateCommittee::className(), ['id' => '_certificate_committee']);
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find()->joinWith('certificateCommittee');
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['lesson_date' => SORT_ASC],
                    'attributes' => [
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );

        if ($this->_faculty) {
            $query->andFilterWhere(['e_certificate_committee._faculty' => $this->_faculty]);
        }

        if ($this->_department) {
            $ids = ECertificateCommittee::find()->select(['id', 'active', '_department'])->where(['active' => true, '_department' => $this->_department])->column();
            $query->andFilterWhere(['_certificate_committee' => $ids]);
        }

        if ($this->_certificate_committee) {
            $query->andFilterWhere(['_certificate_committee' => $this->_certificate_committee]);
        }

        return $dataProvider;
    }

}
