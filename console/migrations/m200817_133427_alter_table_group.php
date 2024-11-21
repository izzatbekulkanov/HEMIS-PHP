<?php

use common\models\student\EGroup;
use yii\db\Migration;

/**
 * Class m200817_133427_alter_table_group
 */
class m200817_133427_alter_table_group extends Migration
{
    public function safeUp()
    {
		$this->addColumn(EGroup::tableName(), '_education_lang', $this->string(64)->null());

        $this->addForeignKey(
            'fk_e_group_education_lang',
            'e_group',
            '_education_lang',
            'h_language',
            'code',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn(EGroup::tableName(), '_education_lang');
    }
}
