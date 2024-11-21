<?php

use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m200817_171859_alter_table_classifiers
 */
class m200817_171859_alter_table_classifiers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(SystemClassifier::tableName(), '_uid', $this->string()->unique());
        $this->addColumn(SystemClassifier::tableName(), '_sync', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(SystemClassifier::tableName(), '_uid');
        $this->dropColumn(SystemClassifier::tableName(), '_sync');

    }
}
