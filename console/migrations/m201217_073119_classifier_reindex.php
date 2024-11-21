<?php

use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m201217_073119_classifier_reindex
 */
class m201217_073119_classifier_reindex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        SystemClassifier::createClassifiersTables($this, -1, Yii::getAlias('@common/data/classifiers_h_education_year.json'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

}
