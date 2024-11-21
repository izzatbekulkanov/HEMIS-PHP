<?php


namespace common\models\system\job;


use common\models\curriculum\ESubjectSchedule;
use common\models\system\Admin;
use common\models\system\AdminMessage;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

class SubjectScheduleListFileGenerateJob extends BaseObject implements RetryableJobInterface
{
    public $filterParams;
    public $education_year;
    public $language;
    public $downloadUrl;
    public $recipients;
    public $retryAfter = 120;
    public $limit = 1;

    public function execute($queue)
    {
        echo "generateDownloadFile";
        \Yii::$app->language = $this->language;

        $searchModel = new ESubjectSchedule();
        $query = $searchModel->search_info($this->filterParams, false);
        $query->select('COUNT(e_subject_schedule.id) as count_lesson,_group as _group,e_subject_schedule._education_year as _education_year,_semester,_curriculum');
        $query->andFilterWhere(['e_subject_schedule._education_year' => $this->education_year]);
        $query->groupBy(['_group', 'e_subject_schedule._education_year', '_semester', '_curriculum']);

        if ($file = ESubjectSchedule::generateDownloadFile($query)) {
            if ($message = AdminMessage::createDraftMessage(Admin::findOne(['login' => Admin::SUPER_ADMIN_LOGIN]))) {
                $name = basename($file);
                $message->_recipients = $this->recipients;
                $message->title = __("Dars jadvallari ro'yxati", []);
                $message->message = __("Dars jadvallari ro'yxatini yuklab oling: {link}", ['link' => Html::a($name, $this->downloadUrl . '' . $name)]);
                $message->sendMessage();
            }
        }

        echo $file . PHP_EOL;
    }

    public function getTtr()
    {
        return $this->retryAfter;
    }

    public function canRetry($attempt, $error)
    {
        echo $error->getMessage();
        if ($attempt <= $this->limit) {
            return true;
        }

        return false;
    }
}