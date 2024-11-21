<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_specialty}}`.
 */
class m200315_184834_create_e_specialty_table extends Migration
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
		$description = 'OTMdagi ta`lim yo`nalishlari va ixtisosliklari';
		$this->createTable('e_specialty', [
            'code' => $this->string(64)->notNull()->unique(),
			'name' => $this->string(256)->notNull(),
			'parent_code' => $this->string(64),
			'_department'=>$this->integer(),
            '_education_type'=>$this->string(64)->notNull(),
            '_knowledge_type'=>$this->string(64),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addPrimaryKey('pk_e_special_code', 'e_specialty', ['code']);
		
        $this->addForeignKey(
            'fk_e_department_id_fkey',
            'e_specialty',
            '_department',
            'e_department',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_s_education_type_fkey',
            'e_specialty',
            '_education_type',
            'h_education_type',
            'code',
            'CASCADE'
        );
		
		$this->addCommentOnTable('e_specialty', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_specialty');
    }
}
