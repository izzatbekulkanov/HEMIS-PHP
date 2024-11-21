<?php

use yii\db\Migration;
use common\models\system\SystemClassifier;
/**
 * Handles the creation of table `{{%e_student_scholarship}}`.
 */
class m210502_074115_create_e_student_scholarship_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Talabalarning stipendiya miqdorlari';
		SystemClassifier::createClassifiersTables($this);
        $this->createTable('e_student_scholarship', [
            'id' => $this->primaryKey(),
            '_department'=>$this->integer()->notNull(),
            '_specialty'=>$this->integer()->notNull(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            '_curriculum'=>$this->integer()->notNull(),
            '_group'=>$this->integer()->notNull(),
            '_payment_form'=>$this->string(64)->notNull(),
            '_student'=>$this->integer()->notNull(),
            '_semester'=>$this->string(64)->notNull(),
            '_education_year'=>$this->string(64),
            '_stipend_rate' => $this->string(64)->notNull(),
            '_decree'=>$this->integer(),
            'summa'=>$this->money()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->null(),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_department_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_specialty_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_specialty',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_type_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_education_form_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_curriculum_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_curriculum',
            'e_curriculum',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_group',
            'e_group',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_payment_form_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_payment_form',
            'h_payment_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_student',
            'e_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_education_year_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_stipend_rate_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_stipend_rate',
            'h_stipend_rate',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_decree_e_student_scholarship_fkey',
            'e_student_scholarship',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->createIndex('e_student_scholarship_unique',
			'e_student_scholarship',
			['_student', '_semester', '_education_year'],
			true);
        $this->addCommentOnTable('e_student_scholarship', $description);
    }

    public function safeDown()
    {
        $this->dropTable('e_student_scholarship');
    }
}
