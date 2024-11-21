<?php

use yii\db\Migration;

/**
 * Class m201008_122203_alter_table_science_branch
 */
class m201008_122203_alter_table_science_branch extends Migration
{
    public function safeUp()
    {
        $tableOptions = "";
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $classifiers = ['h_science_branch'];
        $migration = $this;
        foreach ($classifiers as $classifier) {
            $this->dropTable($classifier);
            $migration->createTable($classifier, [
                'id' => $migration->string(36)->notNull()->unique(),
                'code' => $migration->string(64)->notNull(),
                'name' => $migration->string(256)->notNull(),
                'position' => $migration->integer(3)->defaultValue(0),
                'active' => $migration->boolean()->defaultValue(true),
                '_parent' => $migration->string(36)->null(),
                '_translations' => 'jsonb',
                '_options' => 'jsonb',
                'updated_at' => $migration->dateTime()->notNull(),
                'created_at' => $migration->dateTime()->notNull(),
            ], $tableOptions);

            $migration->addPrimaryKey("pk_$classifier", $classifier, 'id');
            $migration->createIndex("idx_{$classifier}_position", $classifier, 'position');
            $migration->createIndex("idx_{$classifier}_active", $classifier, 'active');

            $migration->addForeignKey(
                "fk_{$classifier}_parent_id",
                $classifier,
                '_parent',
                $classifier,
                'id',
                'RESTRICT',
                'CASCADE'
            );
        }

        $migration->dropForeignKey('fk_e_specialty_master_specialty', 'e_specialty');

        \common\models\student\ESpecialty::updateAll(['_master_specialty' => null], []);

        $this->truncateTable('h_master_speciality');

        $migration->addForeignKey(
            "fk_e_specialty_master_specialty",
            'e_specialty',
            '_master_specialty',
            'h_master_speciality',
            'id',
            'RESTRICT',
            'CASCADE'
        );

    }

    public function safeDown()
    {
        $tableOptions = "";
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $migration = $this;

        $classifiers = ['h_science_branch'];
        foreach ($classifiers as $classifier) {
            $this->dropTable($classifier);

            $migration->createTable($classifier, [
                'code' => $migration->string(64)->notNull()->unique(),
                'name' => $migration->string(256)->notNull(),
                'position' => $migration->integer(3)->defaultValue(0),
                'active' => $migration->boolean()->defaultValue(true),
                '_parent' => $migration->string(64)->null(),
                '_translations' => 'jsonb',
                '_options' => 'jsonb',
                'updated_at' => $migration->dateTime()->notNull(),
                'created_at' => $migration->dateTime()->notNull(),
            ], $tableOptions);

            $migration->addPrimaryKey("pk_$classifier", $classifier, 'code');
            $migration->createIndex("idx_{$classifier}_position", $classifier, 'position');
            $migration->createIndex("idx_{$classifier}_active", $classifier, 'active');

            $migration->addForeignKey(
                "fk_{$classifier}_parent_code",
                $classifier,
                '_parent',
                $classifier,
                'code',
                'RESTRICT',
                'CASCADE'
            );
        }
    }
}
