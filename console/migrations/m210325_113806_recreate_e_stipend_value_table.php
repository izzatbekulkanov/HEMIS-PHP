<?php

use yii\db\Migration;

/**
 * Class m210325_113806_recreate_e_stupend_value_table
 */
class m210325_113806_recreate_e_stipend_value_table extends Migration
{
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('e_stipend_value', true);
		if (isset($table->columns['id'])) {
			$this->dropTable('e_stipend_value');
		}
		
		$tableOptions = null;
		$description = null;
		
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Stipendiya miqdorlari';
		
		$this->createTable('e_stipend_value', [
			'id' => $this->primaryKey(),
			'_stipend_rate' => $this->string(64)->notNull(),
			'stipend_value'=>$this->decimal(10, 1)->notNull(),
			'begin_date' => $this->date(),
			'document'=>$this->string(1024),
			'current_status' => $this->integer(3)->defaultValue(0),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_hh_stipend_rate_e_stipend_value_fkey',
            'e_stipend_value',
            '_stipend_rate',
            'h_stipend_rate',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_stipend_value', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_stipend_value');
    }
}
