<?php

use common\models\employee\EEmployeeMeta;
use yii\db\Migration;

/**
 * Class m200814_124050_alter_table_employee_meta
 */
class m200814_124050_alter_table_employee_meta extends Migration
{
    public function safeUp()
    {

        $this->addColumn(EEmployeeMeta::tableName(), '_employee_type', $this->string(64)->null());

        $this->addForeignKey(
            'fk_e_employee_meta_employee_type',
            'e_employee_meta',
            '_employee_type',
            'h_employee_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

    }

    public function safeDown()
    {
        $this->dropColumn(EEmployeeMeta::tableName(), '_employee_type');
    }
}
