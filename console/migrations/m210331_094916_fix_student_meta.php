<?php

use yii\db\Migration;

/**
 * Class m210331_094916_fix_student_meta
 */
class m210331_094916_fix_student_meta extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $command = $this->db->createCommand('update e_student_meta
set active= true
where id in (
    SELECT m1.id
    FROM e_student s
             JOIN e_student_meta m1 ON (s.id = m1._student)
             LEFT OUTER JOIN e_student_meta m2 ON (
            s.id = m2._student AND (m1.created_at < m2.created_at OR (m1.created_at = m2.created_at AND m1.id < m2.id)))
    WHERE m2.id IS NULL
      and s.id in (select s.id
                   from e_student s
                            left join e_student_meta m on s.id = m._student and m.active = true
                   where m.active ISNULL
    ));');
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
