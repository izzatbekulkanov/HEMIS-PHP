<?php

use common\models\archive\EGraduateQualifyingWork;
use yii\db\Migration;

/**
 * Class m210414_123646_alter_e_graduate_qualifying_work_table
 */
class m210414_123646_alter_e_graduate_qualifying_work_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(EGraduateQualifyingWork::tableName(), 'advisor_name', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(EGraduateQualifyingWork::tableName(), 'advisor_name', $this->string(255)->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210414_123646_alter_e_graduate_qualifying_work_table cannot be reverted.\n";

        return false;
    }
    */
}
