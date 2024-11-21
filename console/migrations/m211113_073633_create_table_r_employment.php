<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m211113_073633_create_table_r_employment
 */
class m211113_073633_create_table_r_employment extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Bandlik hisoboti';

        $this->createTable('r_employment', [
            'id' => $this->primaryKey(),
            'qty' => $this->integer(12),

            '_department' => $this->integer()->notNull(),
            '_education_year' => $this->string(6)->notNull(),
            '_education_type' => $this->string(6)->notNull(),
            '_education_form' => $this->string(6)->notNull(),
            '_payment_form' => $this->string(6)->notNull(),
            '_gender' => $this->string(6)->notNull(),
            'workplace_compatibility' => $this->string(6)->notNull(),
            '_graduate_fields_type' => $this->string(6),
            '_graduate_inactive_type' => $this->string(6),

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

        $this->createIndex('idx_r_employment_unique_rows', 'r_employment', [
            '_department',
            '_education_year',
            '_education_type',
            '_education_form',
            '_payment_form',
            '_gender',
            '_graduate_fields_type',
            '_graduate_inactive_type',
            'workplace_compatibility',
        ], true);

        $this->addCommentOnTable('r_employment', $description);
    }

    public function safeDown()
    {
        $this->dropTable('r_employment');
    }
}
