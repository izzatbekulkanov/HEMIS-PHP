<?php

use yii\db\Migration;

/**
 * Class m210705_181343_alter_e_graduate_qualifying_work_table
 */
class m210705_181343_alter_e_graduate_qualifying_work_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            \common\models\archive\EGraduateQualifyingWork::tableName(),
            'work_name',
            $this->string(500)->notNull()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(
            \common\models\archive\EGraduateQualifyingWork::tableName(),
            'work_name',
            $this->string(255)->notNull()
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210705_181343_alter_e_graduate_qualifying_work_table cannot be reverted.\n";

        return false;
    }
    */
}
