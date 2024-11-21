<?php

use yii\db\Migration;

/**
 * Class m201009_185150_alter_table_spec
 */
class m201009_185150_alter_table_spec extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app
            ->db
            ->createCommand("DROP INDEX e_special_department_uniq")
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex('e_special_department_uniq',
            'e_specialty',
            ['code', '_department'],
            true);
    }


}
