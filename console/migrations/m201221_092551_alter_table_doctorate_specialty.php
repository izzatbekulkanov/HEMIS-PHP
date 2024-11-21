<?php

use yii\db\Migration;

/**
 * Class m201221_092551_alter_table_doctorate_specialty
 */
class m201221_092551_alter_table_doctorate_specialty extends Migration
{
   
    public function safeUp()
    {
		$this->addColumn('e_specialty', '_doctorate_specialty', $this->string(36)->null());
		$this->addForeignKey(
            "fk_e_specialty_doctorate_specialty",
            'e_specialty',
            '_doctorate_specialty',
            'h_science_branch',
            'id',
            'RESTRICT',
            'CASCADE'
        );

    }

    public function safeDown()
    {
       $this->dropColumn('e_specialty', '_doctorate_specialty');
    }
}
