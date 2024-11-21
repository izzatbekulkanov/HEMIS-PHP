<?php

namespace common\models\curriculum;
use common\models\curriculum\Semester;
use common\models\system\_BaseModel;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\TrainingType;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ESubject;

use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "e_curriculum_subject_exam_type".
 *
 * @property int $id
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_semester
 * @property string $_exam_type
 * @property int $max_ball
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property ESubject $subject
 * @property ExamType $examType
 */
class ECurriculumSubjectExamType extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_curriculum_subject_exam_type';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getAllExamTypeByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()->with(['examType'])
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['>', 'max_ball', 0])
            //->orderBy(['examType.position'=>SORT_DESC])
            ->all();
    }

    public static function getExamTypeByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
               'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['>', 'max_ball', 0])
            ->andWhere(['not in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]])
            ->orderBy('position')
            ->all();
    }

    public static function getExamTypeOtherByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['not in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]])
            ->orderBy('position')
            ->all();
    }

    /**
     * @param false $curriculum
     * @param false $semester
     * @param false $subject
     * @param false $exam_type
     * @return self
     */
    public static function getExamTypeOneByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false, $exam_type = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                '_exam_type' => $exam_type,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['>', 'max_ball', 0])
            ->one();
    }

    public static function getOtherExamTypeByCurriculumSemesterSubject($curriculum = false, $semester = false, $subject = false)
    {
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['>', 'max_ball', 0])
            ->andWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]])
            //->orderBy('_exam_type', SORT_DESC)
            ->all();
    }

    public static function getExamTypeByCurriculumSemesterSubjectTraining($curriculum = false, $semester = false, $subject = false, $training = false)
    {
        /*if($model->_exam_type === ExamType::EXAM_TYPE_MIDTERM || $model->examType->_parent === ExamType::EXAM_TYPE_MIDTERM){
            $trainings = array(TrainingType::TRAINING_TYPE_LECTURE => TrainingType::TRAINING_TYPE_LECTURE);
        }
        elseif($model->_exam_type === ExamType::EXAM_TYPE_CURRENT || $model->examType->_parent === ExamType::EXAM_TYPE_CURRENT){
            $trainings = array(TrainingType::TRAINING_TYPE_LABORATORY => TrainingType::TRAINING_TYPE_LABORATORY, TrainingType::TRAINING_TYPE_PRACTICE => TrainingType::TRAINING_TYPE_PRACTICE, TrainingType::TRAINING_TYPE_SEMINAR =>TrainingType::TRAINING_TYPE_SEMINAR);
        }*/
        if($training === TrainingType::TRAINING_TYPE_LECTURE){
            $exams = array(ExamType::EXAM_TYPE_MIDTERM => ExamType::EXAM_TYPE_MIDTERM, ExamType::EXAM_TYPE_MIDTERM_FIRST => ExamType::EXAM_TYPE_MIDTERM_FIRST, ExamType::EXAM_TYPE_MIDTERM_SECOND => ExamType::EXAM_TYPE_MIDTERM_SECOND);
        }
        elseif($training === TrainingType::TRAINING_TYPE_PRACTICE || $training === TrainingType::TRAINING_TYPE_LABORATORY || $training === TrainingType::TRAINING_TYPE_SEMINAR){
            $exams = array(ExamType::EXAM_TYPE_CURRENT => ExamType::EXAM_TYPE_CURRENT, ExamType::EXAM_TYPE_CURRENT_FIRST => ExamType::EXAM_TYPE_CURRENT_FIRST,ExamType::EXAM_TYPE_CURRENT_SECOND => ExamType::EXAM_TYPE_CURRENT_SECOND);
        }
        return self::find()
            ->where([
                '_curriculum' => $curriculum,
                '_semester' => $semester,
                '_subject' => $subject,
                'active' => self::STATUS_ENABLE
            ])
            ->andWhere(['>', 'max_ball', 0])
            ->andWhere(['not in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]])
            ->andWhere(['in', '_exam_type', $exams])
            ->orderBy('position')
            ->all();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_curriculum', '_subject', '_semester', '_exam_type', 'max_ball'], 'required'],
            [['_exam_type', 'max_ball'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_curriculum', '_subject', 'max_ball', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', 'max_ball', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_semester', '_exam_type'], 'string', 'max' => 64],
            [['_curriculum', '_subject', '_semester', '_exam_type'], 'unique', 'targetAttribute' => ['_curriculum', '_subject', '_semester', '_exam_type'], 'message'=>__('This exam type has already been taken')],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_exam_type'], 'exist', 'skipOnError' => true, 'targetClass' => ExamType::className(), 'targetAttribute' => ['_exam_type' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            '_curriculum' => __('Curriculum Curriculum'),
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getExamType()
    {
        return $this->hasOne(ExamType::className(), ['code' => '_exam_type']);
    }

    public function getSemester()
    {
        return $this->hasOne(Semester::className(), ['code' => '_semester']);
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        $query->leftJoin('h_exam_type', 'h_exam_type.code=_exam_type');
        //$query->orderBy(['h_exam_type.position' => SORT_ASC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['h_exam_type.position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                   // 'e_curriculum_subject_exam_type.active',
                    '_exam_type',
                    'max_ball',
                    'id',
                    'h_exam_type.position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            //$query->orWhereLike('name_uz', $this->search, '_translations');
            //  $query->orWhereLike('name_oz', $this->search, '_translations');
            // $query->orWhereLike('name_ru', $this->search, '_translations');
           // $query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_exam_type) {
            $query->andFilterWhere(['_exam_type' => $this->_exam_type]);
        }

        return $dataProvider;
    }

}
