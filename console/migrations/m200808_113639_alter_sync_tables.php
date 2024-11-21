<?php

use yii\db\Migration;

/**
 * Class m200808_113639_alter_sync_tables
 */
class m200808_113639_alter_sync_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\structure\EDepartment::tableName(), '_sync', $this->boolean()->defaultValue(false));
        $this->addColumn(\common\models\student\EStudent::tableName(), '_uid', $this->string()->unique());
        $this->addColumn(\common\models\student\EStudent::tableName(), '_sync', $this->boolean()->defaultValue(false));
        $this->addColumn(\common\models\employee\EEmployee::tableName(), '_uid', $this->string()->unique());
        $this->addColumn(\common\models\employee\EEmployee::tableName(), '_sync', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\structure\EDepartment::tableName(), '_sync');
        $this->dropColumn(\common\models\student\EStudent::tableName(), '_sync');
        $this->dropColumn(\common\models\employee\EEmployee::tableName(), '_sync');
        $this->dropColumn(\common\models\student\EStudent::tableName(), '_uid');
        $this->dropColumn(\common\models\employee\EEmployee::tableName(), '_uid');
    }
}
