<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%h_marking_system}}`.
 */
class m200605_233646_create_h_marking_system_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        if ($this->db->schema->getTableSchema('h_marking_system', true) !== null) {
            $this->dropForeignKey(
                'fk_c_marking_system_fkey',
                'e_curriculum'
            );

            $this->dropTable('h_marking_system');
            \common\models\system\SystemClassifier::deleteAll(['classifier' => 'h_marking_system']);
        }


        $tableOptions = null;
        $description = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $description = 'Baholash tizimlari';
        $this->createTable('h_marking_system', [
            'code' => $this->string(64)->notNull()->unique(),
            'name' => $this->string(256)->notNull(),
            'minimum_limit' => $this->integer(3),
            'position' => $this->integer(3)->defaultValue(0),
            'active' => $this->boolean()->defaultValue(true),
            '_translations' => 'jsonb',
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_h_marking_system_code', 'h_marking_system', ['code']);

        $this->alterColumn('e_curriculum', '_marking_system', $this->string(64)->null());

        \common\models\curriculum\ECurriculum::updateAll(['_marking_system' => null]);

        $this->addForeignKey(
            'fk_c_marking_system_fkey',
            'e_curriculum',
            '_marking_system',
            'h_marking_system',
            'code',
            'SET NULL',
            'CASCADE'
        );

        $this->addCommentOnTable('h_marking_system', $description);
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_c_marking_system_fkey',
            'e_curriculum'
        );
        $this->dropTable('h_marking_system');

        \common\models\system\SystemClassifier::createClassifiersTables($this);

        $this->addForeignKey(
            'fk_c_marking_system_fkey',
            'e_curriculum',
            '_marking_system',
            'h_marking_system',
            'code',
            'SET NULL',
            'CASCADE'
        );

    }
}
