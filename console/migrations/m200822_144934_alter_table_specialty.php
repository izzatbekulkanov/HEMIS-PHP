<?php

use yii\db\Migration;
use \yii\db\Exception;

/**
 * Class m200822_144934_alter_table_specialty
 */
class m200822_144934_alter_table_specialty extends Migration
{
    public function safeUp()
    {
        try {
            Yii::$app
                ->db
                ->createCommand("alter table e_specialty drop constraint e_specialty_code_key;")
                ->execute();
        } catch (Exception $e) {
            $e->getMessage();
        }


        try {
            $this->dropIndex('e_specialty_code_key', 'e_specialty');
        } catch (Exception $e) {
            $e->getMessage();
        }
        try {
            $this->dropPrimaryKey('pk_e_special_code', 'e_specialty');
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public function safeDown()
    {
        //$this->addPrimaryKey('pk_e_special_code', 'e_specialty', ['code']);
    }

}
