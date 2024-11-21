<?php

use yii\db\Migration;
use common\models\performance\EPerformance;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\ExamType;
/**
 * Class m210213_093147_alter_table_apply_default_final_exam_type
 */
class m210213_093147_alter_table_apply_default_final_exam_type extends Migration
{
    
	public function safeUp()
    {
        $this->addColumn('e_subject_task', '_final_exam_type', $this->string(64)->defaultValue('11'));
        $this->addColumn('e_subject_task_student', '_final_exam_type', $this->string(64)->defaultValue('11'));
        $this->addColumn('e_student_task_activity', '_final_exam_type', $this->string(64)->defaultValue('11'));
		
		EPerformance::updateAll([
				'_final_exam_type' => FinalExamType::FINAL_EXAM_TYPE_FIRST,
			],
			[
			'in', '_exam_type', 
				[
					ExamType::EXAM_TYPE_CURRENT, 
					ExamType::EXAM_TYPE_MIDTERM, 
					ExamType::EXAM_TYPE_CURRENT_FIRST, 
					ExamType::EXAM_TYPE_CURRENT_SECOND, 
					ExamType::EXAM_TYPE_MIDTERM_FIRST, 
					ExamType::EXAM_TYPE_MIDTERM_SECOND
				]
			]
		);
		
		ESubjectExamSchedule::updateAll([
				'final_exam_type' => FinalExamType::FINAL_EXAM_TYPE_FIRST,
			],
			[
			'in', '_exam_type', 
				[
					ExamType::EXAM_TYPE_CURRENT, 
					ExamType::EXAM_TYPE_MIDTERM, 
					ExamType::EXAM_TYPE_CURRENT_FIRST, 
					ExamType::EXAM_TYPE_CURRENT_SECOND, 
					ExamType::EXAM_TYPE_MIDTERM_FIRST, 
					ExamType::EXAM_TYPE_MIDTERM_SECOND
				]
			]
		);
	}

    public function safeDown()
    {
        $this->dropColumn('e_subject_task', '_final_exam_type');
        $this->dropColumn('e_subject_task_student', '_final_exam_type');
        $this->dropColumn('e_student_task_activity', '_final_exam_type');
    }
}
