<?php
use common\models\curriculum\ESubjectSchedule;
use yii\db\Migration;

/**
 * Class m201001_151315_alter_table_subject_schedule
 */
class m201001_151315_alter_table_subject_schedule extends Migration
{
   public function safeUp()
    {
		$this->addColumn(ESubjectSchedule::tableName(), 'additional', $this->string(512)->null());
    }

    public function safeDown()
    {
        $this->dropColumn(ESubjectSchedule::tableName(), 'additional');
    }
}
