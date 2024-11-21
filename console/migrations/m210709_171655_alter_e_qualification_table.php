<?php

use yii\db\Migration;

/**
 * Class m210709_171655_alter_e_qualification_table
 */
class m210709_171655_alter_e_qualification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            \common\models\student\EQualification::tableName(),
            'description',
            $this->string(1700)->notNull()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(
            \common\models\student\EQualification::tableName(),
            'description',
            $this->string(1500)->notNull()
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210709_171655_alter_e_qualification_table cannot be reverted.\n";

        return false;
    }
    */
}
