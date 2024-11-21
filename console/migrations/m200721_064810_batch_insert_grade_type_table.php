<?php

use yii\db\Migration;

/**
 * Class m200721_064810_batch_insert_grade_type_table
 */
class m200721_064810_batch_insert_grade_type_table extends Migration
{
    public function up()
    {
        $this->batchInsert('h_grade_type',
            ['code', 'name', '_marking_system', 'min_border', 'max_border', 'updated_at', 'created_at'],
            [
                ['11', '5', '11', 86, 100, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '4', '11', 71, 85, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '3', '11', 55, 70, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['14', '2', '11', 0, 54, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                
				['11', '5', '13', 90, 100, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '4', '13', 70, 89, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '3', '13', 60, 69, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['14', '2', '13', 0, 59, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
            ] 
        ); 
    }

    public function down()
    {
        $this->delete('h_grade_type', 
            ['in', 'code', 
                [
                    ['11'],
                    ['12'],
                    ['13'],
                    ['14'],
                ],
            ]
        ); 
    }
}
