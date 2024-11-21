<?php

use yii\db\Migration;

/**
 * Class m200720_152909_batch_insert_marking_system_table
 */
class m200720_152909_batch_insert_marking_system_table extends Migration
{
    
	public function up()
    {
        $this->batchInsert('h_marking_system',
            ['code', 'name', 'minimum_limit', 'count_final_exams', 'updated_at', 'created_at'],
            [
                ['11', '100 ballik baholash tizimi', 55, 3, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '5 ballik baholash tizimi', null, 3, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', 'Kredit baholash tizimi', 60, 2, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
              
            ] 
        ); 
    }

    public function down()
    {
        $this->delete('h_marking_system', 
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
