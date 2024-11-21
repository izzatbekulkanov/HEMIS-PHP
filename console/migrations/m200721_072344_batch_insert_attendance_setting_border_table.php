<?php

use yii\db\Migration;

/**
 * Class m200721_072344_batch_insert_attendance_setting_border_table
 */
class m200721_072344_batch_insert_attendance_setting_border_table extends Migration
{
    public function up()
    {
        $this->batchInsert('e_attendance_setting_border',
            ['_attendance_setting', '_marking_system', 'min_border', 'updated_at', 'created_at'],
            [
                ['11', '11', 12, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '11', 36, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '11', 74, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['11', '12', 12, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '12', 36, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '12', 74, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				['11', '13', 12, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '13', 36, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '13', 74, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
				
            ] 
        ); 
    }

    public function down()
    {
        $this->delete('e_attendance_setting_border', 
            ['in', 'code', 
                [
                    ['11'],
                    ['12'],
                    ['13'],
                ],
            ]
        ); 
    }
}
