<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_scientific_platform_criteria}}`.
 */
class m210105_184532_create_e_scientific_platform_criteria_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Xalqaro platformalardagi faolligini baholash mezonlari';
		
        $this->createTable('e_scientific_platform_criteria', [
            'id' => $this->primaryKey(),
			'_education_year'=>$this->string(64)->notNull(),
			'_publication_type_table'=>$this->string(64)->notNull(),
			'_scientific_platform'=>$this->string(64)->notNull(),
			'_criteria_type'=>$this->string(64)->notNull(),
			'mark_value'=>$this->integer()->notNull(),
			'coefficient'=>$this->integer(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_education_year_e_scientific_platform_criteria_fkey',
            'e_scientific_platform_criteria',
            '_education_year',
            'e_education_year',
            'code',
            'NO ACTION',
            'CASCADE'
        );
		
		$this->addForeignKey(
            'fk_h_scientific_platform_e_scientific_platform_criteria_fkey',
            'e_scientific_platform_criteria',
            '_scientific_platform',
            'h_scientific_platform',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->createIndex('e_scientific_platform_criteria_uniq',
            'e_scientific_platform_criteria',
            ['_education_year', '_scientific_platform', '_criteria_type'],
            true);
		$this->addCommentOnTable('e_scientific_platform_criteria', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_scientific_platform_criteria');
    }
}
