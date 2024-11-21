<?php

use yii\db\Migration;

/**
 * Class m210119_025651_alter_table_grade_type
 */
class m210119_025651_alter_table_grade_type extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('h_grade_type', 'min_border', $this->decimal(10, 1));
        $this->alterColumn('h_grade_type', 'max_border', $this->decimal(10, 1));
		
		$this->batchInsert('h_grade_type',
            ['code', 'name', '_marking_system', 'min_border', 'max_border', 'updated_at', 'created_at'],
            [
                ['11', '5', '12', 4.5, 5.0, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '4', '12', 3.5, 4.4, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '3', '12', 2.5, 3.4, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['14', '2', '12', 0.0, 2.4, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
            ] 
        ); 
		
    }

    public function safeDown()
    {
		$this->alterColumn('h_grade_type', 'min_border', $this->integer(3));
        $this->alterColumn('h_grade_type', 'max_border', $this->integer(3));
		$this->delete('h_grade_type', 
            ['in', '_marking_system', 
                [
                    ['12'],
                ],
            ]
        ); 
    }
	
}
