<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_scientific_platform_profile}}`.
 */
class m210105_172100_create_e_scientific_platform_profile_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Xodimlarning xalqaro platformalardagi profillari to`g`risida ma`lumot';
		
		\common\models\system\SystemClassifier::createClassifiersTables($this);
		
        $this->createTable('e_scientific_platform_profile', [
            'id' => $this->primaryKey(),
			'_employee'=>$this->integer()->notNull(),
			'_scientific_platform'=>$this->string(64)->notNull(),
			'profile_link'=>$this->string(512)->notNull(),
			'h_index'=>$this->integer(),
			'publication_work_count'=>$this->integer(),
			'citation_count'=>$this->integer(),
			'is_checked'=>$this->boolean(),
			'is_checked_date'=>$this->dateTime(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_e_employee_e_scientific_platform_profile_fkey',
            'e_scientific_platform_profile',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_scientific_platform_e_scientific_platform_profile_fkey',
            'e_scientific_platform_profile',
            '_scientific_platform',
            'h_scientific_platform',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		
		
		$this->createIndex('e_scientific_platform_profile_uniq',
            'e_scientific_platform_profile',
            ['_employee', '_scientific_platform'],
            true);
		
		$this->addCommentOnTable('e_scientific_platform_profile', $description);
    }
	
	

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_scientific_platform_profile');
    }
}
