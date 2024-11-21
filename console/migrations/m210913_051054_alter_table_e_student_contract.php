<?php

use yii\db\Migration;

/**
 * Class m210913_051054_alter_table_e_student_contract
 */
class m210913_051054_alter_table_e_student_contract extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('e_student_contract', 'hash', $this->string(36)->unique());
        $this->createIndex('idx_student_contract_hash', 'e_student_contract', ['hash'], true);
        foreach (\common\models\finance\EStudentContract::find()->all() as $item) {
            $item->updateAttributes(['hash' => gen_uuid()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_student_contract', 'hash');

    }
}
