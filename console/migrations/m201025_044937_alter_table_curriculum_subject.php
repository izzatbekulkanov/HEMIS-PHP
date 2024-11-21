<?php

use yii\db\Migration;

/**
 * Class m201025_044937_alter_table_curriculum_subject
 */
class m201025_044937_alter_table_curriculum_subject extends Migration
{
    public function safeUp()
    {
        //Adds new column
		$this->addColumn(\common\models\curriculum\ECurriculumSubject::tableName(), '_employee', $this->integer()->null());
 
        //Adds foreign keys
		$this->addForeignKey(
            'fk_e_employee_e_curriculum_subject_fkey',
            'e_curriculum_subject',
            '_employee',
            'e_employee',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
    }

    public function safeDown()
    {
        $this->dropColumn(\common\models\curriculum\ECurriculumSubject::tableName(), '_employee');
		$this->dropForeignKey('fk_e_employee_e_curriculum_subject_fkey', 'e_curriculum_subject');
    }
}
