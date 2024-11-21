<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%e_certificate_committee_member}}`.
 */
class m210405_191734_create_e_certificate_committee_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Davlat attestatsiya komissiyasi a\'zolari';

        $this->createTable('{{%e_certificate_committee_member}}', [
            'id' => $this->primaryKey(),
            '_certificate_committee' => $this->integer()->notNull(),
            'name' => $this->string(128)->notNull(),
            'work_place' => $this->string(256)->notNull(),
            'position' => $this->string(128)->notNull(),
            'role' => $this->string(64)->notNull(),
            '_translations' => 'jsonb',
            'active' => $this->boolean()->defaultValue(true),
            'updated_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('e_certificate_committee_member', $description);
        $this->addForeignKey(
            'fk_e_certificate_committee_e_certificate_committee_member_fkey',
            'e_certificate_committee_member',
            '_certificate_committee',
            'e_certificate_committee',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%e_certificate_committee_member}}');
    }
}
