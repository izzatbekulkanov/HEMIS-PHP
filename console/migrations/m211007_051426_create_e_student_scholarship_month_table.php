<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_student_scholarship_month}}`.
 */
class m211007_051426_create_e_student_scholarship_month_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning stipendiya miqdorlarining oylar bo`yicha taqsimoti';

        $this->createTable('e_student_scholarship_month', [
            'id' => $this->primaryKey(),
            '_student'=>$this->integer()->notNull(),
            '_student_scholarship'=>$this->integer()->notNull(),
            '_stipend_rate' => $this->string(64)->notNull(),
            '_education_year'=>$this->string(64),
            '_semester'=>$this->string(64)->notNull(),
            'month_name' => $this->date()->notNull(),
            'summa'=>$this->money()->notNull(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey(
            'fk_e_student_e_student_scholarship_month_fkey',
            'e_student_scholarship_month',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_scholarship_e_student_scholarship_month_fkey',
            'e_student_scholarship_month',
            '_student_scholarship',
            'e_student_scholarship',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_stipend_rate_e_student_scholarship_month_fkey',
            'e_student_scholarship_month',
            '_stipend_rate',
            'h_stipend_rate',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_student_scholarship_month_fkey',
            'e_student_scholarship_month',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->createIndex('e_student_scholarship_month_unique',
            'e_student_scholarship_month',
            ['_student', '_semester', '_education_year', 'month_name'],
            true);
        $this->addCommentOnTable('e_student_scholarship_month', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_scholarship_month');
    }
}
