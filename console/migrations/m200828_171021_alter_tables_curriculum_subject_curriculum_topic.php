<?php

use yii\db\Migration;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectTopic;
/**
 * Class m200828_171021_alter_tables_curriculum_subject_curriculum_topic
 */
class m200828_171021_alter_tables_curriculum_subject_curriculum_topic extends Migration
{
    public function safeUp()
    {
		$this->addColumn(ECurriculumSubject::tableName(), '_department', $this->integer()->null());
		$this->addColumn(ECurriculumSubjectTopic::tableName(), '_department', $this->integer()->null());
		
		$this->addForeignKey(
            'fk_e_department_e_curriculum_subject_fkey',
            'e_curriculum_subject',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		$this->addForeignKey(
            'fk_e_department_e_curriculum_subject_topic_fkey',
            'e_curriculum_subject_topic',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
		
      
    }

    public function safeDown()
    {
        $this->dropColumn(ECurriculumSubject::tableName(), '_department');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), '_department');
    }
}
