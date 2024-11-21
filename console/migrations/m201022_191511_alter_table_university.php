<?php

use common\models\structure\EUniversity;
use yii\db\Migration;

/**
 * Class m201022_191511_alter_table_university
 */
class m201022_191511_alter_table_university extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!(new EUniversity())->hasAttribute('_qid'))
            $this->addColumn(\common\models\structure\EUniversity::tableName(), '_qid', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ((new EUniversity())->hasAttribute('_qid'))
            $this->dropColumn(\common\models\structure\EUniversity::tableName(), '_qid');
    }

}
