<?php

use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m200817_162043_remove_moved_classifier_records
 */
class m200817_162043_remove_moved_classifier_records extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $codes = [
            'h_marking_system',
            'h_semestr',
            'h_grade_type',
            'h_rating_grade',
        ];

        foreach ($codes as $code) {
            if ($classifier = SystemClassifier::findOne(['classifier' => $code])) {
                $classifier->delete();
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        SystemClassifier::createClassifiersTables($this);
    }

}
