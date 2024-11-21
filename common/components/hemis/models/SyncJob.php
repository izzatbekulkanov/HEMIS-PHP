<?php


namespace common\components\hemis\models;


use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiError;
use common\components\hemis\HemisApiSyncModel;
use common\models\system\_BaseModel;
use yii\base\BaseObject;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

class SyncJob extends BaseObject implements RetryableJobInterface
{
    public $class;
    public $type;
    public $id;
    public $limit;
    public $check = false;
    public $retryAfter = 20;

    public function execute($queue)
    {
        /**
         * @var $item HemisApiSyncModel
         * @var $class _BaseModel
         */
        if ($item = $this->getModel()) {
            if ($this->check) {
                $item->checkToApi();
            } else {
                $item->syncToApi();
            }
        }
    }

    public function getTtr()
    {
        return $this->retryAfter;
    }

    public function canRetry($attempt, $error)
    {
        print_r([
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
        ]);

        if ($item = $this->getModel()) {
            if ($error instanceof HemisApiError) {
                SyncLog::registerModel($item, $error->getMessage());
            }

            if ($attempt < $this->limit) {
                return true;
            }
        }

        return false;
    }

    protected function getModel()
    {
        /**
         * @var $class HemisApiSyncModel
         */
        $class = $this->class;
        if ($item = $class::getModel($this->id)) {
            return $item;
        }
    }

}