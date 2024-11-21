<?php

use yii\db\Migration;

/**
 * Class m200823_165704_alter_table_auditorium
 */
class m200823_165704_alter_table_auditorium extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_h_building_e_auditorium_fkey', 'e_auditorium');
        $this->dropForeignKey('fk_h_auditorium_type_e_auditorium_fkey', 'e_auditorium');
        $this->dropForeignKey('fk_e_auditorium_subject_exam_schedule_fkey', 'e_subject_exam_schedule');
        $this->dropForeignKey('fk_e_auditorium_e_subject_schedule_fkey', 'e_subject_schedule');

        $this->addForeignKey(
            'fk_h_building_e_auditorium_fkey',
            'e_auditorium',
            '_building',
            'h_building',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_h_auditorium_type_e_auditorium_fkey',
            'e_auditorium',
            '_auditorium_type',
            'h_auditorium_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_auditorium_subject_exam_schedule_fkey',
            'e_subject_exam_schedule',
            '_auditorium',
            'e_auditorium',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_auditorium_e_subject_schedule_fkey',
            'e_subject_schedule',
            '_auditorium',
            'e_auditorium',
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
    }

}
