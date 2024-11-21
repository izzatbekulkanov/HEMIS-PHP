<?php

use common\models\archive\EDiplomaBlank;
use yii\db\Migration;

/**
 * Class m210601_105946_alter_e_diploma_blank_table
 */
class m210601_105946_alter_e_diploma_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(EDiplomaBlank::tableName(), 'number', $this->string(64)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210601_105946_alter_e_diploma_blank_table cannot be reverted.\n";

        return false;
    }
    */
}
