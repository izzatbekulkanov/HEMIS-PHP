<?php

use yii\db\Migration;

/**
 * Class m200926_072059_alter_curriculum_subject
 */
class m200926_072059_alter_curriculum_subject extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('e_curriculum_subject', 'credit', $this->decimal(10, 1)->null());
    } 
	
	public function safeDown()
    {
        $this->alterColumn('e_curriculum_subject', 'credit', $this->integer());
    }
}
