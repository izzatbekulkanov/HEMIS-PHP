<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
 * @property string $_answer
 * @property integer $_exam
 * @property int|null $position
 * @property bool|null $active
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property EExam $exam
 */
class EExamQuestion extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;

    public static function tableName()
    {
        return 'e_exam_question';
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
            [['content'], 'required'],
            [['active'], 'boolean'],
            [['content'], 'validateQuestion'],
        ]);
    }

    public function getExam()
    {
        return $this->hasOne(EExam::className(), ['id' => '_exam']);
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

    public function beforeSave($insert)
    {
        $this->renderContent();

        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        if ($this->exam->getExamStudentResults()->count()) {
            throw new IntegrityException(__('Could not delete related data'));
        }

        return parent::beforeDelete();
    }

    public function getTitle()
    {
        return strip_tags(StringHelper::truncateWords($this->name, 15));
    }

    public function getShortTitle($len = 6)
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
                    'name',
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
            $query->orWhereLike('name', $this->search);
        }

        return $dataProvider;
    }
}
