<?php

use yii\db\Migration;

/**
 * Class m201013_171617_alter_table_university
 */
class m201013_171617_alter_table_university extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\structure\EUniversity::tableName(), '_soato', $this->string(64)->null());
        $this->addColumn(\common\models\structure\EUniversity::tableName(), '_sync', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\structure\EUniversity::tableName(), '_soato');
        $this->dropColumn(\common\models\structure\EUniversity::tableName(), '_sync');
    }

}
