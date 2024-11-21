<?php

use yii\db\Migration;

/**
 * Class m210126_195106_fix_student_meta
 */
class m210126_195106_fix_student_meta extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $command = $this->db->createCommand('update e_student_meta
set active= true
where _student in (select s.id
                   from e_student s
                            left join e_student_meta esm on s.id = esm._student and esm.active = true
                   where esm.active ISNULL)');
        if ($result = $command->execute()) {
            echo "$result students meta activated\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
