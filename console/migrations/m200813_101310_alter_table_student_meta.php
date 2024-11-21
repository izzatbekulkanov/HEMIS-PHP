<?php

use yii\db\Migration;

/**
 * Class m200813_101310_alter_table_student_meta
 */
class m200813_101310_alter_table_student_meta extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \common\models\student\EStudentMeta::deleteAll([
            'not in',
            '_student',
            \common\models\student\EStudent::find()->select(['id'])->column()
        ]);

        $this->addForeignKey(
            'fk_e_student_meta_student',
            'e_student_meta',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_e_student_meta_student', 'e_student_meta');
    }
}
