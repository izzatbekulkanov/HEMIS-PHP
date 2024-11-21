<?php

namespace common\models\science;


use common\components\hemis\HemisApiSyncModel;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
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
 * This is the model class for table "e_publication_author_meta".
 *
 * @property int $id
 * @property int $_employee
 * @property string $_publication_type_table
 * @property string $_uid
 * @property int|null $_publication_methodical
 * @property int|null $_publication_scientific
 * @property int|null $_publication_property
 * @property int|null $is_checked_by_author
 * @property int|null $is_main_author
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EEmployee $employee
 * @property EPublicationMethodical $publicationMethodical
 * @property EPublicationProperty $publicationProperty
 * @property EPublicationScientific $publicationScientific
 */
class EPublicationAuthorMeta extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_AUTHOR = 'create_author';
    const PUBLICATION_TYPE_METHODICAL = 11;
    const PUBLICATION_TYPE_SCIENTIFIC = 12;
    const PUBLICATION_TYPE_PROPERTY = 13;
    const APPROVED_BY_AUTHOR_ENABLE = true;
    const APPROVED_BY_AUTHOR_DISABLE = false;

    protected $_translatedAttributes = [];
    public $_education_year;
    public $_methodical_publication_type;
    public $_scientific_publication_type;
    public $_patient_type;
    public $is_checked;
    public $methodical_publication_types;
    public $scientific_publication_types;
    public $patient_types;
    public $_publication_database;
    public $certificate_number;
    public $work_name;

    public static function tableName()
    {
        return 'e_publication_author_meta';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getAprovedAuthorOptions()
    {
        return [
            self::APPROVED_BY_AUTHOR_ENABLE => __('AUTHOR IS APPROVED'),
            self::APPROVED_BY_AUTHOR_DISABLE => __('AUTHOR IS NOT APPROVED'),
        ];
    }

    public static function getPublicationTypeOptions()
    {
        return [
            self::PUBLICATION_TYPE_METHODICAL => __('Methodical Publication'),
            self::PUBLICATION_TYPE_SCIENTIFIC => __('Scientific Publication'),
            self::PUBLICATION_TYPE_PROPERTY => __('Property Publication'),
        ];
    }

    public static function getMainAuthor($publication_type_table = false, $publication = false, $employee = false)
    {
        if ($publication_type_table === self::PUBLICATION_TYPE_METHODICAL) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_publication_methodical' => $publication,
                    '_employee' => $employee,
                    'is_main_author' => 1,
                ])
                ->one();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_SCIENTIFIC) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_publication_scientific' => $publication,
                    '_employee' => $employee,
                    'is_main_author' => 1,
                ])
                ->one();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_PROPERTY) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_publication_property' => $publication,
                    '_employee' => $employee,
                    'is_main_author' => 1,
                ])
                ->one();
        }
    }

    public static function getSelectedPublication($publication_type_table = false, $employee = false)
    {
        if ($publication_type_table === self::PUBLICATION_TYPE_METHODICAL) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_employee' => $employee,
                ])
                ->all();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_SCIENTIFIC) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_employee' => $employee,
                ])
                ->all();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_PROPERTY) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_employee' => $employee,
                ])
                ->all();
        }
    }

    public static function getAuthorRequest($publication_type_table = false, $publication = false)
    {
        if ($publication_type_table === self::PUBLICATION_TYPE_METHODICAL) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_publication_methodical' => $publication,
                    'is_checked_by_author' => self::STATUS_DISABLE,
                ])
                ->count();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_SCIENTIFIC) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_publication_scientific' => $publication,
                    'is_checked_by_author' => self::STATUS_DISABLE,
                ])
                ->count();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_PROPERTY) {
            return self::find()
                ->where([
                    '_publication_type_table' => $publication_type_table,
                    '_publication_property' => $publication,
                    'is_checked_by_author' => self::STATUS_DISABLE,
                ])
                ->count();
        }
    }

    public static function getCheckedPublication($publication_type_table = false, $education_year = false, $faculty = "", $department = "", $teacher = "")
    {
        if ($faculty != "" && $department == "") {
            $department_list = EDepartment::getDepartmentList($faculty);
            $departments = array();
            foreach ($department_list as $item) {
                $departments[$item->id] = $item->id;
            }
            $teacher_list = EEmployeeMeta::getTeacherList($departments);
            $teachers = array();
            foreach ($teacher_list as $item) {
                $teachers[$item->_employee] = $item->_employee;
            }
        } elseif ($department != "") {
            $departments = $department;
            $teacher_list = EEmployeeMeta::getTeacherList($departments);
            $teachers = array();
            foreach ($teacher_list as $item) {
                $teachers[$item->_employee] = $item->_employee;
            }
        } elseif ($teacher != "") {
            $teachers = $teacher;
        }

        if ($publication_type_table === self::PUBLICATION_TYPE_METHODICAL) {
            if ($faculty != "" || $department != "" || $teacher != "") {
                return self::find()
                    ->select('e_publication_author_meta._employee, e_publication_methodical._methodical_publication_type as _methodical_publication_type, COUNT(e_publication_methodical._methodical_publication_type) as methodical_publication_types, e_publication_methodical.certificate_number')
                    ->joinWith(['publicationMethodical'])
                    ->where([
                        'e_publication_methodical.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_methodical._education_year' => $education_year,
                    ])
                    ->andWhere(['in', 'e_publication_author_meta._employee', $teachers])
                    ->groupBy(['e_publication_author_meta._employee', 'e_publication_methodical._methodical_publication_type', 'e_publication_methodical.certificate_number'])
                    ->all();
            } else {
                return self::find()
                    ->select('e_publication_author_meta._employee, e_publication_methodical._methodical_publication_type as _methodical_publication_type, COUNT(e_publication_methodical._methodical_publication_type) as methodical_publication_types, e_publication_methodical.certificate_number')
                    ->joinWith(['publicationMethodical'])
                    ->where([
                        'e_publication_methodical.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_methodical._education_year' => $education_year,
                    ])
                    ->groupBy(['e_publication_author_meta._employee', 'e_publication_methodical._methodical_publication_type', 'e_publication_methodical.certificate_number'])
                    ->all();
            }
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_SCIENTIFIC) {
            if ($faculty != "" || $department != "" || $teacher != "") {
                return self::find()
                    ->select('e_publication_author_meta._employee, e_publication_scientific._scientific_publication_type, e_publication_scientific._publication_database, COUNT(e_publication_scientific._scientific_publication_type) as scientific_publication_types')
                    ->joinWith(['publicationScientific'])
                    ->where([
                        'e_publication_scientific.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_scientific._education_year' => $education_year,
                    ])
                    ->andWhere(['in', 'e_publication_author_meta._employee', $teachers])
                    ->groupBy(['e_publication_author_meta._employee', 'e_publication_scientific._scientific_publication_type', 'e_publication_scientific._publication_database'])
                    ->all();
            } else {
                return self::find()
                    ->select('e_publication_author_meta._employee, e_publication_scientific._scientific_publication_type, e_publication_scientific._publication_database, COUNT(e_publication_scientific._scientific_publication_type) as scientific_publication_types')
                    ->joinWith(['publicationScientific'])
                    ->where([
                        'e_publication_scientific.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_scientific._education_year' => $education_year,
                    ])
                    ->groupBy(['e_publication_author_meta._employee', 'e_publication_scientific._scientific_publication_type', 'e_publication_scientific._publication_database'])
                    ->all();
            }
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_PROPERTY) {
            if ($faculty != "" || $department != "" || $teacher != "") {
                return self::find()
                    ->select('e_publication_author_meta._employee, e_publication_property._patient_type, COUNT(e_publication_property._patient_type) as patient_types')
                    ->joinWith(['publicationProperty'])
                    ->where([
                        'e_publication_property.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_property._education_year' => $education_year,
                    ])
                    ->andWhere(['in', 'e_publication_author_meta._employee', $teachers])
                    ->groupBy(['e_publication_author_meta._employee', 'e_publication_property._patient_type'])
                    ->all();;
            } else {
                return self::find()
                    ->select('e_publication_author_meta._employee, e_publication_property._patient_type, COUNT(e_publication_property._patient_type) as patient_types')
                    ->joinWith(['publicationProperty'])
                    ->where([
                        'e_publication_property.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                        'e_publication_property._education_year' => $education_year,
                    ])
                    ->groupBy(['e_publication_author_meta._employee', 'e_publication_property._patient_type'])
                    ->all();
            }
        }
    }

    public static function getEmployeeCheckedPublicationList($publication_type_table = false, $education_year = false, $teacher = "")
    {
        if ($publication_type_table === self::PUBLICATION_TYPE_METHODICAL) {
            return self::find()
                ->select('e_publication_author_meta.id, e_publication_author_meta._employee, e_publication_methodical.name as work_name, e_publication_methodical._methodical_publication_type as _methodical_publication_type, e_publication_methodical.certificate_number')
                ->joinWith(['publicationMethodical'])
                ->where([
                    'e_publication_methodical.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_methodical._education_year' => $education_year,
                ])
                ->andWhere(['in', 'e_publication_author_meta._employee', $teacher])
                ->all();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_SCIENTIFIC) {
            return self::find()
                ->select('e_publication_author_meta.id, e_publication_author_meta._employee, e_publication_scientific.name as work_name, e_publication_scientific._scientific_publication_type, e_publication_scientific._publication_database')
                ->joinWith(['publicationScientific'])
                ->where([
                    'e_publication_scientific.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_author_meta.is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_scientific._education_year' => $education_year,
                ])
                ->andWhere(['in', 'e_publication_author_meta._employee', $teacher])
                ->all();
        } elseif ($publication_type_table === self::PUBLICATION_TYPE_PROPERTY) {
            return self::find()
                ->select('e_publication_author_meta.id, e_publication_author_meta._employee, e_publication_property.name as work_name, e_publication_property._patient_type')
                ->joinWith(['publicationProperty'])
                ->where([
                    'e_publication_property.is_checked' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'is_checked_by_author' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_author_meta.active' => EPublicationAuthorMeta::STATUS_ENABLE,
                    'e_publication_property._education_year' => $education_year,
                ])
                ->andWhere(['in', 'e_publication_author_meta._employee', $teacher])
                ->all();
        }
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += @$item[@$fieldName];
        }

        return @$total;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_employee', '_publication_type_table'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_employee', '_publication_methodical', '_publication_scientific', '_publication_property', 'is_checked_by_author', 'position'], 'default', 'value' => null],
            [['_employee', '_publication_methodical', '_publication_scientific', '_publication_property', 'is_main_author', 'is_checked_by_author', 'position'], 'integer'],
            [['active', 'is_checked'], 'boolean'],
            [['_translations', 'updated_at', 'created_at', '_education_year', '_methodical_publication_type', '_scientific_publication_type', '_patient_type', 'is_checked'], 'safe'],
            [['_publication_type_table'], 'string', 'max' => 64],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_publication_methodical'], 'exist', 'skipOnError' => true, 'targetClass' => EPublicationMethodical::className(), 'targetAttribute' => ['_publication_methodical' => 'id']],
            [['_publication_property'], 'exist', 'skipOnError' => true, 'targetClass' => EPublicationProperty::className(), 'targetAttribute' => ['_publication_property' => 'id']],
            [['_publication_scientific'], 'exist', 'skipOnError' => true, 'targetClass' => EPublicationScientific::className(), 'targetAttribute' => ['_publication_scientific' => 'id']],
        ]);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getPublicationMethodical()
    {
        return $this->hasOne(EPublicationMethodical::className(), ['id' => '_publication_methodical']);
    }

    public function getPublicationProperty()
    {
        return $this->hasOne(EPublicationProperty::className(), ['id' => '_publication_property']);
    }

    public function getPublicationScientific()
    {
        return $this->hasOne(EPublicationScientific::className(), ['id' => '_publication_scientific']);
    }

    public function search_methodical($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['publicationMethodical']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'e_publication_methodical.name',
                    'e_publication_methodical.authors',
                    'e_publication_methodical.publisher',
                    'e_publication_methodical.issue_year',
                    'e_publication_methodical._methodical_publication_type',
                    'e_publication_methodical._publication_database',
                    'e_publication_methodical._education_year',
                    'e_publication_author_meta._employee',
                    '_publication_methodical',
                    'e_publication_methodical.is_checked as is_checked',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_methodical.name_uz', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name_oz', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name_ru', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name', $this->search);
            $query->orWhereLike('e_publication_methodical.authors', $this->search);
        }
        $query->andFilterWhere(['_publication_type_table' => self::PUBLICATION_TYPE_METHODICAL]);

        if ($this->_education_year) {
            $query->andFilterWhere(['e_publication_methodical._education_year' => $this->_education_year]);
        }
        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function search_scientifical($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['publicationScientific']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'e_publication_scientific.name',
                    'e_publication_scientific.authors',
                    'e_publication_scientific.publisher',
                    'e_publication_scientific.issue_year',
                    'e_publication_scientific._scientific_publication_type',
                    'e_publication_scientific._publication_database',
                    'e_publication_scientific._education_year',
                    'e_publication_author_meta._employee',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_scientific.name_uz', $this->search, 'e_publication_scientific._translations');
            $query->orWhereLike('e_publication_scientific.name_oz', $this->search, 'e_publication_scientific._translations');
            $query->orWhereLike('e_publication_scientific.name_ru', $this->search, 'e_publication_scientific._translations');
            $query->orWhereLike('e_publication_scientific.name', $this->search);
            $query->orWhereLike('e_publication_scientific.authors', $this->search);
        }
        $query->andFilterWhere(['_publication_type_table' => self::PUBLICATION_TYPE_SCIENTIFIC]);
        if ($this->_education_year) {
            $query->andFilterWhere(['e_publication_scientific._education_year' => $this->_education_year]);
        }
        /*if ($this->_methodical_publication_type) {
            $query->andFilterWhere(['e_publication_methodical._methodical_publication_type' => $this->_methodical_publication_type]);
        }*/
        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function search_property($params)
    {
        $this->load($params);

        $query = self::find();
        $query->joinWith(['publicationProperty']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'e_publication_property.name',
                    'e_publication_property.authors',
                    'e_publication_property.publisher',
                    'e_publication_property.issue_year',
                    'e_publication_property._patient_type',
                    'e_publication_property._publication_database',
                    'e_publication_property._education_year',
                    'e_publication_author_meta._employee',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_property.name_uz', $this->search, 'e_publication_property._translations');
            $query->orWhereLike('e_publication_property.name_oz', $this->search, 'e_publication_property._translations');
            $query->orWhereLike('e_publication_property.name_ru', $this->search, 'e_publication_property._translations');
            $query->orWhereLike('e_publication_property.name', $this->search);
            $query->orWhereLike('e_publication_property.authors', $this->search);
        }
        $query->andFilterWhere(['_publication_type_table' => self::PUBLICATION_TYPE_PROPERTY]);
        if ($this->_education_year) {
            $query->andFilterWhere(['e_publication_property._education_year' => $this->_education_year]);
        }
        /*if ($this->_methodical_publication_type) {
            $query->andFilterWhere(['e_publication_methodical._methodical_publication_type' => $this->_methodical_publication_type]);
        }*/
        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function search_for_request($params)
    {
        $this->load($params);

        $query = self::find()
            ->andWhere(['!=', 'e_publication_author_meta._employee', Yii::$app->user->identity->_employee]);

        $query->joinWith(['publicationMethodical']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //'e_publication_methodical',
                    'e_publication_methodical.name',
                    'e_publication_methodical.authors',
                    'e_publication_methodical.publisher',
                    'e_publication_methodical.issue_year',
                    'e_publication_methodical._methodical_publication_type',
                    'e_publication_methodical._publication_database',
                    'e_publication_author_meta._employee',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_methodical.name_uz', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name_oz', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name_ru', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name', $this->search);
            $query->orWhereLike('e_publication_methodical.authors', $this->search);
        }

        /*if ($this->_methodical_publication_type) {
            $query->andFilterWhere(['e_publication_methodical._methodical_publication_type' => $this->_methodical_publication_type]);
        }*/
        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function searchForMethodical(EPublicationMethodical $methodical)
    {
        $query = self::find()
            ->andFilterWhere(['_publication_methodical' => $methodical->id])
            ->joinWith(['publicationMethodical']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        return $dataProvider;
    }

    public function searchForScientifical(EPublicationScientific $scientific)
    {
        $query = self::find()
            ->andFilterWhere(['_publication_scientific' => $scientific->id])
            ->joinWith(['publicationScientific']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        return $dataProvider;
    }

    public function searchForProperty(EPublicationProperty $scientific)
    {
        $query = self::find()
            ->andFilterWhere(['_publication_property' => $scientific->id])
            ->joinWith(['publicationProperty']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        return $dataProvider;
    }

    public function search_methodical_department($params, $department)
    {
        $this->load($params);
        $list = array();
        $teachers = EEmployeeMeta::getTeacherList($department);
        foreach ($teachers as $item) {
            $list [$item->_employee] = $item->_employee;
        }
        $query = self::find();
        $query->joinWith(['publicationMethodical']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'e_publication_methodical.name',
                    'e_publication_methodical.authors',
                    'e_publication_methodical.publisher',
                    'e_publication_methodical.issue_year',
                    'e_publication_methodical._methodical_publication_type',
                    'e_publication_methodical._publication_database',
                    'e_publication_author_meta._employee',
                    'e_publication_author_meta._education_year',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_methodical.name_uz', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name_oz', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name_ru', $this->search, 'e_publication_methodical._translations');
            $query->orWhereLike('e_publication_methodical.name', $this->search);
            $query->orWhereLike('e_publication_methodical.authors', $this->search);
        }
        $query->andFilterWhere(['_publication_type_table' => self::PUBLICATION_TYPE_METHODICAL]);
        $query->andFilterWhere(['in', 'e_publication_author_meta._employee', $list]);

        if ($this->_education_year) {
            $query->andFilterWhere(['e_publication_methodical._education_year' => $this->_education_year]);
        }
        if ($this->_methodical_publication_type) {
            $query->andFilterWhere(['e_publication_methodical._methodical_publication_type' => $this->_methodical_publication_type]);
        }

        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function search_scientifical_department($params, $department)
    {
        $this->load($params);
        $list = array();
        $teachers = EEmployeeMeta::getTeacherList($department);
        foreach ($teachers as $item) {
            $list [$item->_employee] = $item->_employee;
        }
        $query = self::find();
        $query->joinWith(['publicationScientific']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'e_publication_scientific.name',
                    'e_publication_scientific.authors',
                    'e_publication_scientific.publisher',
                    'e_publication_scientific.issue_year',
                    'e_publication_scientific._scientific_publication_type',
                    'e_publication_scientific._publication_database',
                    'e_publication_author_meta._employee',
                    'e_publication_scientific._education_year',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_scientific.name_uz', $this->search, '_translations');
            $query->orWhereLike('e_publication_scientific.name_oz', $this->search, '_translations');
            $query->orWhereLike('e_publication_scientific.name_ru', $this->search, '_translations');
            $query->orWhereLike('e_publication_scientific.name', $this->search);
            $query->orWhereLike('e_publication_scientific.authors', $this->search);
        }
        $query->andFilterWhere(['_publication_type_table' => self::PUBLICATION_TYPE_SCIENTIFIC]);
        $query->andFilterWhere(['in', 'e_publication_author_meta._employee', $list]);

        if ($this->_education_year) {
            $query->andFilterWhere(['e_publication_scientific._education_year' => $this->_education_year]);
        }
        if ($this->_scientific_publication_type) {
            $query->andFilterWhere(['e_publication_scientific._scientific_publication_type' => $this->_scientific_publication_type]);
        }
        /*if ($this->_methodical_publication_type) {
            $query->andFilterWhere(['e_publication_methodical._methodical_publication_type' => $this->_methodical_publication_type]);
        }*/
        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function search_property_department($params, $department)
    {
        $this->load($params);
        $list = array();
        $teachers = EEmployeeMeta::getTeacherList($department);
        foreach ($teachers as $item) {
            $list [$item->_employee] = $item->_employee;
        }
        $query = self::find();
        $query->joinWith(['publicationProperty']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    //   'name',
                    'e_publication_property.name',
                    'e_publication_property.authors',
                    'e_publication_property.publisher',
                    'e_publication_property.issue_year',
                    'e_publication_property._patient_type',
                    'e_publication_property._publication_database',
                    'e_publication_author_meta._employee',
                    'e_publication_property._education_year',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('e_publication_property.name_uz', $this->search, '_translations');
            $query->orWhereLike('e_publication_property.name_oz', $this->search, '_translations');
            $query->orWhereLike('e_publication_property.name_ru', $this->search, '_translations');
            $query->orWhereLike('e_publication_property.name', $this->search);
            $query->orWhereLike('e_publication_property.authors', $this->search);
        }
        $query->andFilterWhere(['_publication_type_table' => self::PUBLICATION_TYPE_PROPERTY]);
        $query->andFilterWhere(['in', 'e_publication_author_meta._employee', $list]);

        if ($this->_education_year) {
            $query->andFilterWhere(['e_publication_property._education_year' => $this->_education_year]);
        }
        if ($this->_patient_type) {
            $query->andFilterWhere(['e_publication_property._patient_type' => $this->_patient_type]);
        }

        /*if ($this->_methodical_publication_type) {
            $query->andFilterWhere(['e_publication_methodical._methodical_publication_type' => $this->_methodical_publication_type]);
        }*/
        /* if ($this->_employee) {
             $query->andFilterWhere(['e_publication_methodical._employee' => $this->_employee]);
         }*/
        /*if ($this->_publication_database) {
            $query->andFilterWhere(['e_publication_methodical._publication_database' => $this->_publication_database]);
        }*/
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        return $this->employee->getShortName();
    }
}
