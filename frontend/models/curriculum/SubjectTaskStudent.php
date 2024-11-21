<?php


namespace frontend\models\curriculum;


use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectResourceQuestion;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\Semester;
use frontend\models\system\Student;
use Mpdf\Tag\S;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class SubjectTaskStudent
 * @package frontend\models\curriculum
 */
class SubjectTaskStudent extends ESubjectTaskStudent
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['_subject', 'safe']
        ]);
    }

    public function searchTestsStudent($params, Student $student, Semester $semester, ECurriculumSubject $subject = null)
    {
        $this->load($params);
        if ($this->_subject == null && $subject) {
            $this->_subject = $subject->_subject;
        }
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->leftJoin('e_subject_resource', 'e_subject_resource.id=_subject_resource')
            ->leftJoin('e_curriculum_subject_topic', 'e_curriculum_subject_topic.id=e_subject_resource._subject_topic')
            ->with(['semester', 'subject', 'employee', 'subjectResource', 'trainingType']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['e_subject_task_student._training_type' => SORT_ASC, 'e_curriculum_subject_topic.position' => SORT_ASC],
                'attributes' => [
                    'e_subject_task_student._training_type',
                    'e_curriculum_subject_topic.position',
                    '_curriculum',
                    '_subject',
                    '_employee',
                    '_semester',
                    '_language',
                    '_education_year',
                    '_training_type',
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
            $query->orWhereLike('e_curriculum_subject_topic.name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($this->_subject) {
            $query->andFilterWhere(['e_subject_task_student._subject' => $this->_subject]);
        }

        $query->andFilterWhere(['_student' => $student->id]);
        $query->andFilterWhere(['e_subject_task_student._semester' => $semester->code]);
        $query->andWhere(new Expression('e_subject_task_student._subject_resource is not null and e_subject_resource.resource_type=12 and e_subject_resource.test_question_count>0'));

        return $dataProvider;
    }


    public function finishUserTest()
    {
        /**
         * @var $question ESubjectResourceQuestion
         */

        $data = $this->data;
        $questions = $questionItems = @$data['questions'];
        $resource = $this->subjectResource;
        $task = $this->subjectTask;

        $corrects = 0;
        $questionCount = 0;


        if ($this->_task_type == ESubjectTask::TASK_TYPE_TEST) {
            if (!empty($questions)) {
                if ($this->finished_at == null) {
                    if ($resource) {
                        $maxBall = -1;
                        foreach ($resource->testQuestions as $question) {
                            if (isset($questions[$question->id])) {
                                $questions[$question->id]['q'] = $question;
                            }
                        }
                    } elseif ($task) {
                        $maxBall = $task->max_ball;
                        foreach ($task->testQuestions as $question) {
                            if (isset($questions[$question->id])) {
                                $questions[$question->id]['q'] = $question;
                            }
                        }
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
                            $answers = $question->_answer;
                            $sum = 0;
                            foreach ($answers as $correct) {
                                if (array_key_exists($correct, $s)) $sum++;
                            }
                            $ball = count($answers) ? round($sum / count($answers), 1) : 1;
                            $corrects += $ball;
                        }
                    }

                    $percent = $questionCount > 0 ? round(100 * $corrects / $questionCount, 1) : 0;


                    $transaction = \Yii::$app->db->beginTransaction();

                    try {
                        if ($selected > 0 || $this->attempt_count == 0 || $resource || !isset($data['old_questions'])) {
                            $this->updateAttributes([
                                'finished_at' => new \DateTime('now'),
                                'correct' => $corrects,
                                'attempt_count' => $this->attempt_count + 1,
                                'percent' => $percent,
                                '_task_status' => SubjectTask::TASK_STATUS_PASSED,
                            ]);

                            if ($task) {
                                $model = $this->taskStudentActivity ? $this->taskStudentActivity : new EStudentTaskActivity();
                                $model->_subject_task_student = $this->id;
                                $model->_subject_task = $this->_subject_task;
                                $model->_training_type = $this->_training_type;
                                $model->_curriculum = $task->_curriculum;
                                $model->_employee = $task->_employee;
                                $model->_subject = $task->_subject;
                                $model->_task_type = $task->_task_type;
                                $model->_education_year = $task->_education_year;
                                $model->_semester = $task->_semester;
                                $model->_student = $this->_student;
                                $model->active = EStudentTaskActivity::STATUS_ENABLE;
                                $model->_final_exam_type = $task->_final_exam_type;
                                $model->send_date = new \DateTime('now');
                                $model->attempt_count = $this->attempt_count;
                                $model->started_at = $this->started_at;
                                $model->finished_at = $this->finished_at;
                                $model->mark = round($this->subjectTask->max_ball * $this->percent / 100);
                                $model->percent_c = $this->percent;
                                $model->correct = $this->correct;
                                $model->time = round(($this->finished_at->getTimestamp() - $this->started_at->getTimestamp()) / 60);
                                if ($model->save()) {
                                } else {
                                    throw new Exception(__('Natijalarni saqlashda xatolik vujudga keldi'));
                                }
                            }
                        } else {
                            if (isset($data['old_questions'])) {
                                $data = $this->data;
                                $data['questions'] = $data['old_questions'];
                                $this->updateAttributes([
                                    'finished_at' => $data['old_finished_at']['date'],
                                    'started_at' => $data['old_started_at']['date'],
                                    'data' => $data,
                                ]);
                            }
                        }


                        $transaction->commit();

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        }


        return $this->finished_at != null;
    }


    public function getUserAnswers()
    {
        $data = $this->data;
        $questions = @$data['questions'];

        $result = [];
        if (!empty($questions)) {

            foreach ($questions as $qid => $item) {
                $result[$qid] = isset($item['s']) && count($item['s']) ?1 : null;
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
                    if ($question = ESubjectResourceQuestion::findOne($qid)) {
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

    public function getUserQuestions($regenerate = true)
    {
        $data = $this->data;
        $questions = @$data['questions'];
        $resource = $this->subjectResource;
        $task = $this->subjectTask;

        //regenerate test questions if empty or testing is finished
        if ($regenerate && ($questions == null || empty($questions) || $this->finished_at != null)) {
            $questions = [];
            $random = false;

            if ($resource) {
                $orgQuestions = $resource
                    ->getTestQuestions()
                    ->where(['active' => true])
                    ->all();

                $limit = $resource->test_questions > 0 ? $resource->test_questions : count($orgQuestions);


                if ($random = $resource->test_random)
                    shuffle($orgQuestions);

            } elseif ($task) {
                $orgQuestions = $task
                    ->getTestQuestions()
                    ->where(['active' => true])
                    ->all();

                $limit = $task->question_count > 0 ? $task->question_count : count($orgQuestions);

                if ($random = $task->random)
                    shuffle($orgQuestions);
            }

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
            $this->updateAttributes([
                'data' => $data,
                'finished_at' => null,
                'started_at' => new \DateTime('now'),
            ]);
        }

        if ($resource) {
            foreach ($resource->testQuestions as $question) {
                if (isset($questions[$question->id])) {
                    $questions[$question->id]['q'] = $question;
                }
            }
        } elseif ($task) {
            foreach ($task->testQuestions as $question) {
                if (isset($questions[$question->id])) {
                    $questions[$question->id]['q'] = $question;
                }
            }
        }


        return $questions;
    }

    public function getRealQuestionsCount()
    {
        $data = $this->data;
        $questions = @$data['questions'];
        return is_array($questions) ? count($questions) : 0;
    }

    public function hasTimeForTesting()
    {
        if ($this->subjectTask && $this->started_at && $this->finished_at == null) {
            $left = time() - $this->started_at->getTimestamp();

            return $left < $this->subjectTask->test_duration * 60;
        }

        return true;
    }
}