<?php

namespace common\models\employee;

use common\models\structure\EDepartment;
use common\models\system\_BaseModel;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\TeacherStatus;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "e_employee_competition".
 *
 * @property int $id
 * @property string $_employee
 * @property string $_employee_position
 * @property string $election_date
 * @property string $document
 * @property bool $active
 * @property string|null $_translations
 */
class EEmployeeCompetition extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    public $_faculty;
    public $_department;
    public $months;

    public static function tableName()
    {
        return 'e_employee_competition';
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
                        'election_date',
                        'document',
                    ],
                    'required',
                    'on' => self::SCENARIO_INSERT,
                ],
                [['active'], 'boolean'],

                [['election_date'], 'date', 'format' => 'yyyy-mm-dd'],

                [['document'], 'string', 'max' => 1024],

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
                [['_faculty', '_department'], 'safe', 'on' => 'search'],
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
        return $this->hasOne(EEmployeeMeta::className(), ['_employee' => 'id'])->andFilterWhere(
            ['_position' => TeacherPositionType::TEACHER_POSITIONS]
        )->via('employee');
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

        if ($this->election_date) {
            $query->andFilterWhere(['EXTRACT(year FROM "election_date")' => $this->election_date]);
        }

        return new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['updated_at' => SORT_DESC],
                    'attributes' => [
                        'id',
                        'employee.name',
                        'election_date',
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

        //Competetion have begin
        $subQuery = self::find()
            ->select(['_employee', 'MAX(election_date) AS election_date'])
            ->groupBy('_employee');

        $query = EEmployeeMeta::find();
        $query->leftJoin(
            'e_employee_competition',
            'e_employee_meta._employee=e_employee_competition._employee'
        );
        $query->innerJoin(['c' => $subQuery], 'e_employee_competition._employee = c._employee AND e_employee_competition.election_date = c.election_date');

        $query->select(
            [
                'e_employee_competition.id',
                'e_employee_competition._employee',
                'e_employee_meta._employee',
                'e_employee_meta._employee_status',
                'e_employee_meta._position',
                'e_employee_meta._department',
                'e_employee_meta.contract_date',
                'e_employee_meta.created_at',
                'e_employee_competition._employee_position',
                'e_employee_competition.election_date',
            ]
        );

        $query->addSelect(
            [
                "(DATE_PART('year', now()) - DATE_PART('year', COALESCE(e_employee_competition.election_date, e_employee_meta.contract_date))) * 12 +
              (DATE_PART('month', now()) - DATE_PART('month', COALESCE(e_employee_competition.election_date, e_employee_meta.contract_date))) AS months",
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
        //Competetion have end

        //Competetion haven't begin

        $query_not_competetion = EEmployeeMeta::find();

        $query_not_competetion->leftJoin(
            'e_employee_competition',
            'e_employee_meta._employee=e_employee_competition._employee'
        );
        $query_not_competetion->select(
            [
                'e_employee_competition.id',
                'e_employee_competition._employee',
                'e_employee_meta._employee',
                'e_employee_meta._employee_status',
                'e_employee_meta._position',
                'e_employee_meta._department',
                'e_employee_meta.contract_date',
                'e_employee_meta.created_at',
                'e_employee_competition._employee_position',
                'e_employee_competition.election_date',
            ]
        );

        $query_not_competetion->addSelect(
            [
                "(DATE_PART('year', now()) - DATE_PART('year', COALESCE(e_employee_competition.election_date, e_employee_meta.contract_date))) * 12 +
              (DATE_PART('month', now()) - DATE_PART('month', COALESCE(e_employee_competition.election_date, e_employee_meta.contract_date))) AS months",
            ]
        );

        $query_not_competetion->andFilterWhere(['e_employee_meta._position' => TeacherPositionType::TEACHER_POSITIONS]);
        $query_not_competetion->andFilterWhere(['e_employee_meta._position' => TeacherPositionType::TEACHER_POSITIONS]);
        $query_not_competetion->andFilterWhere(['e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING]);
        if ($this->_department) {
            $query_not_competetion->andFilterWhere(['e_employee_meta._department' => $this->_department]);
        }

        if ($this->_faculty) {
            $ids = EDepartment::find()->select('id, parent')->where(['parent' => $this->_faculty])->column();
            $query_not_competetion->andFilterWhere(['e_employee_meta._department' => !empty($ids) ? $ids : $this->_faculty]);
        }
        $query_not_competetion->andFilterWhere(['NOT IN', 'e_employee_meta._employee', $subQuery->column()]);
        //Competetion haven't end


        //$query->union($query_not_competetion, true);
        $query2 = (new ActiveQuery(EEmployeeMeta::className()))->from([
            'union' => $query->union($query_not_competetion)
        ]);
        return new ActiveDataProvider(
            [
                'query' => $query2,
                'sort' => [
                    'defaultOrder' => ['months' => SORT_DESC],
                    'attributes' => [
                        'id',
                        'election_date',
                        'months'
                    ],

                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]
        );
    }

}
