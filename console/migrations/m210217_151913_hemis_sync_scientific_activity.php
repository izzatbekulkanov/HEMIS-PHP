<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m210217_151913_hemis_sync_scientific_activity
 */
class m210217_151913_hemis_sync_scientific_activity extends Migration
{
    protected $tables = [
        'e_scientific_platform_profile',
    ];

    public function safeUp()
    {
        foreach ($this->tables as $table) {
            $this->addColumn($table, '_qid', $this->bigInteger());
            $this->addColumn($table, '_uid', $this->string()->unique());
            $this->addColumn($table, '_sync', $this->boolean()->defaultValue(false));
            $this->addColumn($table, '_sync_diff', 'json');
            $this->addColumn($table, '_sync_date', $this->dateTime()->null());
            $this->addColumn($table, '_sync_status',
                $this
                    ->string(16)
                    ->null()
                    ->defaultValue(HemisApiSyncModel::STATUS_NOT_CHECKED)
            );
        }
    }

    public function safeDown()
    {
        foreach ($this->tables as $table) {
            $this->dropColumn($table, '_qid');
            $this->dropColumn($table, '_uid');
            $this->dropColumn($table, '_sync');
            $this->dropColumn($table, '_sync_diff');
            $this->dropColumn($table, '_sync_date');
            $this->dropColumn($table, '_sync_status');
        }
    }
}
