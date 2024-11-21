<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_contract_type}}`.
 */
class m210317_130701_create_e_contract_type_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		\common\models\system\SystemClassifier::createClassifiersTables($this);
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Tolov-shartnoma turlari';
		
		$this->createTable('e_contract_type', [
			'id' => $this->primaryKey(),
			'_contract_type' => $this->string(64)->notNull(),
			'coef'=>$this->decimal(10, 1)->notNull(),
			'current_status' => $this->integer(3)->defaultValue(0),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_h_contract_type_e_contract_type_fkey',
            'e_contract_type',
            '_contract_type',
            'h_contract_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_contract_type', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_contract_type');
    }
	
}
