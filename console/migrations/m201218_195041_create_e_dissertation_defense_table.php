<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_dissertation_defense}}`.
 */
class m201218_195041_create_e_dissertation_defense_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Dissertatsiya himoyalari to`g`risida ma`lumot';
		
        $this->createTable('e_dissertation_defense', [
            'id' => $this->primaryKey(),
			'_doctorate_student'=>$this->integer()->notNull(),
			'_science_branch_id' => $this->string(36)->notNull(),
			'_specialty_id' => $this->integer()->notNull(),
			'defense_date' => $this->date()->notNull(),
			'defense_place' => $this->string(500)->notNull(),
			'approved_date'=>$this->date(),
			'diploma_number'=>$this->string(20)->notNull(),
			'diploma_given_date'=>$this->date(),
			'diploma_given_by_whom'=>$this->string(500)->notNull(),
			'register_number'=>$this->string(30)->notNull(),
			'filename' => 'jsonb',
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
        $this->addForeignKey(
            'fk_e_doctorate_student_e_dissertation_defense_fkey',
            'e_dissertation_defense',
            '_doctorate_student',
            'e_doctorate_student',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_science_branch_e_dissertation_defense_fkey',
            'e_dissertation_defense',
            '_science_branch_id',
            'h_science_branch',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_specialty_e_dissertation_defense_fkey',
            'e_dissertation_defense',
            '_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
        $this->addCommentOnTable('e_dissertation_defense', $description);
    }
	
	public function safeDown()
    {
        $this->dropTable('e_dissertation_defense');
    }
}
