<?php

use yii\db\Migration;

/**
 * Class m210705_094541_alter_table_student_diploma
 */
class m210705_094541_alter_table_student_diploma extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_student_diploma', 'hash', $this->string(36)->unique());
        $this->createIndex('idx_student_diploma_hash', 'e_student_diploma', ['hash'], true);
        foreach (\common\models\archive\EStudentDiploma::find()->all() as $item) {
            $item->updateAttributes(['hash' => gen_uuid()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_student_diploma', 'hash');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210705_094541_alter_table_student_diploma cannot be reverted.\n";

        return false;
    }
    */
}
