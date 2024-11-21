<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%curriculum_week}}`.
 */
class m200407_084917_create_e_curriculum_week_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$description = 'O`quv reja haftalari';
		
		$this->createTable('e_curriculum_week', [
            'id' => $this->primaryKey(),
			'start_date' => $this->date()->notNull(),
			'end_date' => $this->date()->notNull(),
            '_curriculum'=>$this->integer()->notNull(),
			'_semester'=>$this->string(64)->notNull(),
			'_level'=>$this->string(64)->notNull(),
			'_education_week_type'=>$this->string(64)->notNull(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_curriculum_curriculum_week_fkey',
            'e_curriculum_week',
            '_curriculum',
            'e_curriculum',
            'id',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_course_curriculum_week_fkey',
            'e_curriculum_week',
            '_level',
            'h_course',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_education_week_type_curriculum_week_fkey',
            'e_curriculum_week',
            '_education_week_type',
            'h_education_week_type',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_curriculum_week', $description);
	}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum_week');
    }
}
