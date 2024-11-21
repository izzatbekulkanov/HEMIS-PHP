<?php

use common\models\academic\EDecree;
use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m210303_111518_create_table_decree
 */
class m210303_111518_create_table_decree extends Migration
{
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this);

        $tableOptions = null;
        $description = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $description = 'Buyruqlar jadvali';

        $this->createTable('e_decree', [
            'id' => $this->primaryKey(),
            '_department' => $this->integer()->notNull(),
            '_decree_type' => $this->string(64)->notNull(),
            'number' => $this->string(16)->notNull(),
            'date' => $this->date()->notNull(),
            'name' => $this->string(512)->notNull(),
            'header' => $this->text(),
            'body' => $this->text(),
            'trailer' => $this->text(),
            'file' => 'jsonb',
            'status' => $this->string(16)->defaultValue(EDecree::STATUS_DISABLE),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addCommentOnTable('e_decree', $description);

        $this->addForeignKey(
            'fk_e_decree_decree_type',
            'e_decree',
            '_decree_type',
            'h_decree_type',
            'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_decree_department',
            'e_decree',
            '_department',
            'e_department',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('e_decree');
    }
}
