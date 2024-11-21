<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m211009_130431_create_table_e_employee_training
 */
class m211009_130431_create_table_e_employee_training extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = "O'qituvchilarning xorijning top 1000 oliygohlarida o'tkizgan o'quv mashg'ulotlari";

        $this->createTable('e_employee_training', [
            'id' => $this->primaryKey(),
            '_employee' => $this->integer()->notNull(),
            '_country' => $this->string(64)->notNull(),
            '_training_type' => $this->string(64)->notNull(),
            '_education_year' => $this->string(64)->notNull(),

            'university' => $this->string(512)->notNull(),
            'specialty_name' => $this->string(512)->notNull(),
            'training_contract' => $this->string(512)->notNull(),
            'training_date_start' => $this->date()->notNull(),
            'training_date_end' => $this->date()->notNull(),

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
            'fk_e_employee_training_employee',
            'e_employee_training',
            '_employee',
            'e_employee',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_training_country',
            'e_employee_training',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_training_type',
            'e_employee_training',
            '_training_type',
            'h_training_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_training_education_year',
            'e_employee_training',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_employee_training', $description);

        $this->addColumn('e_employee_academic_degree', '_sync_diff', 'json');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_employee_training');
        $this->dropColumn('e_employee_academic_degree', '_sync_diff');
    }
}
