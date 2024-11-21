<?php

use yii\db\Migration;

/**
 * Class m200721_070055_batch_insert_rating_grade_table
 */
class m200721_070055_batch_insert_rating_grade_table extends Migration
{
    public function up()
    {
        $this->batchInsert('h_rating_grade',
            ['code', 'name', 'template', 'updated_at', 'created_at'],
            [
                ['11', 'Fandan o`zlashtirish qaydnomasi', 'subject', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', 'Kurs ishi qaydnomasi', 'course', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', 'Davlat imtihoni qaydnomasi', 'state', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['14', 'Malakaviy amaliyot qaydnomasi', 'practicum', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['15', 'Bitiruv ishi qaydnomasi', 'graduate', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
            ]
        ); 
    }

    public function down()
    {
        $this->delete('h_rating_grade', 
            ['in', 'code', 
                [
                    ['11'],
                    ['12'],
                    ['13'],
                    ['14'],
                    ['15'],
                ],
            ]
        ); 
    }
}
