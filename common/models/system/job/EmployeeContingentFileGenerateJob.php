<?php


namespace common\models\system\job;


use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\student\EStudentMeta;
use common\models\system\Admin;
use common\models\system\AdminMessage;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

class EmployeeContingentFileGenerateJob extends BaseObject implements RetryableJobInterface
{
    public $filterParams;
    public $language;
    public $downloadUrl;
    public $recipients;
    public $retryAfter = 120;
    public $limit = 1;
    public $type = 'employee';

    public function execute($queue)
    {
        echo "generateDownloadFile\n";
        \Yii::$app->language = $this->language;

        if ($this->type == 'employee') {
            $searchModel = new EEmployee();
            $file = EEmployee::generateDownloadFile($searchModel->searchContingent($this->filterParams, false));
        } else {
            $searchModel = new EEmployeeMeta();
            $file = EEmployeeMeta::generateDownloadFile($searchModel->search($this->filterParams)->query);
        }

        if ($file) {
            if ($message = AdminMessage::createDraftMessage(Admin::findOne(['login' => Admin::SUPER_ADMIN_LOGIN]))) {
                $name = basename($file);
                $message->_recipients = $this->recipients;

                if ($this->type == 'employee') {
                    $message->title = __("Xodimlar ro'yxati", []);
                    $message->message = __("Xodimlar ro'yxatini yuklab oling: {link}", ['link' => Html::a($name, $this->downloadUrl . '' . $name)]);
                } else {
                    $message->title = __("Lavozimlar ro'yxati", []);
                    $message->message = __("Lavozimlar ro'yxatini yuklab oling: {link}", ['link' => Html::a($name, $this->downloadUrl . '' . $name)]);
                }

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