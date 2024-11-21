<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_curriculum}}`.
 */
class m200316_011716_create_e_curriculum_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
		$description = null;
		
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		$description = 'OTM o`quv rejalari';
		
		$this->createTable('e_curriculum', [
            'id' => $this->primaryKey(),
			'name' => $this->string(256)->notNull(),
			'_department'=>$this->integer()->notNull(),
            '_education_type'=>$this->string(64)->notNull(),
            '_education_form'=>$this->string(64)->notNull(),
            '_specialty'=>$this->string(64)->notNull(),
            '_marking_system'=>$this->string(64)->notNull(),
            '_education_year'=>$this->string(64)->notNull(),
            'semester_count'=>$this->integer(3)->notNull(),
            'education_period'=>$this->integer(3)->notNull(),
			'autumn_start_date' => $this->date()->notNull(),
			'autumn_end_date' => $this->date()->notNull(),
			'spring_start_date' => $this->date()->notNull(),
			'spring_end_date' => $this->date()->notNull(),
            'accepted' => $this->boolean()->defaultValue(false),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_c_department_fkey',
            'e_curriculum',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_c_education_type_fkey',
            'e_curriculum',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_c_education_form_fkey',
            'e_curriculum',
            '_education_form',
            'h_education_form',
            'code',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_c_specialty_fkey',
            'e_curriculum',
            '_specialty',
            'e_specialty',
            'code',
            'CASCADE'
        );
		/*$this->addForeignKey(
            'fk_c_marking_system_fkey',
            'e_curriculum',
            '_marking_system',
            'h_marking_system',
            'code',
            'CASCADE'
        );*/
		$this->addForeignKey(
            'fk_c_education_year_fkey',
            'e_curriculum',
            '_education_year',
            'h_education_year',
            'code',
            'CASCADE'
        );
		$this->addCommentOnTable('e_curriculum', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_curriculum');
    }
}
