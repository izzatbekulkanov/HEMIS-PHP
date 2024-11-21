<?php

use common\models\student\EStudentMeta;
use yii\db\Migration;

/**
 * Class m210124_095328_alter_table_student_meta
 */
class m210124_095328_alter_table_student_meta extends Migration
{

    public function safeUp()
    {
        /**
         * @var $moreThanOnes EStudentMeta[]
         */
        $moreThanOnes = EStudentMeta::find()
            ->select(['id', new \yii\db\Expression("concat(_curriculum,'-', _student,'-', _education_type,'-', _education_year,'-', _semestr,'-', _student_status) as search")])
            ->orderBy(['search' => SORT_ASC])
            ->all();

        $before = null;
        foreach ($moreThanOnes as $item) {
            if ($before == $item->search) {
                if ($item->delete()) {
                    echo "{$item->id} student meta deleted\n";
                }
            }
            $before = $item->search;
        }

        $this->createIndex('idx_unique_student_data', 'e_student_meta', ['_curriculum', '_student', '_education_type', '_education_year', '_semestr', '_student_status'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_unique_student_data', 'e_student_meta');
    }

}
