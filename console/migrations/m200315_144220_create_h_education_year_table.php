<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_education_year}}`.
 */
class m200315_144220_create_h_education_year_table extends Migration
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
		$description = 'Akademik o`quv yillari';
		$this->createTable('h_education_year', [
            'code' => $this->string(64)->notNull()->unique(),
			'name' => $this->string(256)->notNull(),
			'current_status' => $this->integer(3)->defaultValue(0),
			'_semestr_type'=>$this->string(64),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		$this->addPrimaryKey('pk_h_education_year_code', 'h_education_year', ['code']);
		
		/*$this->addForeignKey(
            'fk_h_semestr_type_education_year_fkey',
            'h_education_year',
            '_semestr_type',
            'h_semestr_type',
            'code',
            'CASCADE'
        );*/
		$this->addCommentOnTable('h_education_year', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('h_education_year');
    }
}
