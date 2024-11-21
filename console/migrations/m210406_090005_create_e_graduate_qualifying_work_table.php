<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_graduate_qualifying_work}}`.
 */
class m210406_090005_create_e_graduate_qualifying_work_table extends Migration
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
        $description = 'Bitiruv malakaviy ishi';

        $this->createTable('{{%e_graduate_qualifying_work}}', [
            'id' => $this->primaryKey(),
            '_faculty' => $this->integer()->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            '_specialty' => $this->integer()->notNull(),
            '_student' => $this->integer()->notNull(),
            'work_name' => $this->string(255)->notNull(),
            'supervisor_name' => $this->string(255)->notNull(),
            'supervisor_work' => $this->string(255),
            'advisor_name' => $this->string(255)->notNull(),
            'advisor_work' => $this->string(255),
            '_education_year' => $this->string(64)->notNull(),
            '_translations' => 'jsonb',
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('e_graduate_qualifying_work', $description);
        $this->addForeignKey(
            'fk_e_department_e_graduate_qualifying_work_fkey',
            'e_graduate_qualifying_work',
            '_faculty',
            'e_department',
            'id'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_graduate_qualifying_work_fkey',
            'e_graduate_qualifying_work',
            '_specialty',
            'e_specialty',
            'id'
        );
        $this->addForeignKey(
            'fk_e_student_e_graduate_qualifying_work_fkey',
            'e_graduate_qualifying_work',
            '_student',
            'e_student',
            'id'
        );

        $this->addForeignKey(
            'fk_h_education_type_e_graduate_qualifying_work_fkey',
            'e_graduate_qualifying_work',
            '_education_type',
            'h_education_type',
            'code'
        );
        $this->addForeignKey(
            'fk_h_education_year_e_graduate_qualifying_work_fkey',
            'e_graduate_qualifying_work',
            '_education_year',
            'h_education_year',
            'code'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_graduate_qualifying_work}}');
    }
}
