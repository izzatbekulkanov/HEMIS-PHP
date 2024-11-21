<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_stipend_value}}`.
 */
class m210317_073604_create_e_stipend_value_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Stipendiya miqdorlari';
		
		
		$this->createTable('e_stipend_value', [
			'id' => $this->primaryKey(),
		], $tableOptions);
		
		$this->addCommentOnTable('e_stipend_value', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_stipend_value');
    }
	
}
