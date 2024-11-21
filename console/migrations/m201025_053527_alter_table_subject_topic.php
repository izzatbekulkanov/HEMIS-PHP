<?php

use common\models\curriculum\ECurriculumSubjectTopic;
use yii\db\Migration;

/**
 * Class m201025_053527_alter_table_subject_topic
 */
class m201025_053527_alter_table_subject_topic extends Migration
{
    public function safeUp()
    {
        $this->addColumn(ECurriculumSubjectTopic::tableName(), 'random', $this->boolean()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn(ECurriculumSubjectTopic::tableName(), 'random');
    }
}
