<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_employee_professional_development}}`.
 */
class m210224_140819_create_e_employee_professional_development_table extends Migration
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
        $description = 'O\'qituvchilarni malaka oshirish faoliyati';

        $this->createTable(
            '{{%e_employee_professional_development}}',
            [
                'id' => $this->primaryKey(),
                '_employee' => $this->integer()->notNull(),
                '_employee_position' => $this->string(64)->notNull(),
                'training_title' => $this->string(1024),
                'training_year' => $this->integer(4)->notNull(),
                '_training_place' => $this->string(64)->notNull(),
                '_translations' => 'jsonb',
                'begin_date' => $this->date()->notNull(),
                'end_date' => $this->date()->notNull(),
                'document' => $this->string(1024),
                'active' => $this->boolean()->defaultValue(true),
                'updated_at' => $this->dateTime()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_e_employee_e_employee_professional_development',
            'e_employee_professional_development',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_employee_position_type_e_employee_professional_development',
            'e_employee_professional_development',
            '_employee_position',
            'h_teacher_position_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_qualification_e_employee_professional_development',
            'e_employee_professional_development',
            '_training_place',
            'h_qualification',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_employee_professional_development', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_employee_professional_development}}');
    }
}
