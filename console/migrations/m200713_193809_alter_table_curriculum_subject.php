<?php

use yii\db\Migration;

/**
 * Class m200713_193809_alter_table_curriculum_subject
 */
class m200713_193809_alter_table_curriculum_subject extends Migration
{
	public function safeUp()
    {
       $this->addColumn('e_curriculum_subject', 'at_semester', $this->boolean()->defaultValue(true));
       $this->addColumn('e_curriculum_subject', 'in_group', $this->string(64));
	}

    public function safeDown()
    {
        $this->dropColumn('e_curriculum_subject', 'in_auditorium');
        $this->dropColumn('e_curriculum_subject', 'in_group');
    }
}
