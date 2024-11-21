<?php

use yii\db\Migration;

/**
 * Class m210914_141641_alter_table_student_unique
 */
class m210914_141641_alter_table_student_unique extends Migration
{

    public function safeUp()
    {
        Yii::$app
            ->db
            ->createCommand("alter table e_student drop constraint IF EXISTS e_student_passport_pin_key")
            ->execute();

        Yii::$app
            ->db
            ->createCommand("alter table e_student drop constraint IF EXISTS e_student_passport_number_key")
            ->execute();

        $this->createIndex('e_student_passport_pin_key_uniq', 'e_student', ['passport_pin', 'year_of_enter'], true);
        $this->createIndex('e_student_passport_number_key_uniq', 'e_student', ['passport_number', 'year_of_enter'], true);
    }


    public function safeDown()
    {
        $this->dropIndex('e_student_passport_number_key_uniq', 'e_student');
        $this->dropIndex('e_student_passport_pin_key_uniq', 'e_student');
        $this->alterColumn('e_student', 'passport_number', $this->string(14)->unique());
        $this->alterColumn('e_student', 'passport_pin', $this->string(20)->unique());
    }
}
