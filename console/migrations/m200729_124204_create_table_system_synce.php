<?php

use yii\db\Migration;

/**
 * Class m200729_124204_create_table_system_synce
 */
class m200729_124204_create_table_system_synce extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Sinxronizatsiya modellari';

        $this->createTable('e_system_sync_log', [
            'id' => $this->primaryKey(),
            'model' => $this->string(256)->notNull(),
            'model_id' => $this->string(64)->notNull(),
            'description' => $this->string(256),
            'status' => $this->string(16),
            'error' => $this->string(256),
            'created_at' => $this->dateTime()->defaultExpression('NOW()'),
        ], $tableOptions);

        $this->addCommentOnTable('e_attendance_setting_border', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_system_sync_log');
    }
}
