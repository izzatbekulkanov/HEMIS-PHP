<?php

use common\models\student\EQualification;
use yii\db\Migration;

/**
 * Class m210419_141128_alter_e_qualification_table
 */
class m210419_141128_alter_e_qualification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(EQualification::tableName(), 'description', $this->string(1500)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(EQualification::tableName(), 'description', $this->string(1000)->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210419_141128_alter_e_qualification_table cannot be reverted.\n";

        return false;
    }
    */
}
