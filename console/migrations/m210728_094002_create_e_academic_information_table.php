<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_academic_information}}`.
 */
class m210728_094002_create_e_academic_information_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Akademik ma`lumotnoma ma`lumotlari';

        $this->createTable('e_academic_information', [
            'id' => $this->primaryKey(),
            '_specialty'=>$this->integer()->notNull(),
            '_student_meta'=>$this->integer()->notNull(),
            '_student'=>$this->integer()->notNull(),
            '_department'=>$this->integer()->notNull(),
            '_group'=>$this->integer()->notNull(),
            '_education_year'=>$this->string(64),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            '_marking_system'=>$this->string(64)->notNull(),
            '_decree'=>$this->integer(),
            'academic_number'=>$this->string(20),
            'academic_register_number'=>$this->string(30),
            'academic_register_date'=>$this->date(),
            'academic_status' => $this->string(64),
            'filename' => 'jsonb',
            'rector' => $this->string(255),
            'dean' => $this->string(255),
            'secretary' => $this->string(255),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_specialty_e_academic_information_fkey',
            'e_academic_information',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_e_academic_information_fkey',
            'e_academic_information',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_meta_e_academic_information_fkey',
            'e_academic_information',
            '_student_meta',
            'e_student_meta',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_department_e_academic_information_fkey',
            'e_academic_information',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_group_e_academic_information_fkey',
            'e_academic_information',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_education_year_e_academic_information_fkey',
            'e_academic_information',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_h_education_type_e_academic_information_fkey',
            'e_academic_information',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_academic_information_fkey',
            'e_academic_information',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_marking_system_e_academic_information_fkey',
            'e_academic_information',
            '_marking_system',
            'h_marking_system',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_decree_e_academic_information_fkey',
            'e_academic_information',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->createIndex('e_academic_information_unique',
            'e_academic_information',
            ['_student', '_student_meta'],
            true);

        $this->addCommentOnTable('e_academic_information', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_academic_information');
    }
}
