<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m211013_094059_create_table_e_student_exchange
 */
class m211013_094059_create_table_e_student_exchange extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = "Talabalarning xorijiy almashinuv dasturlari";

        $this->createTable('e_student_exchange', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(512)->notNull(),
            '_education_year' => $this->string(64)->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            '_country' => $this->string(64)->notNull(),
            'university' => $this->string(512)->notNull(),
            'specialty_name' => $this->string(512)->notNull(),
            'exchange_document' => $this->string(512)->notNull(),
            'exchange_type' => $this->string(64)->notNull()->defaultValue(\common\models\student\EStudentExchange::TYPE_INCOME),

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
            'fk_e_student_exchange_country',
            'e_student_exchange',
            '_country',
            'h_country',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_exchange_education_year',
            'e_student_exchange',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_student_exchange_education_type',
            'e_student_exchange',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_student_exchange', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student_exchange');
    }
}
