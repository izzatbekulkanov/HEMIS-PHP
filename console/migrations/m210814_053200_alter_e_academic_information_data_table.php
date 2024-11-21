<?php

use yii\db\Migration;

/**
 * Class m210814_053200_alter_e_academic_information_data_table
 */
class m210814_053200_alter_e_academic_information_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_academic_information_data', 'education_form_name_moved', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_academic_information_data', 'education_form_name_moved');
    }
}
