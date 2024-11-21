<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_criteria_template}}`.
 */
class m210106_103850_create_e_criteria_template_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Kriteriylar shabloni';
		
        $this->createTable('e_criteria_template', [
            'id' => $this->primaryKey(),
			'_publication_type_table'=>$this->string(64)->notNull(),
			'_publication_methodical_type'=>$this->string(64),
			'_publication_scientific_type'=>$this->string(64),
			'_publication_property_type'=>$this->string(64),
			'_in_publication_database'=>$this->integer(3)->defaultValue(0),
			'exist_certificate'=>$this->integer(3)->defaultValue(0),
			'mark_value'=>$this->integer()->notNull(),
			'_scientific_platform'=>$this->string(64),
			'_criteria_type'=>$this->string(64),
			'coefficient'=>$this->integer(),
			'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
			'_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
		
		$this->addForeignKey(
            'fk_h_methodical_publication_type_e_publication_criteria_fkey',
            'e_criteria_template',
            '_publication_methodical_type',
            'h_methodical_publication_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_scientific_publication_type_e_criteria_template_fkey',
            'e_criteria_template',
            '_publication_scientific_type',
            'h_scientific_publication_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h_patient_type_e_criteria_template_fkey',
            'e_criteria_template',
            '_publication_property_type',
            'h_patient_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_h__scientific_platform_e_criteria_template_fkey',
            'e_criteria_template',
            '_scientific_platform',
            'h_scientific_platform',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		$this->addCommentOnTable('e_criteria_template', $description);
		
		$this->batchInsert('e_criteria_template',
            [
				'_publication_type_table', 
				'_publication_methodical_type', 
				'_publication_scientific_type', 
				'_publication_property_type', 
				'_in_publication_database', 
				'exist_certificate', 
				'mark_value', 
				'_scientific_platform', 
				'_criteria_type', 
				'coefficient', 
				'updated_at', 
				'created_at'
			],
            [
                ['11', '11', null, null, 0, 0, 30, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '11', null, null, 0, 1, 60, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '12', null, null, 0, 0, 30, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '12', null, null, 0, 1, 60, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '13', null, null, 0, 0, 20, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '13', null, null, 0, 1, 40, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '14', null, null, 0, 0, 10, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['11', '14', null, null, 0, 1, 20, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				
				['12', null, '11',  null, 0, 0, 40, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '11', null, 1, 0, 80, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '13', null, 0, 0, 20, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '13',  null, 1, 0, 40, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '12', null, 0, 0, 15, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '12', null, 1, 0, 30, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '14', null, 0, 0, 15, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '14', null, 1, 0, 30, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '16', null, 0, 0, 10, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['12', null, '15', null, 0, 0, 10, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				
				['13', null, null, '11', 0, 0, 100, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['13', null, null, '12', 0, 0, 80, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['13', null, null, '13', 0, 0, 80, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['13', null, null, '14', 0, 0, 80, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['13', null, null, '15', 0, 0, 20, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['13', null, null, '16', 0, 0, 20, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['13', null, null, '17', 0, 0, 20, null, null, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				
				['14', null, null, null, 0, 0, 10, 11, 11, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['14', null, null, null, 0, 0, 10, 11, 12, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['14', null, null, null, 0, 0, 10, 13, 11, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['14', null, null, null, 0, 0, 10, 13, 12, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['14', null, null, null, 0, 0, 10, 12, 11, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['14', null, null, null, 0, 0, 10, 12, 12, null, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
			] 
        ); 
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('e_criteria_template', 
            ['in', '_publication_type_table', 
                [
                    ['11'],
                    ['12'],
                    ['13'],
                    ['14'],
                ],
            ]
        ); 
		$this->dropTable('e_criteria_template');
    }
}
