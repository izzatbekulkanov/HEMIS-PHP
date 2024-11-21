<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_employee_competition}}`.
 */
class m210305_152604_create_e_employee_competition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'O\'qituvchilarni tanlovdan o\'tishi';

        $this->createTable(
            '{{%e_employee_competition}}',
            [
                'id' => $this->primaryKey(),
                '_employee' => $this->integer()->notNull(),
                '_employee_position' => $this->string(64)->notNull(),
                '_translations' => 'jsonb',
                'election_date' => $this->date()->notNull(),
                'document' => $this->string(1024),
                'active' => $this->boolean()->defaultValue(true),
                'updated_at' => $this->dateTime()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_e_employee_e_employee_competition',
            'e_employee_competition',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_employee_position_type_e_employee_competition',
            'e_employee_competition',
            '_employee_position',
            'h_teacher_position_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_employee_competition', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_employee_competition}}');
    }
}
