<?php

use yii\db\Migration;

/**
 * Class m200721_071504_batch_insert_lesson_pair_table
 */
class m200721_071504_batch_insert_lesson_pair_table extends Migration
{
    public function up()
    {
        $this->batchInsert('h_education_year',
            ['code', 'name', 'current_status', 'updated_at', 'created_at'],
            [
                ['2015', '2015-2016', 0, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['2016', '2016-2017', 0, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['2017', '2017-2018', 0, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['2018', '2018-2019', 0, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['2019', '2019-2020', 0, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['2020', '2020-2021', 1, new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
            ]
        );

        $this->batchInsert('h_lesson_pair',
            ['code', 'name', 'start_time', 'end_time', '_education_year', 'updated_at', 'created_at'],
            [
                ['11', '1', '08:30', '09:50', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['12', '2', '10:00', '11:20', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['13', '3', '11:30', '12:50', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['14', '4', '13:00', '14:20', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['15', '5', '14:30', '15:50', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['16', '6', '16:00', '17:20', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['17', '7', '17:30', '18:50', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
                ['18', '8', '19:00', '20:20', '2019', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')],
            ]
        );
    }

    public function down()
    {

        $this->delete('h_lesson_pair',
            ['in', 'code',
                [
                    ['11'],
                    ['12'],
                    ['13'],
                    ['14'],
                    ['15'],
                    ['16'],
                    ['17'],
                    ['18'],
                ],
            ]
        );

        $this->delete('h_education_year',
            ['in', 'code',
                [
                    ['2019'],
                    ['2020'],
                ],
            ]
        );
    }
}
