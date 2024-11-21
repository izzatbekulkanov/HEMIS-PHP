<?php

use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectResourceQuestion;
use yii\db\Migration;

/**
 * Class m201103_123857_alter_table_topic_resources_and_test
 */
class m201103_123857_alter_table_topic_resources_and_test extends Migration
{
    public function safeUp()
    {
        $this->addColumn(ESubjectResource::tableName(), 'resource_type', $this->integer(2)->defaultValue(ESubjectResource::RESOURCE_TYPE_RESOURCE));
        $this->addColumn(ESubjectResource::tableName(), 'test_duration', $this->integer()->defaultValue(20));
        $this->addColumn(ESubjectResource::tableName(), 'test_questions', $this->integer()->defaultValue(10));
        $this->addColumn(ESubjectResource::tableName(), 'test_attempt_count', $this->integer(3)->defaultValue(20));
        $this->addColumn(ESubjectResource::tableName(), 'test_question_count', $this->integer());
        $this->addColumn(ESubjectResource::tableName(), 'test_random', $this->boolean());
        $this->addColumn('e_subject_topic_question', '_subject_resource', $this->integer()->null());

        $this->addForeignKey(
            'fk_e_subject_topic_question_resource',
            'e_subject_topic_question',
            '_subject_resource',
            ESubjectResource::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );


        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'test_duration');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'test_questions');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'attempt_count');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'question_count');
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'random');
    }

    public function safeDown()
    {

        $this->addColumn(ECurriculumSubjectTopic::tableName(), 'test_duration', $this->integer()->defaultValue(20));
        $this->addColumn(ECurriculumSubjectTopic::tableName(), 'test_questions', $this->integer()->defaultValue(10));
        $this->addColumn(ECurriculumSubjectTopic::tableName(), 'attempt_count', $this->integer(3)->defaultValue(20));
        $this->addColumn(ECurriculumSubjectTopic::tableName(), 'question_count', $this->integer());
        $this->addColumn(ECurriculumSubjectTopic::tableName(), 'random', $this->boolean());


        $this->dropColumn(ESubjectResource::tableName(), 'resource_type');
        $this->dropColumn(ESubjectResource::tableName(), 'test_duration');
        $this->dropColumn(ESubjectResource::tableName(), 'test_questions');
        $this->dropColumn(ESubjectResource::tableName(), 'test_attempt_count');
        $this->dropColumn(ESubjectResource::tableName(), 'test_question_count');
        $this->dropColumn(ESubjectResource::tableName(), 'test_random');
        $this->dropColumn('e_subject_topic_question', '_subject_resource');
    }
}
