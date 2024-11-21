<?php

use common\models\archive\EStudentDiploma;
use common\models\system\Admin;
use common\models\system\AdminMessage;
use yii\db\Migration;
use yii\helpers\Html;

/**
 * Class m210721_093729_alter_e_student_diploma_table
 */
class m210721_093729_alter_e_student_diploma_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = EStudentDiploma::tableName();

        $sql = <<<SQL
SELECT 
    a.id id,
    a._student _student,
    a.student_name student_name,
    a.student_id_number student_id_number,
    a.diploma_number diploma_number,
    a.register_number register_number,
    a.hash hash,
    a._uid _uid
FROM $table a,
     $table b
WHERE a.id < b.id
    AND a._student = b._student
    AND a.diploma_number = b.diploma_number
    AND a.register_number = b.register_number;
SQL;

        $data = $this->getDb()->createCommand($sql)->queryAll();

        if (count($data)) {
            $shouldBeDeleted = \yii\helpers\ArrayHelper::getColumn($data, 'id');
            $sql = "DELETE FROM $table WHERE id IN (" . implode(',', $shouldBeDeleted) . ")";

            if ($count = $this->getDb()->createCommand($sql)->execute()) {
                echo $title = "$count duplicated diplomas deleted from database\n";

                $admin = Admin::findOne(['login' => Admin::SUPER_ADMIN_LOGIN]);
                $techAdmin = Admin::findOne(['login' => Admin::TECH_ADMIN_LOGIN]);

                try {
                    if ($message = AdminMessage::createDraftMessage($admin)) {
                        $message->_recipients = [@$admin->contact->id, @$techAdmin->contact->id];
                        $message->title = $title;
                        $message->message = "<p></p><pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                        $message->sendMessage();
                    }
                } catch (Exception $exception) {
                    echo $exception->getMessage();
                    echo $exception->getTraceAsString();
                }
            }
        }


        $this->alterColumn(EStudentDiploma::tableName(), '_student', $this->integer()->notNull()->unique());
        $this->alterColumn(EStudentDiploma::tableName(), 'diploma_number', $this->string(20)->notNull()->unique());
        $this->alterColumn(EStudentDiploma::tableName(), 'register_number', $this->string(30)->notNull()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = "alter table e_student_diploma drop constraint e_student_diploma__student_key;
alter table e_student_diploma drop constraint e_student_diploma_diploma_number_key;
alter table e_student_diploma drop constraint e_student_diploma_register_number_key;";
        foreach (explode(PHP_EOL, $sql) as $query)
            $this->getDb()->createCommand($query)->execute();
    }
}
