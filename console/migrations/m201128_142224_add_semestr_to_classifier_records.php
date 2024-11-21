<?php
use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m201128_142224_add_semestr_to_classifier_records
 */
class m201128_142224_add_semestr_to_classifier_records extends Migration
{
    public function safeUp()
    {
		SystemClassifier::createClassifiersTables($this);
    }
}
