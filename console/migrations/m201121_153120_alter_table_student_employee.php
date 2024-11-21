<?php

use common\models\employee\EEmployee;
use common\models\student\EStudent;
use yii\db\Migration;

/**
 * Class m201121_153120_alter_table_student_employee
 */
class m201121_153120_alter_table_student_employee extends Migration
{
    public function safeUp()
    {
        //add unique index to passport fields

        $this->alterColumn('e_system_log', 'message', $this->string(16000)->null());
        $this->alterColumn('e_student', 'passport_number', $this->string(14));
        $this->alterColumn('e_student', 'passport_pin', $this->string(20));

        $this->alterColumn('e_employee', 'passport_number', $this->string(14));
        $this->alterColumn('e_employee', 'passport_pin', $this->string(20));

        foreach ([EStudent::className(), EEmployee::className()] as $class) {
            foreach (['passport_number', 'passport_pin'] as $attribute) {
                $result = $class::find()
                    ->select([$attribute])
                    ->groupBy($attribute)
                    ->having('count(1) > 1')
                    ->all();

                foreach ($result as $item) {
                    $items = $class::find()
                        ->orderBy(['created_at' => SORT_ASC])
                        ->where([$attribute => $item->$attribute])
                        ->all();
                    foreach ($items as $i => $student) {
                        if ($i == 0) continue;
                        $student->updateAttributes([$attribute => $student->$attribute . '-D' . $i]);
                    }
                }
            }
        }

        $this->alterColumn('e_student', 'passport_number', $this->string(14)->unique());
        $this->alterColumn('e_student', 'passport_pin', $this->string(20)->unique());

        $this->alterColumn('e_employee', 'passport_number', $this->string(14)->unique());
        $this->alterColumn('e_employee', 'passport_pin', $this->string(20)->unique());
    }

    public function safeDown()
    {
    }
}
