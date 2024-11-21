<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_minimum_wage}}`.
 */
class m210317_072819_create_e_minimum_wage_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Mehnatga haq to`lashning eng kam miqdori';
		
		$table = Yii::$app->db->schema->getTableSchema('e_minimum_wage', true);
		
		if (isset($table->columns['id'])) {
			$this->dropTable('e_minimum_wage');
		}
			
		$this->createTable('e_minimum_wage', [
				'id' => $this->primaryKey(),
				'name'=>$this->decimal(10, 1)->notNull(),
				'begin_date' => $this->date(),
				'document'=>$this->string(1024),
				'current_status' => $this->integer(3)->defaultValue(0),
				'position' => $this->integer(3)->defaultValue(0),
				'active' => $this->boolean()->defaultValue(true),
				'_translations' => 'jsonb',
				'updated_at' => $this->dateTime()->notNull(),
				'created_at' => $this->dateTime()->notNull(),
			], $tableOptions);
			$this->addCommentOnTable('e_minimum_wage', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_minimum_wage');
    }
}
