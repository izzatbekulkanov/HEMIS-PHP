<?php

use yii\db\Migration;

/**
 * Class m210724_095244_alter_e_student_diploma_table
 */
class m210724_095244_alter_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%e_student_diploma}}', 'graduate_qualifying_work', $this->string(500));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%e_student_diploma}}', 'graduate_qualifying_work', $this->string(300));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210724_095244_alter_e_student_diploma_table cannot be reverted.\n";

        return false;
    }
    */
}
