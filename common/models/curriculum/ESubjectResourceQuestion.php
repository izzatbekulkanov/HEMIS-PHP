<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "e_subject_topic_question".
 *
 * @property int $id
 * @property string $name
 * @property string $content
 * @property string $content_r
 * @property string $answers
 * @property string[] $_answer
 * @property int $_curriculum
 * @property int $_subject
 * @property string $_language
 * @property string $_training_type
 * @property int $_subject_topic
 * @property string $_education_year
 * @property string $_semester
 * @property int $_employee
 * @property int $_subject_resource
 * @property int $_subject_task
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ECurriculum $curriculum
 * @property ECurriculumSubjectTopic $subjectTopic
 * @property EEmployee $employee
 * @property ESubject $subject
 * @property EducationYear $educationYear
 * @property Language $language
 * @property TrainingType $trainingType
 * @property ESubjectResource $subjectResource
 * @property ESubjectTask $subjectTask
 */
class ESubjectResourceQuestion extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    protected $_translatedAttributes = ['name'];

    public static function tableName()
    {
        return 'e_subject_resource_question';
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
            //[['name', 'content', '_language'], 'required', 'on' => self::SCENARIO_CREATE],
            [['content'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['content', 'status', '_subject', '_topic', '_answer', 'answers'], 'safe', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            //[['name', 'content', 'content_r', 'answers', '_answer', '_curriculum', '_subject', '_language', '_training_type', '_subject_topic', '_education_year', '_semester', '_employee', 'updated_at', 'created_at'], 'required'],
            [['name', 'content', 'content_r'], 'string'],
            [['_curriculum', '_subject', '_subject_topic', '_employee', 'position'], 'default', 'value' => null],
            [['_curriculum', '_subject', '_subject_topic', '_employee', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['content'], 'validateQuestion'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['_language', '_training_type', '_education_year', '_semester'], 'string', 'max' => 64],
            [['_curriculum'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculum::className(), 'targetAttribute' => ['_curriculum' => 'id']],
            [['_subject_topic'], 'exist', 'skipOnError' => true, 'targetClass' => ECurriculumSubjectTopic::className(), 'targetAttribute' => ['_subject_topic' => 'id']],
            [['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
            [['_subject'], 'exist', 'skipOnError' => true, 'targetClass' => ESubject::className(), 'targetAttribute' => ['_subject' => 'id']],
            [['_education_year'], 'exist', 'skipOnError' => true, 'targetClass' => EducationYear::className(), 'targetAttribute' => ['_education_year' => 'code']],
            [['_language'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['_language' => 'code']],
            [['_training_type'], 'exist', 'skipOnError' => true, 'targetClass' => TrainingType::className(), 'targetAttribute' => ['_training_type' => 'code']],
        ]);
    }

    public function getCurriculum()
    {
        return $this->hasOne(ECurriculum::className(), ['id' => '_curriculum']);
    }

    public function getSubjectTopic()
    {
        return $this->hasOne(ECurriculumSubjectTopic::className(), ['id' => '_subject_topic']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function getSubject()
    {
        return $this->hasOne(ESubject::className(), ['id' => '_subject']);
    }

    public function getEducationYear()
    {
        return $this->hasOne(EducationYear::className(), ['code' => '_education_year']);
    }

    public function getSubjectResource()
    {
        return $this->hasOne(ESubjectResource::className(), ['id' => '_subject_resource']);
    }

    public function getSubjectTask()
    {
        return $this->hasOne(ESubjectTask::className(), ['id' => '_subject_task']);
    }

    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['code' => '_language']);
    }

    public function getTrainingType()
    {
        return $this->hasOne(TrainingType::className(), ['code' => '_training_type']);
    }

    public function validateQuestion()
    {
        $this->renderContent();

        if (count($this->answers) < 2) {
            $this->addError('content', __('Variantlar soni kamida 1 ta bo\'lishi kerak'));
        }

        if (count($this->_answer) == 0) {
            $this->addError('content', __('To\'g\'ri javoblar soni kamida 1 ta bo\'lishi kerak'));
        }
    }

    public function renderContent()
    {
        if ($this->content_r == null || $this->isNewRecord || $this->isAttributeChanged('content')) {
            $this->content = strip_tags($this->content, '<br><img><a><pre><code><sup><sub><b><i><em><u><strong>');
            $this->content_r = $this->content;
            $content = preg_replace('/[=]{4,}/', '@===@', $this->content);
            $content = str_replace('[[[', '<pre class="language-markup"><code>', $content);
            $content = str_replace(']]]', '</code></pre>', $content);
            $answers = explode('@===@', $content);
            $this->name = trim(strtr(ArrayHelper::remove($answers, 0), $this->getCharMap()), " \t\n\r");
            $chars = 'abcdefghijklmnopqrstuvxyz';
            $cor = [];
            $vars = [];
            $orgs = [];
            $k = 0;
            foreach ($answers as $answer) {
                $org = $answer = trim($answer, " \t\n\r");
                if ($pos = strpos($answer, '#') === 0) {
                    $cor[] = $chars[$k];
                    $answer = preg_replace('/#/', '', $answer, 1);
                }
                $var = trim(strtr($answer, $this->getCharMap()), " \t\n\r");
                if (strlen($var)) {
                    $vars[$chars[$k]] = $var;
                    $orgs[] = $org;
                    $k++;
                }
            }
            $this->_answer = $cor;
            $this->answers = $vars;


            foreach ($vars as $c => &$var) {
                if (in_array($c, $cor)) {
                    $var = '#' . $var;
                }
            }

            $this->content_r = "<p>{$this->name}</p><p>======</p>" . implode("<p>======</p>", $orgs);
        }

        return parent::beforeValidate();
    }


    public function beforeDelete()
    {
        if ($this->subjectResource && ESubjectTaskStudent::find()->where([
                '_subject_resource' => $this->_subject_resource
            ])->count()) {
            throw new IntegrityException(__('Could not delete related data'));
        }
        if ($this->subjectTask && ESubjectTaskStudent::find()->where([
                '_subject_task' => $this->_subject_task
            ])->count()) {
            throw new IntegrityException(__('Could not delete related data'));
        }

        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        $this->renderContent();

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        //$this->subjectTopic->updateAttributes(['question_count' => self::find()->where(['_subject_topic' => $this->subjectTopic->id])->count()]);

        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        if ($this->subjectResource)
            $this->subjectResource->updateQuestionsCount();

        parent::afterDelete(); // TODO: Change the autogenerated stub
    }


    public function getCharMap()
    {
        return [
            "&ldquo;" => '"',
            "&rdquo;" => '"',
            "o'" => "o‘",
            "o`" => "o‘",
            "o’" => "o‘",
            "O'" => "O‘",
            "O`" => "O‘",
            "O’" => "O‘",
            "g'" => "g‘",
            "g`" => "g‘",
            "g’" => "g‘",
            "G'" => "G‘",
            "G`" => "G‘",
            "G’" => "G‘",
            // "`" => "’",
            '$с$' => '$c$',
        ];
    }

    public function getShuffledAnswers()
    {
        $answers = $this->answers;
        $keys = array_keys($answers);
        $shuffled_answers = [];
        shuffle($keys);

        foreach ($keys as $key) {
            $shuffled_answers[$key] = $answers[$key];
        }

        return $shuffled_answers;
    }

    public function getTitle()
    {
        return strip_tags(StringHelper::truncateWords($this->name, 10));
    }

    public function getShortTitle($len=6)
    {
        return StringHelper::truncateWords(strip_tags($this->name), $len);
    }

    public function isMultiple()
    {
        return count($this->_answer) > 1;
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_subject_topic',
                    '_employee',
                    '_semester',
                    '_education_year',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            //$query->orWhereLike('code', $this->search);
        }
        if ($this->_curriculum) {
            $query->andFilterWhere(['_curriculum' => $this->_curriculum]);
        }
        if ($this->_subject_topic) {
            $query->andFilterWhere(['_subject_topic' => $this->_subject_topic]);
        }
        if ($this->_semester) {
            $query->andFilterWhere(['_semester' => $this->_semester]);
        }
        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        return $dataProvider;
    }
}
