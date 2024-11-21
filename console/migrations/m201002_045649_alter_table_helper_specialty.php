<?php

use yii\db\Migration;

/**
 * Class m201002_045649_alter_table_helper_specialty
 */
class m201002_045649_alter_table_helper_specialty extends Migration
{
    public function safeUp()
    {
        $tableOptions = "";
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $classifiers = ['h_bachelor_speciality', 'h_master_speciality'];
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

        $migration->addColumn('e_specialty', '_bachelor_specialty', $this->string(36)->null());
        $migration->addColumn('e_specialty', '_master_specialty', $this->string(36)->null());

        $migration->addForeignKey(
            "fk_e_specialty_bachelor_specialty",
            'e_specialty',
            '_bachelor_specialty',
            'h_bachelor_speciality',
            'id',
            'RESTRICT',
            'CASCADE'
        );

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

        $migration->dropColumn('e_specialty', '_bachelor_specialty');
        $migration->dropColumn('e_specialty', '_master_specialty');

        $classifiers = ['h_bachelor_speciality', 'h_master_speciality'];
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
