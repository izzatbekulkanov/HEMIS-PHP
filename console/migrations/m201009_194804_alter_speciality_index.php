<?php

use yii\db\Migration;

/**
 * Class m201009_194804_alter_speciality_index
 */
class m201009_194804_alter_speciality_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try{
            Yii::$app
                ->db
                ->createCommand("ALTER TABLE e_specialty DROP constraint e_specialty_code_key")
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
