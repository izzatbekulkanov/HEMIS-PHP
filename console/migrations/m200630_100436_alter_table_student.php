<?php

use yii\db\Migration;

/**
 * Class m200630_100436_alter_table_student
 */
class m200630_100436_alter_table_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_h_gender_student_fkey', 'e_student');
        $this->addForeignKey(
            'fk_h_gender_student_fkey',
            'e_student',
            '_gender',
            'h_gender',
            'code',
            'SET NULL',
            'CASCADE'
        );

        $this->dropForeignKey('fk_h_nationality_student_fkey', 'e_student');
        $this->addForeignKey(
            'fk_h_nationality_student_fkey',
            'e_student',
            '_nationality',
            'h_nationality',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200630_100436_alter_table_student cannot be reverted.\n";

        return false;
    }
    */
}
