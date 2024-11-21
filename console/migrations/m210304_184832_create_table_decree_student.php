<?php

use yii\db\Migration;

/**
 * Class m210304_184832_create_table_decree_student
 */
class m210304_184832_create_table_decree_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('e_decree_student', [
            'id' => $this->primaryKey(),
            '_decree' => $this->integer()->notNull(),
            '_student' => $this->integer()->notNull(),
            '_admin' => $this->integer()->notNull(),
            '_student_meta' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_e_decree_student_decree',
            'e_decree_student',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_decree_student_student',
            'e_decree_student',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_e_decree_student_student_meta',
            'e_decree_student',
            '_student_meta',
            'e_student_meta',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_decree_student_admin',
            'e_decree_student',
            '_admin',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('e_student_meta', '_decree', $this->integer()->null());
        $this->addForeignKey(
            'fk_e_student_meta_decree',
            'e_student_meta',
            '_decree',
            'e_decree',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('e_student_meta', '_decree');
        $this->dropTable('e_decree_student');
    }

}
