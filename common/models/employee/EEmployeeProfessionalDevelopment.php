<?php

namespace common\models\employee;

use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\Qualification;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\TeacherStatus;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_employee_professional_development".
 *
 * @property int $id
 * @property string $_employee
 * @property string $_employee_position
 * @property string $training_title
 * @property string $training_year
 * @property string $_training_place
 * @property string $begin_date
 * @property string $end_date
 * @property string $document
 * @property bool $active
 * @property string|null $_translations
 */
class EEmployeeProfessionalDevelopment extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    public $_faculty;
    public $_department;
    public $months;

    public static function tableName()
    {
        return 'e_employee_professional_development';
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

    public static function getEmployees()
    {
        return ArrayHelper::map(
            self::find()
                ->where(['active' => self::STATUS_ENABLE])
                //->orderByTranslationField('name')
                ->all(),
            'id',
            'fullName'
        );
    }

    public function rules()
    {
        $rules = array_merge(
            parent::rules(),
            [
                [
                    [
                        '_employee',
                        '_employee_position',
                        'training_title',
                        'training_year',
                        '_training_place',
                        'begin_date',
                        'end_date',
                        'document',
                    ],
                    'required',
                    'on' => self::SCENARIO_INSERT,
                ],
                [['active'], 'boolean'],

                [['begin_date', 'end_date'], 'date', 'format' => 'yyyy-mm-dd'],

                [['document', 'training_title'], 'string', 'max' => 1024],

                [
                    ['_employee'],
                    'exist',
                    'targetClass' => EEmployee::className(),
                    'targetAttribute' => ['_employee' => 'id'],
                ],
                [
                    ['_employee_position'],
                    'in',
                    'range' => array_keys(TeacherPositionType::getTeacherOptions()),
                ],
                [
                    ['_training_place'],
                    'exist',
                    'targetClass' => Qualification::class,
                    'targetAttribute' => ['_training_place' => 'code'],
                ],
                [['_faculty', '_department', 'training_year'], 'safe', 'on' => 'search'],
            ]
        );

        return $rules;
    }

    /*
        public function attributeLabels()
        {
            return array_merge(
                parent::attributeLabels(),
                [
                    'year_of_enter' => __('Ishga kirgan yili'),
                ]
            );
        }*/

    public static function getYearOfEnterOptions()
    {
        $years = [];

        for ($i = date('Y'); $i > (date('Y') - 20); $i--) {
            $years [$i] = $i;
        }

        return $years;
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getTrainingPlace()
    {
        return $this->hasOne(Qualification::className(), ['code' => '_training_place']);
    }

    public function getEmployeePosition()
    {
        return $this->hasOne(TeacherPositionType::className(), ['code' => '_employee_position']);
    }

    public function getDepartments()
    {
        return $this->hasMany(EDepartment::className(), ['id' => '_department'])
                    ->viaTable('e_employee_meta', ['_employee' => 'id'])->with(['structureType']);
    }

    public function getHeadDepartments()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
                    ->viaTable(
                        'e_employee_meta',
                        ['_employee' => 'id'],
                        function ($query) {
                            $ids = EDepartment::find()
                                              ->select(['id'])
                                              ->where(
                                                  [
                                                      'active' => self::STATUS_ENABLE,
                                                      '_structure_type' => StructureType::STRUCTURE_TYPE_DEPARTMENT,
                                                  ]
                                              )
                                              ->column();

                            $query->andFilterWhere(
                                [
                                    'e_employee_meta._department' => $ids,
                                    'e_employee_meta._position' => TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
                                ]
                            );
                        }
                    )
                    ->with(['structureType']);
    }

    public function getTeachers()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department'])
                    ->viaTable(
                        'e_employee_meta',
                        ['_employee' => 'id'],
                        function ($query) {
                            $ids = EDepartment::find()
                                              ->select(['id'])
                                              ->where(
                                                  [
                                                      'active' => self::STATUS_ENABLE,
                                                      '_structure_type' => StructureType::STRUCTURE_TYPE_DEPARTMENT,
                                                  ]
                                              )
                                              ->column();

                            $query->andFilterWhere(['e_employee_meta._department' => $ids]);
                            $query->andFilterWhere(
                                [
                                    'in',
                                    'e_employee_meta._position',
                                    [
                                        TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT,
                                        TeacherPositionType::TEACHER_POSITION_TYPE_INTERN,
                                        TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER,
                                        TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR,
                                        TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR,
                                        TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT,
                                    ],
                                ]
                            );
                        }
                    )
                    ->with(['structureType']);
    }

    public function getEmployeeMeta()
    {
        return $this->hasOne(EEmployeeMeta::className(), ['_employee' => 'id'])->via('employee');
        // ->viaTable('e_employee_meta', ['_employee' => 'id'])//->with(['structureType']);
    }

    public function getEmployeeCathedra()
    {
        $ids = EDepartment::find()
                          ->select(['id'])
                          ->where(
                              [
                                  'active' => self::STATUS_ENABLE,
                                  '_structure_type' => StructureType::STRUCTURE_TYPE_DEPARTMENT,
                              ]
                          )
                          ->column();
        return $this->hasOne(EEmployeeMeta::className(), ['_employee' => 'id'])->via('employee')->andWhere(
            ['_department' => $ids]
        );
        // ->viaTable('e_employee_meta', ['_employee' => 'id'])//->with(['structureType']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()->joinWith('employeeMeta');

        if ($this->search) {
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
            $query->orWhereLike('e_employee.third_name', $this->search);
            $query->orWhereLike('e_employee.passport_number', $this->search);
            $query->orWhereLike('e_employee.passport_pin', $this->search);
            $query->orWhereLike('e_employee.employee_id_number', $this->search);
        }

        $query->andFilterWhere(['e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING]);

        if ($this->_department) {
            $query->andFilterWhere(['e_employee_meta._department' => $this->_department]);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['e_employee_meta._department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->training_year) {
            $query->andFilterWhere(['training_year' => $this->training_year]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['updated_at' => SORT_DESC],
                    'attributes' => [
                        'id',
                        'position',
                        '_gender',
                        '_academic_degree',
                        'passport_number',
                        'passport_pin',
                        'birth_date',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }

    public function searchForMonitoring($params)
    {
        $this->load($params);

        $subQuery = self::find()
                        ->select(['_employee', 'MAX(end_date) AS end_date'])
                        ->groupBy('_employee');

        $query = EEmployeeMeta::find()->leftJoin(
            'e_employee_professional_development',
            'e_employee_meta._employee=e_employee_professional_development._employee'
        );
        $query->innerJoin(
            ['c' => $subQuery],
            'e_employee_professional_development._employee = c._employee AND e_employee_professional_development.end_date = c.end_date'
        );

        $query->select(
            [
                'e_employee_professional_development.id',
                'e_employee_professional_development._employee',
                'e_employee_professional_development._employee_position',
                'e_employee_professional_development.training_title',
                'e_employee_professional_development.training_year',
                'e_employee_professional_development._training_place',
                'e_employee_professional_development.begin_date',
                'e_employee_professional_development.end_date',
                'e_employee_meta._employee',
                'e_employee_meta._employee_status',
                'e_employee_meta._position',
                'e_employee_meta._department',
                'e_employee_meta.contract_date',
                'e_employee_meta.created_at',
            ]
        );
        $query->addSelect(
            [
                "(DATE_PART('year', now()) - DATE_PART('year', e_employee_professional_development.end_date)) * 12 +
              (DATE_PART('month', now()) - DATE_PART('month', e_employee_professional_development.end_date)) AS months",
            ]
        );

        $query->andFilterWhere(['e_employee_meta._position' => TeacherPositionType::TEACHER_POSITIONS]);
        $query->andFilterWhere(['e_employee_meta._position' => TeacherPositionType::TEACHER_POSITIONS]);
        $query->andFilterWhere(['e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING]);
        if ($this->_department) {
            $query->andFilterWhere(['e_employee_meta._department' => $this->_department]);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query->andFilterWhere(['e_employee_meta._department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->training_year) {
            $query->andFilterWhere(['training_year' => $this->training_year]);
        }

        $query_have_not = EEmployeeMeta::find()->leftJoin(
            'e_employee_professional_development',
            'e_employee_meta._employee=e_employee_professional_development._employee'
        );

        $query_have_not->select(
            [
                'e_employee_professional_development.id',
                'e_employee_professional_development._employee',
                'e_employee_professional_development._employee_position',
                'e_employee_professional_development.training_title',
                'e_employee_professional_development.training_year',
                'e_employee_professional_development._training_place',
                'e_employee_professional_development.begin_date',
                'e_employee_professional_development.end_date',
                'e_employee_meta._employee',
                'e_employee_meta._employee_status',
                'e_employee_meta._position',
                'e_employee_meta._department',
                'e_employee_meta.contract_date',
                'e_employee_meta.created_at',
            ]
        );
        $query_have_not->addSelect(
            [
                "(DATE_PART('year', now()) - DATE_PART('year', e_employee_meta.contract_date)) * 12 +
              (DATE_PART('month', now()) - DATE_PART('month', e_employee_meta.contract_date)) AS months",
            ]
        );

        $query_have_not->andFilterWhere(['e_employee_meta._position' => TeacherPositionType::TEACHER_POSITIONS]);
        $query_have_not->andFilterWhere(['e_employee_meta._position' => TeacherPositionType::TEACHER_POSITIONS]);
        $query_have_not->andFilterWhere(['e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING]);
        if ($this->_department) {
            $query_have_not->andFilterWhere(['e_employee_meta._department' => $this->_department]);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query_have_not->andFilterWhere(['e_employee_meta._department' => !empty($ids) ? $ids : $this->_faculty]);
        }

        if ($this->training_year) {
            $query_have_not->andFilterWhere(['training_year' => $this->training_year]);
        }

        $query_have_not->andFilterWhere(['NOT IN', 'e_employee_meta._employee', $subQuery->column()]);

        $query2 = (new ActiveQuery(EEmployeeMeta::className()))->from(
            [
                'union' => $query->union($query_have_not),
            ]
        );

        return new ActiveDataProvider(
            [
                'query' => $query2,
                'sort' => [
                    'defaultOrder' => ['months' => SORT_DESC],
                    'attributes' => [
                        'id',
                        'months',
                        'training_year',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }

}
