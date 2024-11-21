<?php

use yii\db\Migration;

/**
 * Class m210915_173227_clean_report_contract
 */
class m210915_173227_clean_report_contract extends Migration
{
    public function safeUp()
    {
        \common\models\report\ReportContract::deleteAll();
    }


    public function safeDown()
    {
    }
}
