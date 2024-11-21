<?php

use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m211221_173942_alter_table_e_student
 */
class m211221_173942_alter_table_e_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this, -1);

        $this->addColumn('e_student', 'person_phone', $this->string(20));
        $this->addColumn('e_student', 'parent_phone', $this->string(20));
        $this->addColumn('e_student', 'email', $this->string(64));
        $this->addColumn('e_student', 'geo_location', $this->string(32));
        $this->addColumn('e_student', 'roommate_count', $this->integer(2));
        $this->addColumn('e_student', '_student_living_status', $this->string(64));
        $this->addColumn('e_student', '_student_roommate_type', $this->string(64));

        $this->addForeignKey(
            'fk_e_group_e_student_student_living_status',
            'e_student',
            '_student_living_status',
            'h_student_living_status',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_group_e_student_student_roommate_type',
            'e_student',
            '_student_roommate_type',
            'h_student_roommate_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ([
                     'person_phone',
                     'parent_phone',
                     'email',
                     'geo_location',
                     'roommate_count',
                     '_student_living_status',
                     '_student_roommate_type',
                 ] as $col) {
            $this->dropColumn('e_student', $col);
        }
    }
}
