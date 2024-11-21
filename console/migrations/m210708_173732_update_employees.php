<?php

use yii\db\Migration;

/**
 * Class m210708_173732_update_employees
 */
class m210708_173732_update_employees extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->language = \common\components\Config::LANGUAGE_UZBEK;
        foreach (\common\models\employee\EEmployee::find()->all() as $student) {
            $student->save(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210708_173732_update_employees cannot be reverted.\n";

        return false;
    }
    */
}
