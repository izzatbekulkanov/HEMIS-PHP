<?php

use yii\db\Migration;

/**
 * Class m210507_051332_alter_e_academic_record_table
 */
class m210507_051332_alter_e_academic_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(\common\models\archive\EAcademicRecord::tableName(), 'grade', $this->decimal(5, 2)->notNull());
        $this->alterColumn(\common\models\archive\EAcademicRecord::tableName(), 'total_point', $this->decimal(5, 2)->notNull());
        $this->alterColumn(\common\models\archive\EAcademicRecord::tableName(), 'credit', $this->decimal(5, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(\common\models\archive\EAcademicRecord::tableName(), 'grade', $this->integer()->notNull());
        $this->alterColumn(\common\models\archive\EAcademicRecord::tableName(), 'total_point', $this->integer()->notNull());
        $this->alterColumn(\common\models\archive\EAcademicRecord::tableName(), 'credit', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210507_051332_alter_e_academic_record_table cannot be reverted.\n";

        return false;
    }
    */
}
