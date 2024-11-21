<?php

use yii\db\Migration;

/**
 * Class m210108_011525_alter_table_scientific_platform_profile
 */
class m210108_011525_alter_table_scientific_platform_profile extends Migration
{
    public function safeUp()
    {
		$this->addColumn('e_scientific_platform_profile', '_education_year', $this->string(64));

        $this->addForeignKey(
            'fk_e_education_year_e_scientific_platform_profile_fkey',
            'e_scientific_platform_profile',
            '_education_year',
            'e_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
		Yii::$app
            ->db
            ->createCommand("DROP INDEX e_scientific_platform_profile_uniq")
            ->execute();
			
		$this->createIndex('e_scientific_platform_profile_two_uniq',
            'e_scientific_platform_profile',
            ['_employee', '_scientific_platform', '_education_year'],
            true);
    }

    public function safeDown()
    {
        $this->dropColumn('e_scientific_platform_profile', '_education_year');
    }

}
