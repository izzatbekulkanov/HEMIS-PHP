<?php

use common\models\system\classifier\ScienceBranch;
use yii\db\Migration;

/**
 * Class m210130_125504_clean_h_science_branch_classifier
 */
class m210130_125504_clean_h_science_branch_classifier extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('e_dissertation_defense', '_science_branch_id', $this->string(36)->null());
        $this->alterColumn('e_doctorate_student', '_science_branch_id', $this->string(36)->null());

        $ids = ScienceBranch::find()
            ->select(['id'])
            ->where(new \yii\db\Expression("(_options->>'version')::integer=-1"))
            ->column();

        \common\models\student\ESpecialty::updateAll(['_doctorate_specialty' => null], ['_doctorate_specialty' => $ids]);
        \common\models\science\EDissertationDefense::updateAll(['_science_branch_id' => null], ['_science_branch_id' => $ids]);
        \common\models\science\EDoctorateStudent::updateAll(['_science_branch_id' => null], ['_science_branch_id' => $ids]);
        ScienceBranch::updateAll(['_parent' => null], ['_parent' => $ids]);
        $deleted = ScienceBranch::deleteAll(['id' => $ids]);

        echo "$deleted items deleted from h_science_branch\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
