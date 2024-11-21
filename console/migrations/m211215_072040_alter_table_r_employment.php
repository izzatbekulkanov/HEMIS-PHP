<?php

use yii\db\Migration;

/**
 * Class m211215_072040_alter_table_r_employment
 */
class m211215_072040_alter_table_r_employment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \common\models\report\ReportEmployment::updateAll(['_uid' => null]);
        $this->addColumn('r_employment', 'last', $this->boolean()->defaultValue(true));
        $this->addColumn('r_contract', 'last', $this->boolean()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('r_employment', 'last');
        $this->dropColumn('r_contract', 'last');
    }
}
