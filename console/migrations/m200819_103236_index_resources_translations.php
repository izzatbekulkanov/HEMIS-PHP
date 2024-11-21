<?php

use backend\models\FormUploadTrans;
use common\components\AccessResources;
use yii\db\Migration;
use yii\helpers\Console;

/**
 * Class m200819_103236_index_resources_translations
 */
class m200819_103236_index_resources_translations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $updated = AccessResources::parsePermissions(true);

        echo sprintf("Updated %d permissions\n", $updated);

        $updated = FormUploadTrans::parseTranslations();

        echo sprintf("Updated %d translations\n", $updated);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }


}
