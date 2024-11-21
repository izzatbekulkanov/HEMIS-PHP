<?php


namespace frontend\controllers;

use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\Semester;
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

class ResourceController extends FrontendController
{
    public $activeMenu = 'education';

    public function actionDownload($id, $f)
    {
        if ($model = SubjectResource::findOne($id)) {
            if (is_array($model->filename)) {
                $files = $model->filename;
                if (isset($files[$f])) {
                    $file = Yii::getAlias('@static/uploads/') . $files[$f]['path'];

                    if (file_exists($file)) {
                        return Yii::$app->response->sendFile($file, $files[$f]['name']);
                    }
                }
            }
            return $this->redirect(['resource/index', 'subject' => $model->_subject]);
        }

        return $this->goHome();
    }

    public function actionIndex($subject)
    {
        $searchModel = new SubjectResource();
        $params = [];

        if ($subject = $this->findSubject($subject)) {
            $params = $this->get();
            $params['subject'] = $subject->_subject;
        }


        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForStudent($params, $this->_user(), $this->getSelectedSemester(), $subject),
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

}