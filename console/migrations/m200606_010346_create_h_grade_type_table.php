<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_grade_type}}`.
 */
class m200606_010346_create_h_grade_type_table extends Migration
{

    public function safeUp()
    {

        if ($this->db->schema->getTableSchema('h_grade_type', true) !== null) {
            $this->dropTable('h_grade_type');
            \common\models\system\SystemClassifier::deleteAll(['classifier' => 'h_grade_type']);
        }

        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'Baho turlarining nomlari';
		$this->createTable('h_grade_type', [
			'id' => $this->primaryKey(),
			'code' => $this->string(64)->notNull(),
			'name' => $this->string(256)->notNull(),
			'_marking_system'=>$this->string(64)->notNull(),
            'min_border'=>$this->integer(3),
            'max_border'=>$this->integer(3),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->createIndex('un_h_grade_type_uniq',
              'h_grade_type',
              ['code', '_marking_system'],
              true);
			  
		$this->addForeignKey(
            'fk_h_marking_system_h_grade_type_fkey',
            'h_grade_type',
            '_marking_system',
            'h_marking_system',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('h_grade_type', $description);
		
    }

    public function safeDown()
    {
        $this->dropTable('h_grade_type');
        \common\models\system\SystemClassifier::createClassifiersTables($this);
    }
}
