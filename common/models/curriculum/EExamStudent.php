<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use frontend\models\curriculum\SubjectTask;
use frontend\models\curriculum\SubjectTaskActivity;
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
 * This is the model class for table "e_subject_task_student".
 *
 * @property int $id
 * @property int $_exam
 * @property int $_student
 * @property int $_group
 * @property int $percent
 * @property int $correct
 * @property int $attempts
 * @property int $mark
 * @property int $time
 * @property string[] $data
 * @property string $ip
 * @property DateTime $finished_at
 * @property DateTime $started_at
 * @property string $updated_at
 * @property string $created_at
 * @property string $session
 *
 * @property EStudent $student
 * @property EGroup $group
 * @property EExam $exam
 */
class EExamStudent extends _BaseModel
{
    public function rules()
    {
        return [
            [['_group'], 'safe']
        ];
    }


    public static function tableName()
    {
        return 'e_exam_student';
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function getExam()
    {
        return $this->hasOne(EExam::className(), ['id' => '_exam']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
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
                    '_student',
                    '_group',
                    '_exam',
                    'correct',
                    'attempts',
                    'mark',
                    'percent',
                    'started_at',
                    'finished_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_group) {
            $query->andFilterWhere(['_student' => $this->_group]);
        }

        if ($this->_exam) {
            $query->andFilterWhere(['_student' => $this->_exam]);
        }

        return $dataProvider;
    }


    public function searchByExam(EExam $exam, $params, $asProvider = true)
    {
        $this->load($params);

        $query = self::find()->with(['student', 'group', 'exam']);

        $query->andFilterWhere(['_exam' => $exam->id]);

        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }

        $query->andFilterWhere(['>', 'attempts', 0]);

        return $asProvider ?
            new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['finished_at' => SORT_DESC],
                    'attributes' => [
                        '_student',
                        '_group',
                        '_exam',
                        'correct',
                        'attempts',
                        'mark',
                        'time',
                        'ip',
                        'percent',
                        'started_at',
                        'finished_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 400,
                ],
            ]) : $query->orderBy(['percent' => SORT_DESC])->all();
    }


    public function getRealQuestionsCount()
    {
        $data = $this->data;
        $questions = @$data['questions'];
        return is_array($questions) ? count($questions) : 0;
    }

    public function getUserAnswers()
    {
        $data = $this->data;
        $questions = @$data['questions'];

        $result = [];
        if (!empty($questions)) {

            foreach ($questions as $qid => $item) {
                $result[$qid] = isset($item['s']) && count($item['s']) ? 1 : null;
            }
        }

        return $result;
    }

    public function setUserAnswer($qid, $variant, $selected)
    {
        /**
         * @var $question ESubjectResourceQuestion
         */
        $data = $this->data;
        $questions = @$data['questions'];

        if ($this->finished_at == null) {
            if (!empty($questions)) {

                if (isset($questions[$qid])) {
                    if ($question = EExamQuestion::findOne($qid)) {
                        if (isset($questions[$qid]['a'][$variant])) {
                            $v = $questions[$qid]['a'][$variant];
                            if ($question->isMultiple()) {
                                if ($selected) {
                                    $questions[$qid]['s'][$v] = $variant;
                                } else {
                                    if (isset($questions[$qid]['s'][$v]))
                                        unset($questions[$qid]['s'][$v]);
                                }
                            } else {
                                $questions[$qid]['s'] = $selected ? [$v => $variant] : [];
                            }

                            $data['questions'] = $questions;
                            $this->updateAttributes(['data' => $data]);

                            return $questions;
                        }
                    }
                }
            }
        }

    }

    public function finishUserTest()
    {
        /**
         * @var $question ESubjectResourceQuestion
         */

        $data = $this->data;
        $questions = $questionItems = @$data['questions'];

        $corrects = 0;
        $questionCount = 0;

        if (!empty($questions)) {
            if ($this->finished_at == null) {

                foreach ($this
                             ->exam
                             ->getTestQuestions()
                             ->where(['id' => array_keys($questions)])
                             ->all() as $question) {
                    $questions[$question->id]['q'] = $question;
                }

                $selected = 0;
                foreach ($questions as $id => $item) {
                    if (isset($item['q'])) {
                        $questionCount++;
                        if (isset($item['s'])) {
                            $s = $item['s'];
                            if (!empty($s))
                                $selected++;
                        } else {
                            $s = [];
                        }

                        $question = $item['q'];
                        if (!($question instanceof \common\models\curriculum\EExamQuestion)) continue;
                        $answers = $question->_answer;

                        $sum = 0;
                        foreach ($answers as $correct) {
                            if (array_key_exists($correct, $s)) $sum++;
                        }
                        $ball = count($answers) ? round($sum / count($answers), 0) : 1;
                        $corrects += $ball;
                    }
                }

                $percent = $questionCount > 0 ? round(100 * $corrects / $questionCount, 1) : 0;

                $now = new \DateTime('now');

                if ($selected > 0 || $this->attempts == 0 || !isset($data['old_questions'])) {
                    $this->updateAttributes([
                        'finished_at' => $now,
                        'correct' => $corrects,
                        'session' => null,
                        'percent' => $percent,
                        'mark' => round($percent / 100 * $this->exam->max_ball, 1),
                        'attempts' => $this->attempts + 1,
                        'time' => $now->getTimestamp() - $this->started_at->getTimestamp(),
                    ]);
                } else {
                    if (isset($data['old_questions'])) {
                        $data = $this->data;
                        $data['questions'] = $data['old_questions'];
                        $this->updateAttributes([
                            'session' => null,
                            'finished_at' => $data['old_finished_at']['date'],
                            'started_at' => $data['old_started_at']['date'],
                            'data' => $data,
                        ]);
                    }
                }
            }
        }

        return $this->finished_at != null;
    }

    public function getUserQuestions($regenerate = true, $ip = '')
    {
        /**
         * @var $orgQuestions EExamQuestion[]
         */
        $data = $this->data;
        $questions = @$data['questions'];

        //regenerate test questions if empty or testing is finished
        if ($regenerate && ($questions == null || empty($questions) || $this->finished_at != null)) {
            $questions = [];
            $random = false;

            $orgQuestions = $this->exam
                ->getTestQuestions()
                ->where(['active' => true])
                ->all();

            $limit = $this->exam->question_count > 0 ? $this->exam->question_count : count($orgQuestions);


            if ($random = $this->exam->random)
                shuffle($orgQuestions);

            if ($limit > count($orgQuestions)) {
                $limit = count($orgQuestions);
            }

            $orgQuestions = array_slice($orgQuestions, 0, $limit);

            foreach ($orgQuestions as $item) {
                $answers = array_keys($item->answers);
                if ($random)
                    shuffle($answers);
                $questions[$item->id] = [
                    'q' => $item->id,
                    'a' => $answers,
                    's' => [],
                ];
            }

            if ($this->finished_at) {
                $data['old_questions'] = $data['questions'];
                $data['old_finished_at'] = $this->finished_at;
                $data['old_started_at'] = $this->started_at;
            }

            $data['questions'] = $questions;
            $this->updateAttributes(['data' => $data, 'finished_at' => null, 'ip' => $ip, 'started_at' => new \DateTime('now')]);
            $this->refresh();
        }

        foreach ($this
                     ->exam
                     ->getTestQuestions()
                     ->where(['id' => array_keys($questions)])
                     ->all() as $question) {
            $questions[$question->id]['q'] = $question;
        }


        return $questions;
    }

    public function canJoinTest()
    {
        return $this->exam->canJoinExam($this->student);
    }

    public function getHowMuchTime()
    {
        $now = time();
        $examPassTime = $this->started_at ? $now - $this->started_at->getTimestamp() : 0;
        $howMuchTimeHas = $this->exam->duration * 60 - $examPassTime;
        $timeToFinish = $this->exam->getStudentExamGroup($this->student)->getFinishAtTime()->getTimestamp() - $now;

        return $timeToFinish < $howMuchTimeHas ? $timeToFinish : $howMuchTimeHas;

    }

    public function isFinished()
    {
        return $this->finished_at != null;
    }
}
