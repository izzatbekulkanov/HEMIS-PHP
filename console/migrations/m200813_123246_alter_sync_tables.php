<?php

use yii\db\Migration;

/**
 * Class m200813_123246_alter_sync_tables
 */
class m200813_123246_alter_sync_tables extends Migration
{
    public function safeUp()
    {
        $this->addColumn(\common\components\hemis\models\SyncLog::tableName(), 'delete', $this->boolean()->defaultValue(false));

    }

    public function safeDown()
    {
        $this->dropColumn(\common\components\hemis\models\SyncLog::tableName(), 'delete');
    }
}
