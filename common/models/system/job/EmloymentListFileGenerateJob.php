<?php


namespace common\models\system\job;


use common\models\archive\EGraduateQualifyingWork;
use common\models\archive\EStudentEmployment;
use common\models\finance\EStudentContract;
use common\models\student\EStudentMeta;
use common\models\system\Admin;
use common\models\system\AdminMessage;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

class EmloymentListFileGenerateJob extends BaseObject implements RetryableJobInterface
{
    public $filterParams;
    public $education_year;
    public $department;
    public $language;
    public $downloadUrl;
    public $recipients;
    public $retryAfter = 120;
    public $limit = 1;

    public function execute($queue)
    {
        echo "generateDownloadFile";
        \Yii::$app->language = $this->language;

        $searchModel = new EStudentEmployment();
        $query = $searchModel->searchContingent($this->filterParams, $this->department, false);
        $query->andFilterWhere(['_education_year' => $this->education_year]);
        if ($file = EStudentEmployment::generateEmploymentDownloadFile($query)) {
            if ($message = AdminMessage::createDraftMessage(Admin::findOne(['login' => Admin::SUPER_ADMIN_LOGIN]))) {
                $name = basename($file);
                $message->_recipients = $this->recipients;
                $message->title = __("Bitiruvchilar ro'yxati", []);
                $message->message = __("Bitiruvchilarning ishga joylashish ro'yxatini yuklab oling: {link}", ['link' => Html::a($name, $this->downloadUrl . '' . $name)]);
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