<?php

use common\models\curriculum\MarkingSystem;
use yii\db\Migration;

/**
 * Class m210125_025720_alter_table_marking_system_batch_update
 */
class m210125_025720_alter_table_marking_system_batch_update extends Migration
{
    public function safeUp()
    {
		if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_RATING)) {
			$type->updateAttributes(['name' => 'Reyting baholash tizimi']);
		}
		if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_FIVE)) {
			$type->updateAttributes(['name' => '5 baholik baholash tizimi']);
		}
	}
	public function safeDown()
    {
		
    }
}
