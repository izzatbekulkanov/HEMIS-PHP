<?php

use common\models\curriculum\Semester;
use yii\db\Migration;

/**
 * Class m200702_132912_alter_table_semesters
 */
class m200702_132912_alter_table_semesters extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('h_semestr', 'last', $this->boolean()->defaultValue(false));

        \console\controllers\IndexerController::indexSemesters();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('h_semestr', 'last');
    }
}
