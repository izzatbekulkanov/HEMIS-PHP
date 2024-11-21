<?php

use common\components\hemis\HemisApiSyncModel;
use yii\db\Migration;

/**
 * Class m211006_003227_create_table_employee_academic_degree
 */
class m211006_003227_create_table_employee_academic_degree extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = "O'qituvchilar ilmiy darajalari jadvali";

        $this->createTable('e_employee_academic_degree', [
            'id' => $this->primaryKey(),
            '_employee' => $this->integer()->notNull(),
            '_academic_degree' => $this->string(64)->null(),
            '_academic_rank' => $this->string(64)->null(),
            '_education_year' => $this->string(64)->notNull(),
            'diploma_type' => $this->string(64)->notNull(),

            'diploma_number' => $this->string(20)->notNull(),
            'diploma_date' => $this->date()->notNull(),

            'specialty_code' => $this->string(20)->notNull(),
            'specialty_name' => $this->string(512)->notNull(),

            'council_date' => $this->date()->notNull(),
            'council_number' => $this->string(20)->notNull(),

            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),

            '_qid' => $this->bigInteger(),
            '_uid' => $this->string()->unique(),
            '_sync' => $this->boolean()->defaultValue(false),
            '_sync_date' => $this->dateTime()->null(),
            '_sync_status' => $this->string(16)->defaultValue(HemisApiSyncModel::STATUS_NOT_CHECKED)
        ], $tableOptions);

        $this->addForeignKey(
            'fk_e_employee_academic_degree_employee',
            'e_employee_academic_degree',
            '_employee',
            'e_employee',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_academic_degree_academic_degree',
            'e_employee_academic_degree',
            '_academic_degree',
            'h_academic_degree',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_academic_degree_academic_rank',
            'e_employee_academic_degree',
            '_academic_rank',
            'h_academic_rank',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_employee_academic_degree_education_year',
            'e_employee_academic_degree',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );
        $this->addCommentOnTable('e_employee_academic_degree', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_employee_academic_degree');
    }
}
