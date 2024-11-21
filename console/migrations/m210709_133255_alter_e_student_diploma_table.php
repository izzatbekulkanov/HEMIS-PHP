<?php

use yii\db\Migration;

/**
 * Class m210709_133255_alter_e_student_diploma_table
 */
class m210709_133255_alter_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            \common\models\archive\EStudentDiploma::tableName(),
            'qualification_data',
            $this->string(1700)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(
            \common\models\archive\EStudentDiploma::tableName(),
            'qualification_data',
            $this->string(1500)
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210709_133255_alter_e_student_diploma_table cannot be reverted.\n";

        return false;
    }
    */
}
