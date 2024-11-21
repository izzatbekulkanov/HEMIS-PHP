<?php

use yii\db\Migration;

/**
 * Class m210803_192506_alter_e_academic_information_table
 */
class m210803_192506_alter_e_academic_information_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn(
            'e_academic_information',
            'department_name',
            'faculty_name'
        );
    }

    public function safeDown()
    {
        $this->renameColumn(
            'e_academic_information',
            'faculty_name',
            'department_name'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210803_192506_alter_e_academic_information_table cannot be reverted.\n";

        return false;
    }
    */
}
