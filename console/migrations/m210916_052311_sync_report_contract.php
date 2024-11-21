<?php

use yii\db\Migration;

/**
 * Class m210916_052311_sync_report_contract
 */
class m210916_052311_sync_report_contract extends Migration
{
    public function safeUp()
    {
        \common\models\report\ReportContract::updateAll(['_sync' => false]);
    }


    public function safeDown()
    {
    }
}
