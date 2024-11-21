<?php

use common\models\student\ESpecialty;
use common\models\system\classifier\BachelorSpeciality;
use yii\db\Migration;

/**
 * Class m210602_151440_remove_bachelor_specialty_duplicates
 */
class m210602_151440_remove_bachelor_specialty_duplicates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * @var $item ESpecialty
         */
        $items = ESpecialty::find()->where(
            [
                '_bachelor_specialty' => BachelorSpeciality::find()
                    ->select([
                        'id'
                    ])
                    ->where([
                        'active' => false
                    ])
                    ->column(),
            ])
            ->all();

        foreach ($items as $item) {
            if ($activeSp = BachelorSpeciality::findOne(['active' => true, 'code' => $item->bachelorSpecialty->code])) {
                if ($item->updateAttributes(['_bachelor_specialty' => $activeSp->id])) {

                }
            }
        }
        /**
         * @var $item BachelorSpeciality
         */
        $items = BachelorSpeciality::find()->where(
            [
                '_parent' => BachelorSpeciality::find()
                    ->select([
                        'id'
                    ])
                    ->where([
                        'active' => false
                    ])
                    ->column(),
            ])
            ->all();

        foreach ($items as $item) {
            if ($activeSp = BachelorSpeciality::findOne(['active' => true, 'code' => $item->code])) {
                if ($item->updateAttributes(['_parent' => $activeSp->id])) {

                }
            }
        }

        echo BachelorSpeciality::deleteAll(['active' => false]) . " non active bachelor specialities deleted\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
