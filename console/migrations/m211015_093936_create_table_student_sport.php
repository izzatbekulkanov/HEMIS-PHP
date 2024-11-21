<?php

use common\components\hemis\HemisApiSyncModel;
use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m211015_093936_create_table_student_sport
 */
class m211015_093936_create_table_student_sport extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);

        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = "Talabalarning sport seksiyalari va razryadlari";

        $this->createTable('e_student_sport', [
            'id' => $this->primaryKey(),
            '_student' => $this->integer()->notNull(),
            '_education_year' => $this->string(64)->notNull(),
            '_sport_type' => $this->string(64)->notNull(),
            'record_type' => $this->string(64)->null(),
            'sport_date' => $this->date()->null(),
            'sport_rank' => $this->string(32),
            'sport_rank_document' => $this->string(32),

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
            'fk_e_student_sport_education_year',
            'e_student_sport',
            '_education_year',
            'h_education_year',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_sport_student',
            'e_student_sport',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_sport_sport',
            'e_student_sport',
            '_sport_type',
            'h_sport_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addCommentOnTable('e_student_sport', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_student_sport');
        $this->dropTable('h_sport_type');
    }
}
