<?php

use yii\db\Migration;

/**
 * Class m210507_060414_fix_student_passport_en_translations
 */
class m210507_060414_fix_student_passport_en_translations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
select * from e_student where _translations @> '{"first_name_en": []}' or _translations @> '{"second_name_en": []}'
SQL;

        $students = \common\models\student\EStudent::findBySql($sql)->all();
        /** @var \common\models\student\EStudent $student */
        foreach ($students as $student) {
            $translations = $student->_translations;
            if (
                (isset($translations['first_name_en']) && !is_string($translations['first_name_en']))
                || (isset($translations['second_name_en']) && !is_string($translations['second_name_en']))
            ) {
                $translations['first_name_en'] = '';
                $translations['second_name_en'] = '';
                $student->updateAttributes(['_translations' => $translations]);
                echo "Student `{$student->id}` passport data fixed\n";
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m210507_060414_fix_student_passport_en_translations cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210507_060414_fix_student_passport_en_translations cannot be reverted.\n";

        return false;
    }
    */
}
