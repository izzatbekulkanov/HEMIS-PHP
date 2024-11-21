<?php


namespace frontend\controllers;

use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\EExamStudent;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\Semester;
use frontend\models\curriculum\StudentFinalExam;
use frontend\models\curriculum\SubjectResourceStudent;
use frontend\models\curriculum\SubjectTaskActivity;
use frontend\models\curriculum\SubjectTaskStudent;
use Yii;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectResource;
use common\models\system\classifier\TrainingType;
use frontend\models\curriculum\StudentAttendance;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\StudentExam;
use frontend\models\curriculum\StudentPerformance;
use frontend\models\curriculum\StudentSchedule;
use frontend\models\curriculum\SubjectResource;
use frontend\models\curriculum\SubjectTask;
use frontend\models\curriculum\SubjectTopic;
use yii\helpers\Url;
use yii\web\Response;

class TestController extends FrontendController
{
    public $activeMenu = 'education';


    public function actionExamResult($id)
    {
        /**
         * @var $exam EExamStudent
         */
        if ($exam = EExamStudent::findOne(['_student' => $this->_user()->id, 'id' => $id])) {
            if ($exam->finished_at) {
                return $this->renderView([
                    'model' => $exam,
                ]);
            }
        }

        return $this->goBack();
    }

    public function actionResult($id)
    {
        /**
         * @var $exam EStudentTaskActivity
         */
        if ($exam = EStudentTaskActivity::findOne(['_student' => $this->_user()->id, 'id' => $id])) {
            if ($exam->finished_at) {
                return $this->renderView([
                    'model' => $exam,
                ]);
            }
        }

        return $this->goBack();
    }

    public function actionFinishExam($id)
    {
        /**
         * @var $model StudentFinalExam
         */
        $user = $this->_user();
        if ($model = StudentFinalExam::findOne($id)) {
            if ($studentExam = $model->getStudentExam($user, false)) {
                if ($studentExam->finished_at == null) {
                    $studentExam->finishUserTest();
                    $this->addInfo(__('{name} imtihonida test sessiyasi yakunlandi.', ['name' => $model->name]));
                }
            }
        }
        return $this->redirect(['exams']);
    }

    public function actionStartExam($id)
    {
        $this->layout = 'empty';
        /**
         * @var $model StudentFinalExam
         */
        $user = $this->_user();
        if ($model = StudentFinalExam::findOne($id)) {
            if ($examGroup = $model->getStudentExamGroup($user)) {

                if ($this->get('finish')) {
                    if ($studentExam = $model->getStudentExam($user, false)) {
                        if ($studentExam->finishUserTest()) {
                            $studentExam->refresh();
                            if ($this->get('auto')) {
                                $this->addInfo(__('Test topshirishda sizning vaqtingiz avtomatik yakunlandi.'));
                            }
                            return $this->render('finish-exam', [
                                'model' => $studentExam,
                            ]);
                        }
                    }

                    return $this->redirect(['test/exams']);
                }

                if ($model->canJoinExam($user)) {
                    $studentExam = $model->getStudentExam($user, true, $deviceId = $this->getDeviceUniqueId());

                    if ($deviceId != $studentExam->session) {
                        $this->addError(__("Ushbu testni ishlash avvalroq boshqa qurilmada boshlangan. Birinchi boshlangan qurilma yakunlanmagunga qadar siz ushbu qurilmadan testga kira olmaysiz."));
                        return $this->redirect(['exams']);
                    }

                    if ($this->get('set')) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        $q = $this->get('q');
                        $v = $this->get('v');
                        $s = $this->get('s');

                        $studentExam->setUserAnswer($q, $v, $s);

                        return $studentExam->getUserAnswers();
                    }

                    $questions = $studentExam->getUserQuestions(true, Yii::$app->request->getUserIP());

                    return $this->renderView([
                        'model' => $studentExam,
                        'questions' => $questions,
                    ]);
                } else {
                    if ($studentExam = $model->getStudentExam($user, false)) {
                        if ($studentExam->finished_at == null) {
                            $studentExam->finishUserTest();
                            $this->addInfo(__('{name} imtihonida test yakunlandi.', ['name' => $model->name]));
                        }
                    }

                    $this->addError(__('Ushbu imtihonga ruhsat etilmagansiz'));
                }
            }

        }
        return $this->redirect(['exams']);
    }

    public function actionExams()
    {

        $searchModel = new StudentFinalExam();

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester(), $this->getFilterParams()),
        ]);
    }

    public function actionStart($id)
    {
        $this->layout = 'empty';

        if ($model = $this->findSubjectTask($id, $this->_user()->id, $this->get('resource'))) {
            if ($this->get('finish')) {
                if ($model->finishUserTest()) {
                    $model->refresh();
                    if ($this->get('auto')) {
                        $this->addInfo(__('Test topshirishda sizning vaqtingiz avtomatik yakunlandi.'));
                    }
                    return $this->render($model->subjectTask ? 'finish-task' : 'finish-resource', [
                        'model' => $model,
                    ]);
                }
            }

            if ($model->subjectResource != null) {
                if (!$model->subjectResource->canStartTest()) {
                    $this->addError(__('Test savollari yetarli emas'));
                    return $this->redirect(['test/index', 'subject' => $model->_subject]);
                }
            }

            if ($model->subjectTask != null) {
                if (!$model->canStartTest()) {
                    $this->addError(__('Test savollari yetarli emas'));
                    return $this->redirect(['education/tasks', 'subject' => $model->_subject]);
                }

                if (!$model->hasTimeForTesting()) {
                    $this->addInfo(__('Test vaqti yakunlandi'));
                    return $this->redirect(Url::current(['finish' => 1]));
                }
            }

            if ($this->get('set')) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $q = $this->get('q');
                $v = $this->get('v');
                $s = $this->get('s');

                $model->setUserAnswer($q, $v, $s);

                return $model->getUserAnswers();
            }

            $questions = $model->getUserQuestions();

            return $this->render($model->subjectTask ? 'start-task' : 'start-resource', [
                'model' => $model,
                'questions' => $questions,
            ]);
        }

        return $this->goBack();
    }

    public function actionIndex($subject)
    {
        $searchModel = new SubjectTaskStudent();
        $params = [];

        if ($subject = $this->findSubject($subject)) {
            $params = $this->get();
            $params['subject'] = $subject->_subject;
        }


        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchTestsStudent($params, $this->_user(), $this->getSelectedSemester(), $subject),
        ]);
    }

    /**
     * @param $subject
     * @return ECurriculumSubject | null
     */
    public function findSubject($subject)
    {
        return ECurriculumSubject::find()
            ->where([
                '_curriculum' => $this->_user()->meta->_curriculum,
                '_semester' => $this->getSelectedSemester()->code,
                '_subject' => $subject,
                'active' => ECurriculumSubject::STATUS_ENABLE
            ])
            ->one();
    }

    /**
     * @param $id
     * @return SubjectTaskStudent
     */
    public function findSubjectTask($id, $userId, $isResource = false)
    {
        if ($isResource) {
            return SubjectTaskStudent::findOne(['_subject_resource' => $id, '_student' => $userId, '_task_type' => ESubjectTask::TASK_TYPE_TEST]);
        } else {
            return SubjectTaskStudent::findOne(['id' => $id, '_student' => $userId, '_task_type' => ESubjectTask::TASK_TYPE_TEST]);
        }
    }

}