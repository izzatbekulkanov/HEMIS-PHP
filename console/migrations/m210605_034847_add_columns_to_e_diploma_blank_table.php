<?php
/*
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_diploma_blank}}`.
 */
class m210605_034847_add_columns_to_e_diploma_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%e_diploma_blank}}', 'status', $this->string(64));
        $this->addColumn('{{%e_diploma_blank}}', 'year', $this->string(32));
        $this->addColumn('{{%e_diploma_blank}}', '_options', 'json');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%e_diploma_blank}}', 'year');
        $this->dropColumn('{{%e_diploma_blank}}', '_options');
    }
}
