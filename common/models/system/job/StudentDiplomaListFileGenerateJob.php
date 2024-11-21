<?php


namespace common\models\system\job;


use common\models\archive\EStudentDiploma;
use common\models\student\EStudentMeta;
use common\models\system\Admin;
use common\models\system\AdminMessage;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

class StudentDiplomaListFileGenerateJob extends BaseObject implements RetryableJobInterface
{
    public $filterParams;
    public $language;
    public $downloadUrl;
    public $recipients;
    public $retryAfter = 120;
    public $limit = 1;

    public function execute($queue)
    {
        echo "generateDownloadFile\n";
        \Yii::$app->language = $this->language;

        $searchModel = new EStudentDiploma();
        if ($file = EStudentDiploma::generateDiplomaListFile($searchModel->searchDiplomaList($this->filterParams, false))) {
            if ($message = AdminMessage::createDraftMessage(Admin::findOne(['login' => Admin::SUPER_ADMIN_LOGIN]))) {
                $name = basename($file);
                $message->_recipients = $this->recipients;
                $message->title = __("Talabalar ro'yxati", []);
                $message->message = __("Talabalar ro'yxati tayyor, yuklab oling: {link}", ['link' => Html::a($name, $this->downloadUrl . '' . $name)]);
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