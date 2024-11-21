<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m211015_070815_create_table_e_student_olympiad
 */
class m211015_070815_create_table_e_student_olympiad extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = "Talabalar qatnashgan musobaqalar";

        $this->createTable('e_student_olympiad', [
            'id' => $this->primaryKey(),
            '_student' => $this->integer()->notNull(),
            '_education_year' => $this->string(64)->notNull(),
            '_country' => $this->string(64)->notNull(),
            'olympiad_type' => $this->string(64)->notNull(),
            'olympiad_place' => $this->string(512)->notNull(),
            'olympiad_name' => $this->string(512)->notNull(),
            'olympiad_section_name' => $this->string(512)->null(),
            'olympiad_date' => $this->date()->notNull(),
            'student_place' => $this->integer(4)->defaultValue(1),
            'diploma_serial' => $this->string(32),
            'diploma_number' => $this->integer(),

            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),

            '_qid' => $this->bigInteger(),
            '_uid' => $this->string()->unique(),
            '_sync' => $this->boolean()->defaultValue(false),
            '_sync_diff' => 'json',
            '_sync_date' => $this->dateTime()->null(),
            '_sync_status' => $this->string(16)->defaultValue(HemisApiSyncModel::STATUS_NOT_CHECKED)
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_student_olympiad_country',
            'e_student_olympiad',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_olympiad_education_year',
            'e_student_olympiad',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_olympiad_student',
            'e_student_olympiad',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addCommentOnTable('e_student_olympiad', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student_olympiad');
    }
}
