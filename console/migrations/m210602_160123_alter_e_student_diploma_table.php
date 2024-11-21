<?php

use common\models\archive\EDiplomaBlank;
use yii\db\Migration;

/**
 * Class m210602_160123_alter_e_student_diploma_table
 */
class m210602_160123_alter_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(\common\models\archive\EStudentDiploma::tableName(), 'marking_system', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m210602_160123_alter_e_student_diploma_table cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210602_160123_alter_e_student_diploma_table cannot be reverted.\n";

        return false;
    }
    */
}
