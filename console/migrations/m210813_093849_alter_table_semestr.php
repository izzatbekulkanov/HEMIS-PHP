<?php

use common\models\curriculum\Semester;
use yii\db\Migration;

/**
 * Class m210813_093849_alter_table_semestr
 */
class m210813_093849_alter_table_semestr extends Migration
{
    public function safeUp()
    {
        $this->addColumn(Semester::tableName(), '_level', $this->string(64)->null());
        $this->addForeignKey(
            'fk_h_semestr_level',
            Semester::tableName(),
            '_level',
            'h_course',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        if ($count = $this->getDb()->createCommand("
UPDATE h_semestr
SET _level=e_curriculum_week._level
FROM e_curriculum_week
WHERE h_semestr.code = e_curriculum_week._semester and h_semestr._curriculum = e_curriculum_week._curriculum and e_curriculum_week._level is not null
")->execute()) {
            echo "$count semesters updated by level\n";
        }
    }


    public function safeDown()
    {
        $this->dropColumn(Semester::tableName(), '_level');
    }
}
