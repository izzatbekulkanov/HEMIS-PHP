<?php

namespace common\models\academic;

use common\models\curriculum\ECurriculum;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EStudent;
use common\models\student\EStudentDecreeMeta;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\StudentStatus;
use DateTime;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "e_decree".
 * @property string $id
 * @property string $_department
 * @property string $_decree_type
 * @property string $number
 * @property string $name
 * @property string $header
 * @property string $body
 * @property string $trailer
 * @property string[] $file
 * @property string $status
 * @property DateTime $date
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DecreeType $decreeType
 * @property EDecreeStudent[] $decreeStudents
 * @property EDepartment $department
 */
class EDecree extends _BaseModel
{
    const STATUS_ENABLE = 'enable';
    const STATUS_DISABLE = 'disable';
    public $selected_decree;


    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }


    public static function tableName()
    {
        return 'e_decree';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'number', '_department', '_decree_type', 'date'], 'required'],
            [['number'], 'string', 'max' => 16],
            [['name'], 'string', 'max' => 512],
            [['header', 'body', 'trailer'], 'string', 'max' => 32000],
            [['file'], 'required'],
            [['selected_decree'], 'required', 'on' => 'apply'],
            [['_decree_type', '_department'], 'safe', 'on' => 'apply'],
            [['status'], 'in', 'range' => array_keys(self::getStatusOptions())],
            [['_department'], 'exist', 'skipOnError' => true, 'targetClass' => EDepartment::className(), 'targetAttribute' => ['_department' => 'id'], 'filter' => ['_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY]],
            [['_decree_type'], 'exist', 'skipOnError' => true, 'targetClass' => DecreeType::className(), 'targetAttribute' => ['_decree_type' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'date' => __('Buyruq sanasi'),
            'header' => __('Buyruq maqsadi'),
            'body' => __('Buyruq tanasi'),
            'trailer' => __('Buyruq yakuni va asosi'),
            '_department' => __('Faculty'),
        ]);
    }

    public function beforeDelete()
    {
        if ($this->getDecreeStudents()->count()) {
            throw new IntegrityException(__('Could not delete related data'));
        }
        return parent::beforeDelete();
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public function getDecreeType()
    {
        return $this->hasOne(DecreeType::className(), ['code' => '_decree_type']);
    }


    public function getDecreeStudents()
    {
        return $this->hasMany(EDecreeStudent::className(), ['_decree' => 'id'])->with(['studentMeta']);
    }


    public function searchForEmployee($params, Admin $admin)
    {
        $this->load($params);

        $query = self::find()->with(['department', 'decreeType']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'name',
                    'number',
                    '_department',
                    '_decree_type',
                    'created_at',
                    'updated_at',
                    'date',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name', $this->search);
            $query->orWhereLike('number', $this->search);
        }

        if ($this->_decree_type) {
            $query->andFilterWhere(['_decree_type' => $this->_decree_type]);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }

        if ($admin->role->isDeanRole()) {
            $query->andFilterWhere(['_department' => $admin->employee->deanFaculties->id]);
        }

        return $dataProvider;
    }

    public static function getOptionsByCurriculum(Admin $_user, ECurriculum $curriculum = null, $decreeType = false)
    {
        $query = self::find()
            ->andFilterWhere(['status' => self::STATUS_ENABLE])
            ->orderBy(['date' => SORT_DESC]);

        if ($curriculum && $curriculum->department) {
            $query->andFilterWhere(['_department' => $curriculum->_department]);
        }
        if ($decreeType) {
            $query->andFilterWhere(['_decree_type' => $decreeType]);
        }

        $items = $query->all();

        return [
            'data' => ArrayHelper::map($items, 'id', function (EDecree $item) {
                return [
                    'date' => $item->date->format('Y-m-d'),
                    'number' => $item->number,
                    'name' => $item->name,
                ];
            }),
            'options' => ArrayHelper::map($items, 'id', function (EDecree $item) {
                return $item->getFullInformation();
            })
        ];
    }

    public static function getOptions($department = false, $decreeType = false)
    {
        $query = self::find()
            ->andFilterWhere(['status' => self::STATUS_ENABLE])
            ->orderBy(['date' => SORT_DESC]);
        if ($department)
            $query->andFilterWhere(['_department' => $department]);

        if ($decreeType) {
            $query->andFilterWhere(['_decree_type' => $decreeType]);
        }

        $items = $query->all();

        return ArrayHelper::map($items, 'id', function (EDecree $item) {
            return sprintf("%s / %s / %s", $item->date->format('Y-m-d'), $item->number, $item->getShortTitle());
        });
    }


    public function getStudentsProvider()
    {
        $query = $this->getDecreeStudents();

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);
    }

    public function registerStudent(EStudent $student, IdentityInterface $user)
    {
        if ($decree = EDecreeStudent::findOne(['_student' => $student->id, '_decree' => $this->id])) {
            return $decree;
        } else {
            $decree = new EDecreeStudent([
                '_admin' => $user->getId(),
                '_student' => $student->id,
                '_student_meta' => $student->meta ? $student->meta->id : null,
                '_decree' => $this->id,
                'created_at' => new DateTime()
            ]);
            if ($decree->save()) {
                return $decree;
            }
        }
    }

    /**
     * @param EStudent $student
     * @param $type
     * @return self
     */
    public static function getDecreeByType(EStudent $student, $type)
    {
        return self::findOne([
            'id' => EDecreeStudent::find()
                ->select(['_decree'])
                ->where(['_student' => $student->id])
                ->column(),
            '_decree_type' => $type,
        ]);
    }

    public function getShortInformation()
    {
        return sprintf('№ %s / %s', $this->number, \Yii::$app->formatter->asDate($this->date->getTimestamp()));
    }

    public function getFullInformation()
    {
        return sprintf('%s / %s / %s', $this->number, \Yii::$app->formatter->asDate($this->date->getTimestamp()), $this->name);
    }


    public function searchForApply($params, $faculty = false)
    {
        if ($faculty) {
            $this->_department = $faculty;
        }
    }

    private function getSelectQueryFilters($col)
    {
        $query = self::find()->select([$col])
            ->andFilterWhere([
                'status' => true
            ])
            ->distinct();

        foreach (['_department', '_decree_type'] as $attribute) {
            if ($col != $attribute && $this->$attribute) {
                $query->andFilterWhere([$attribute => $this->$attribute]);
            }
        }

        return $query->column();
    }


    public function getDecreeItems()
    {
        return ArrayHelper::map(
            EDecree::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(['status' => true, 'id' => $this->getSelectQueryFilters('id')])
                ->all(), 'id', function (self $item) {
            return $item->getFullInformation();
        }
        );
    }

    public function getDecreeTypeItems()
    {
        return ArrayHelper::map(
            DecreeType::find()
                ->orderByTranslationField('name', 'ASC')
                ->where(['active' => true, 'code' => $this->getSelectQueryFilters('_decree_type')])
                ->all(), 'code', 'name');
    }

    public function getDepartmentItems()
    {
        return ArrayHelper::map(
            EDepartment::find()
                ->orderByTranslationField('name', 'ASC')
                ->where(['active' => true, 'id' => $this->getSelectQueryFilters('_department')])
                ->all(), 'id', 'name');
    }

}
