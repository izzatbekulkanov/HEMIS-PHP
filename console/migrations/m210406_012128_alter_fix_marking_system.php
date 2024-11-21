<?php
use common\models\curriculum\MarkingSystem;
use yii\db\Migration;

/**
 * Class m210406_012128_alter_fix_marking_system
 */
class m210406_012128_alter_fix_marking_system extends Migration
{
    public function safeUp()
    {
		if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_CREDIT)) {
			$type->updateAttributes(['count_final_exams' => 3]);
		}
	}

    public function safeDown()
    {
        if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_CREDIT)) {
			$type->updateAttributes(['count_final_exams' => 2]);
		}
    }
}
