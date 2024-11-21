<?php

use common\models\student\EStudent;
use yii\db\Migration;

/**
 * Class m211229_070439_fix_student_phone
 */
class m211229_070439_fix_student_phone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $phones = EStudent::find()
            ->select(['id', 'phone'])
            ->where(['not in', 'phone', ["", null]])
            ->asArray()
            ->all();
        $phones = \yii\helpers\ArrayHelper::map($phones, 'id', 'phone');

        $updated = 0;
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($phones as $id => $phone) {
            $phone = EStudent::normalizeMobile($phone);
            if ($this->db
                ->createCommand("UPDATE e_student SET phone=:phone WHERE id=:id", [
                    'phone' => $phone,
                    'id' => $id
                ])
                ->execute()) {
                $updated++;
            };
        }
        echo "$updated phone formatted successfully\n";
        $transaction->commit();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}
