<?php

use yii\db\Migration;
use common\models\performance\EPerformance;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
/**
 * Class m210221_052514_alter_table_performance_student_passed_setting
 */
class m210221_052514_alter_table_performance_student_passed_setting extends Migration
{
   
	public function safeUp()
    {
        $this->addColumn('e_performance', 'passed_status', $this->integer(3));
		EPerformance::updateAll([
				'passed_status' => 1,
			],
			[
			'in', '_final_exam_type', 
				[
					FinalExamType::FINAL_EXAM_TYPE_FIRST,
				]
			]
		);
		
		EPerformance::updateAll([
				'_final_exam_type' => FinalExamType::FINAL_EXAM_TYPE_FIRST,
			],
			[
				'AND',
				[
					'not in', '_final_exam_type', 
					[
						FinalExamType::FINAL_EXAM_TYPE_SECOND, 
						FinalExamType::FINAL_EXAM_TYPE_THIRD, 
					]
				],
				
				[
					'in', '_exam_type', 
								
					[
						ExamType::EXAM_TYPE_FINAL, 
						ExamType::EXAM_TYPE_OVERALL, 
						ExamType::EXAM_TYPE_LIMIT, 
					]
				]
			]
		);
    }
	
	public function safeDown()
    {
        $this->dropColumn('e_performance', 'passed_status');
    }
	
}
