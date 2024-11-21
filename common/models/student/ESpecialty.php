<?php

namespace common\models\student;

use common\models\curriculum\ECurriculum;
use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\LocalityType;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\ScienceBranch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;


/**
 * This is the model class for table "e_specialty".
 *
 * @property string $code
 * @property string $name
 * @property string|null $parent_code
 * @property int|null $_department
 * @property string $_education_type
 * @property string|null $_knowledge_type
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 * @property string $_master_specialty
 * @property string $_bachelor_specialty
 *
 * @property ECurriculum[] $eCurriculums
 * @property EGroup[] $eGroups
 * @property EDepartment $department
 * @property EducationType $educationType
 * @property MasterSpeciality $masterSpecialty
 * @property BachelorSpeciality $bachelorSpecialty
 * @property BachelorSpeciality $mainSpecialty
 */
class ESpecialty extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_HIGHER = 'higher';
    const SCENARIO_HIGHER_DEAN = 'higher_dean';
    const SCENARIO_HIGHER_DOCTORATE = 'doctorate';

    protected $_translatedAttributes = ['name'];
    public $specialty_id;

    public static function tableName()
    {
        return 'e_specialty';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->active]) ? $labels[$this->active] : '';
    }

    public static function getHigherSpecialty($faculty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
                ->orderByTranslationField('name')
                ->all(), 'id', 'fullName');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE, '_department' => $faculty])
                ->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
                ->orderByTranslationField('name')
                ->all(), 'id', 'fullName');
        }
        return $result;
    }

    public static function getHigherSpecialtyByType($education_type = EducationType::EDUCATION_TYPE_BACHELOR, $faculty = "")
    {
        if ($faculty == "") {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE])
                ->andWhere(['_education_type' => $education_type])
                ->orderByTranslationField('name')
                ->all(), 'id', 'fullName');
        } else {
            $result = ArrayHelper::map(self::find()
                ->where(['active' => self::STATUS_ENABLE, '_department' => $faculty])
                ->andWhere(['_education_type' => $education_type])
                ->orderByTranslationField('name')
                ->all(), 'id', 'fullName');
        }
        return $result;
    }

    public static function getDoctorateSpecialtyList()
    {
        $result = ArrayHelper::map(self::find()
            ->where(['active' => self::STATUS_ENABLE])
            ->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_PHD, EducationType::EDUCATION_TYPE_DSC]])
            ->orderByTranslationField('name')
            ->all(), 'id', 'fullName');
        return $result;
    }

    public static function getSpecialtyName($code = false, $faculty = false)
    {
        $result = self::find()
            ->where(['active' => self::STATUS_ENABLE, 'id' => $code, '_department' => $faculty])
            ->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
            ->one();
        return $result;
    }

    public static function getSpecialtyExist($id = false, $education_type = false, $faculty = false)
    {
        if ($education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
            $result = self::find()
                ->where(['_bachelor_specialty' => $id, '_education_type'=>$education_type, '_department' => $faculty])
                ->count();

        } elseif ($education_type == EducationType::EDUCATION_TYPE_MASTER) {
            $result = self::find()
                ->where(['_master_specialty' => $id, '_education_type'=>$education_type, '_department' => $faculty ])
                ->count();
        } elseif ($education_type == EducationType::EDUCATION_TYPE_PHD) {
            $result = self::find()
                ->where(['_doctorate_specialty' => $id, '_education_type'=>$education_type])
                ->count();
        }
        return $result;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_education_type', '_department', '_type', 'specialty_id'], 'required', 'on' => self::SCENARIO_HIGHER],
            [['_type', 'specialty_id'], 'required', 'on' => self::SCENARIO_HIGHER_DOCTORATE],
            [['_education_type', '_type'], 'required', 'on' => self::SCENARIO_HIGHER_DEAN],
            [['_department', 'position'], 'default', 'value' => null],
            [['_department', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['code', 'parent_code', '_education_type', '_knowledge_type', '_type'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['specialty_id', '_doctorate_specialty'], 'safe'],
            //[['code', '_department'], 'unique', 'targetAttribute' => ['code', '_department']],

            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id']],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_type'], 'exist', 'skipOnError' => true, 'targetClass' => LocalityType::className(), 'targetAttribute' => ['_type' => 'code']],
        ]);
    }

    public function afterFind()
    {
        if ($this->hasAttribute('_bachelor_specialty')) {
            $this->specialty_id = $this->_bachelor_specialty ?: $this->_master_specialty;
            if ($this->specialty_id == null) {
                if ($this->_education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
                    if ($specialty = BachelorSpeciality::findOne(['code' => $this->code])) {
                        $this->specialty_id = $specialty->id;
                    }
                } elseif ($this->_education_type == EducationType::EDUCATION_TYPE_MASTER) {
                    if ($specialty = MasterSpeciality::findOne(['code' => $this->code])) {
                        $this->specialty_id = $specialty->id;
                    }
                } elseif ($this->_education_type == EducationType::EDUCATION_TYPE_PHD) {
                    if ($specialty = ScienceBranch::findOne(['code' => $this->code])) {
                        $this->specialty_id = $specialty->id;
                    }
                }
            }
        }
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if ($this->_education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
            if ($specialty = BachelorSpeciality::findOne($this->specialty_id)) {
                $this->_bachelor_specialty = $specialty->id;
                $this->name = $specialty->name;
                $this->code = $specialty->code;
            }
        } elseif ($this->_education_type == EducationType::EDUCATION_TYPE_MASTER) {
            if ($specialty = MasterSpeciality::findOne($this->specialty_id)) {
                $this->_master_specialty = $specialty->id;
                $this->name = $specialty->name;
                $this->code = $specialty->code;
            }
        } else {
            if ($specialty = ScienceBranch::findOne($this->specialty_id)) {
                $this->_doctorate_specialty = $specialty->id;
                $this->name = $specialty->name;
                $this->code = $specialty->code;
                $this->_education_type = EducationType::EDUCATION_TYPE_PHD;
            }
        }


        return parent::beforeSave($insert);
    }

    public function getECurriculums()
    {
        return $this->hasMany(ECurriculum::className(), ['_specialty_id' => 'id']);
    }

    public function getEGroups()
    {
        return $this->hasMany(EGroup::className(), ['_specialty_id' => 'id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getMasterSpecialty()
    {
        return $this->hasOne(MasterSpeciality::className(), ['id' => '_master_specialty']);
    }

    public function getBachelorSpecialty()
    {
        return $this->hasOne(BachelorSpeciality::className(), ['id' => '_bachelor_specialty']);
    }

    public function getDoctorateSpecialty()
    {
        return $this->hasOne(ScienceBranch::className(), ['id' => '_doctorate_specialty']);
    }

    public function getMainSpecialty()
    {
        if ($this->_education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
            return $this->getBachelorSpecialty();
        }
        else if ($this->_education_type == EducationType::EDUCATION_TYPE_MASTER) {
            return $this->getMasterSpecialty();
        }
        return $this->getDoctorateSpecialty();
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getFullName()
    {
        return $this->code . ' - ' . ($this->mainSpecialty? $this->mainSpecialty->name : $this->name);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_department' => __('Faculty'),
            'specialty_id' => __('Specialty'),
        ]);
    }

    public function getLocalityType()
    {
        return $this->hasOne(LocalityType::className(), ['code' => '_type']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'position',
                    '_department',
                    '_education_type',
                    // '_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
            $query->orWhereLike('name', $this->search);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }
        $query->andFilterWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]]);
        /*if ($this->_type) {
            $query->andFilterWhere(['_type' => $this->_type]);
        }*/
        return $dataProvider;
    }

    public function search_doctorate($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['code' => SORT_ASC],
                'attributes' => [
                    'name',
                    'code',
                    'position',
                    '_department',
                    '_education_type',
                    // '_type',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('code', $this->search);
            $query->orWhereLike('name', $this->search);
        }
        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        $query->andFilterWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_PHD, EducationType::EDUCATION_TYPE_DSC]]);

        /*if ($this->_type) {
            $query->andFilterWhere(['_type' => $this->_type]);
        }*/
        return $dataProvider;
    }
}