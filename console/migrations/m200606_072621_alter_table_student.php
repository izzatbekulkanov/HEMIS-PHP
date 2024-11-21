<?php

use yii\db\Migration;

/**
 * Class m200606_072621_alter_table_student
 */
class m200606_072621_alter_table_student extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('e_system_log', '_admin', $this->integer()->null());
        $this->addColumn('e_system_log', '_student', $this->integer()->null());

        $this->addForeignKey(
            'fk_system_log_student',
            'e_system_log',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('e_system_login', 'user', $this->integer(1));
        $this->addColumn('e_system_log', 'user', $this->integer(1));

        $this->addColumn('e_student', 'password', $this->string(256));
        $this->addColumn('e_student', 'auth_key', $this->string(32));
        $this->addColumn('e_student', 'access_token', $this->string(32));
        $this->addColumn('e_student', 'password_reset_token', $this->string(32));
        $this->addColumn('e_student', 'password_reset_date', $this->dateTime());

        foreach (\common\models\student\EStudent::find()->all() as $student) {
            echo $student->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropColumn('e_system_log', '_student');
        $this->dropColumn('e_system_login', 'user');
        $this->dropColumn('e_system_log', 'user');
        $this->dropColumn('e_student', 'password');
        $this->dropColumn('e_student', 'auth_key');
        $this->dropColumn('e_student', 'access_token');
        $this->dropColumn('e_student', 'password_reset_token');
        $this->dropColumn('e_student', 'password_reset_date');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200606_072621_alter_table_student cannot be reverted.\n";

        return false;
    }
    */
}
