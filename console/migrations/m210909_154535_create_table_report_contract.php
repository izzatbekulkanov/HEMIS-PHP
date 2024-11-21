<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m210909_154535_create_table_report_contract
 */
class m210909_154535_create_table_report_contract extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Shartnomalar hisoboti';

        $this->createTable('r_contract', [
            'id' => $this->primaryKey(),
            'total' => $this->integer(9),
            'daily' => $this->integer(9),
            '_department' => $this->integer()->notNull(),
            '_education_year' => $this->string(64)->notNull(),
            '_education_type' => $this->string(64)->notNull(),
            '_education_form' => $this->string(64)->notNull(),
            '_course' => $this->string(64)->notNull(),
            'date' => $this->date()->notNull(),

            '_qid' => $this->bigInteger(),
            '_uid' => $this->string()->unique(),
            '_sync' => $this->boolean()->defaultValue(false),
            'updated_at' => $this->dateTime()->defaultExpression('NOW()'),
            '_sync_date' => $this->dateTime()->null(),
            '_sync_status' => $this
                ->string(16)
                ->null()
                ->defaultValue(HemisApiSyncModel::STATUS_NOT_CHECKED)
        ], $tableOptions);

        $this->addForeignKey(
            'fk_r_contract_department',
            'r_contract',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_r_contract_education_year',
            'r_contract',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_r_contract_education_form',
            'r_contract',
            '_education_form',
            'h_education_form',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_r_contract_education_type',
            'r_contract',
            '_education_type',
            'h_education_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_r_contract_course',
            'r_contract',
            '_course',
            'h_course',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->createIndex('idx_r_contract_unique_rows', 'r_contract', [
            '_department',
            '_education_year',
            '_education_type',
            '_education_form',
            '_course',
            'date',
        ], true);

        $this->addCommentOnTable('r_contract', $description);
    }

    public function safeDown()
    {
        $this->dropTable('r_contract');
    }
}
