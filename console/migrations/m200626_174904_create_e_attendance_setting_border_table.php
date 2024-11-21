<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%attendance_setting_border}}`.
 */
class m200626_174904_create_e_attendance_setting_border_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Davomatga chora ko\'rish chegaralari';
        \common\models\system\SystemClassifier::createClassifiersTables($this);

        $this->createTable('e_attendance_setting_border', [
            'id' => $this->primaryKey(),
            '_attendance_setting' => $this->string(64)->notNull(),
            '_marking_system' => $this->string(64)->notNull(),
            'min_border' => $this->integer(3),
            'max_border' => $this->integer(3),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('un_e_attendance_setting_border_table_uniq',
            'e_attendance_setting_border',
            ['_attendance_setting', '_marking_system'],
            true);

        $this->addForeignKey(
            'fk_h_attendance_setting_e_attendance_setting_border_fkey',
            'e_attendance_setting_border',
            '_attendance_setting',
            'h_attendance_setting',
            'code',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_marking_system_e_attendance_setting_border_fkey',
            'e_attendance_setting_border',
            '_marking_system',
            'h_marking_system',
            'code',
            'CASCADE'
        );

        $this->addCommentOnTable('e_attendance_setting_border', $description);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_attendance_setting_border');
    }
}
