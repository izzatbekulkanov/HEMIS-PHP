<?php

use yii\db\Migration;
use common\models\curriculum\ECurriculumSubjectTopic;

/**
 * Class m201011_034849_alter_table_e_curriculum_subject_topic
 */
class m201011_034849_alter_table_e_curriculum_subject_topic extends Migration
{
    public function safeUp()
    {
		$this->addColumn(ECurriculumSubjectTopic::tableName(), 'test_duration', $this->integer()->defaultValue(20));
		$this->addColumn(ECurriculumSubjectTopic::tableName(), 'test_questions', $this->integer()->defaultValue(10));
		$this->addColumn(ECurriculumSubjectTopic::tableName(), 'attempt_count', $this->integer(3)->defaultValue(20));
		$this->addColumn(ECurriculumSubjectTopic::tableName(), 'question_count', $this->integer());
	}

    public function safeDown()
    {
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'test_duration');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'test_questions');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'attempt_count');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'question_count');
    }
	
}
