<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m201126_122753_alter_sync_tables
 */
class m201126_122753_alter_sync_tables extends Migration
{
    protected $tables = [
        'e_university',
        'e_department',
        'e_student',
        'e_student_diploma',
        'e_employee',
        'e_employee_meta',
        'e_system_classifier',
    ];

    public function safeUp()
    {
        foreach ($this->tables as $table) {
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
            $this->dropColumn($table, '_sync_diff');
            $this->dropColumn($table, '_sync_status');
            $this->dropColumn($table, '_sync_date');
        }
    }
}
