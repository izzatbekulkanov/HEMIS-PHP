<?php

use common\models\archive\EAcademicRecord;
use common\models\student\EStudentMeta;
use yii\db\Migration;

/**
 * Class m210223_093302_alter_table_e_academic_record
 */
class m210223_093302_alter_table_e_academic_record extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(EAcademicRecord::tableName(), '_employee', $this->integer()->null());
        $this->alterColumn(EAcademicRecord::tableName(), 'employee_name', $this->string(256)->null());
        $this->alterColumn(EAcademicRecord::tableName(), '_semester', $this->string(64)->null());
        $this->alterColumn(EAcademicRecord::tableName(), 'semester_name', $this->string(256)->null());
        $this->addColumn(EStudentMeta::tableName(), 'accreditation_accepted', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        $this->alterColumn(EAcademicRecord::tableName(), '_employee', $this->integer()->notNull());
//        $this->alterColumn(EAcademicRecord::tableName(), 'employee_name', $this->string(256)->notNull());
//        $this->alterColumn(EAcademicRecord::tableName(), '_semester', $this->string(64)->notNull());
//        $this->alterColumn(EAcademicRecord::tableName(), 'semester_name', $this->string(256)->notNull());
        //$this->dropColumn(EStudentMeta::tableName(), 'accreditation_accepted');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210223_093302_alter_table_e_academic_record cannot be reverted.\n";

        return false;
    }
    */
}
