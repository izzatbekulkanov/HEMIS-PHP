<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m211011_034840_create_table_e_employee_foreign
 */
class m211011_034840_create_table_e_employee_foreign extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = "Xorijiy o'qituvchilar haqida malumot";

        $this->createTable('e_employee_foreign', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(512)->notNull(),
            '_education_year' => $this->string(64)->notNull(),
            '_country' => $this->string(64)->notNull(),
            'work_place' => $this->string(512)->notNull(),
            'specialty_name' => $this->string(512)->notNull(),
            'subject' => $this->string(512)->notNull(),
            'contract_data' => $this->string(512)->notNull(),

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
            'fk_e_employee_foreign_country',
            'e_employee_foreign',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_foreign_education_year',
            'e_employee_foreign',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_employee_foreign', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_employee_foreign');
    }
}
