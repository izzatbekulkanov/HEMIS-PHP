<?php

use yii\db\Migration;

/**
 * Class m210705_130700_alter_e_student_diploma_table
 */
class m210705_130700_alter_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (\common\models\archive\EStudentDiploma::find()->all() as $item) {
            $item->generateDiplomaLinks();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Do nothing
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210705_130700_alter_e_student_diploma_table cannot be reverted.\n";

        return false;
    }
    */
}
