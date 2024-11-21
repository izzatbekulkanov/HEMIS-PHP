<?php

use yii\db\Migration;

/**
 * Class m200813_124528_alter_table_employee
 */
class m200813_124528_alter_table_employee extends Migration
{

    public function safeUp()
    {
        $this->addColumn(\common\models\employee\EEmployeeMeta::tableName(), '_uid', $this->string()->unique());
        $this->addColumn(\common\models\employee\EEmployeeMeta::tableName(), '_sync', $this->boolean()->defaultValue(false));


        //fix passport number
        $this->db->createCommand("UPDATE e_employee SET passport_number = replace(passport_number, ' ', '')")->execute();
        //fix citizenship
        $this->db->createCommand("UPDATE e_employee SET _citizenship = 11")->execute();
        $this->addColumn(\common\models\employee\EEmployee::tableName(), 'year_of_enter', $this->integer(4)->null()->defaultValue(date('Y')));


    }

    public function safeDown()
    {

        $this->dropColumn(\common\models\employee\EEmployeeMeta::tableName(), '_uid');
        $this->dropColumn(\common\models\employee\EEmployeeMeta::tableName(), '_sync');

        $this->dropColumn(\common\models\employee\EEmployee::tableName(), 'year_of_enter');
    }

}
