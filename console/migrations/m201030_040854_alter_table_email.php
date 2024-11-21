<?php

use yii\db\Migration;

/**
 * Class m201030_040854_alter_table_email
 */
class m201030_040854_alter_table_email extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try{
            Yii::$app
                ->db
                ->createCommand("ALTER TABLE e_admin DROP constraint e_admin_email_key")
                ->execute();
        }catch (Exception $e){

        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
