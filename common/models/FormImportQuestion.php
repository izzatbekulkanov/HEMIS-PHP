<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class FormImportQuestion extends Model
{
    /**
     * @var yii\web\UploadedFile
     */
    public $content;
    public $subject_id;
    public $topic_id;
    public $task_id;

    public function rules()
    {
        return [
            [['content', 'subject_id'], 'required'],
            [['topic_id', 'task_id'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content' => __('Content'),
            'subject_id' => __('Subject'),
            'topic_id' => __('Topic'),
            'task_id' => __('Task'),
        ];
    }

    public function normalizedContent()
    {
        //$content   = preg_replace('/(<p[^>]*>)\s(.*?)\s(<\/p>)/i', '${2}<br>', $this->content);
        //$content   = strip_tags($this->content, '<img> <img/> <i> <u> <b> <strong> <br> <br/> <del> <kbd> <code> <pre>');
        $content = preg_replace('/[+]{4,}/', '@+++@', $this->content);
        //$content = preg_replace('/`(.*?)`/i', '<code>$1</code>', $content);
        $content = str_replace('[[[', '<pre class="language-markup"><code>', $content);
        $content = str_replace(']]]', '</code></pre>', $content);

        $questions = explode('@+++@', $content);
        $chars = 'abcdefghijklmnopqrstuvxy';
        $data = [];
        foreach ($questions as $i => $question) {
            $cor = [];
            $vars = [];
            $k = 0;
            $orgQuestion = $question;
            //$question    = strip_tags($question, '<img> <img/> <i> <u> <b> <strong> <br> <br/> <del> <kbd> <code> <pre>');
            $answers = preg_replace('/[=]{4,}+/', '@===@', $question);
            $answers = explode('@===@', $answers);
            $q = ArrayHelper::remove($answers, 0);
            foreach ($answers as $answer) {
                $answer = trim($answer, " \t\n\r");
                if (($pos = strpos($answer, '#')) === 0) {
                    $cor[] = $chars[$k];
                    $answer = preg_replace('/#/', '', $answer, 1);
                }
                $var = trim(strtr($answer, $this->getCharMap()), " \t\n\r");
                if (strlen($var)) {
                    $vars[$chars[$k]] = $var;
                }
                $k++;
            }
            $q = preg_replace('/^(<br>)(.*)(<br>)$/', '$2', $q);
            $q = trim(strtr($q, $this->getCharMap()), " \t\n\r");
            $orgQuestion = preg_replace('/^(<br>)(.*)(<br>)$/', '$2', $orgQuestion);
            if (mb_strlen($q) > 5) {
                $data[] = [
                    '_subject' => $this->subject_id,
                    '_subject_topic' => $this->topic_id,
                    '_subject_task' => $this->task_id,
                    'content' => $orgQuestion,
                    'q' => $q,
                    'vars' => $vars,
                    'correct' => $cor,
                ];
            }
        }

        return $data;
    }

    public function getCharMap()
    {
        return [
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
            //"`"   => "’",
            '$с$' => '$c$',
        ];
    }

}