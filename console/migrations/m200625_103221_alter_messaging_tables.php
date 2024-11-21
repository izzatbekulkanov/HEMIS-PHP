<?php

use yii\db\Migration;

/**
 * Class m200625_103221_alter_messaging_tables
 */
class m200625_103221_alter_messaging_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        /**
         * Kontaktlar jadvali
         */
        $this->createTable('e_admin_message_contact', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128),
            'label' => $this->string(128),
            'type' => $this->string(16),
            'active' => $this->boolean(),

            '_admin' => $this->integer()->unique()->null(),
            '_student' => $this->integer()->unique()->null(),

            '_employee' => $this->integer()->null(),
            '_role' => $this->integer()->null(),

            '_department' => $this->integer()->null(),
            '_group' => $this->integer()->null(),
            '_student_department' => $this->integer()->null(),
            '_translations' => 'jsonb',
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_message_contact_admin',
            'e_admin_message_contact',
            '_admin',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );


        $this->addForeignKey(
            'fk_admin_message_contact_employee',
            'e_admin_message_contact',
            '_employee',
            'e_employee',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_contact_role',
            'e_admin_message_contact',
            '_role',
            'e_admin_role',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_contact_department',
            'e_admin_message_contact',
            '_department',
            'e_department',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_contact_group',
            'e_admin_message_contact',
            '_group',
            'e_group',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_contact_student',
            'e_admin_message_contact',
            '_student',
            'e_student',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_contact_student_department',
            'e_admin_message_contact',
            '_student_department',
            'e_department',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /**
         * Qaysi kontakt qaysi kontaktga yoza oladi, shuni hal qilib beradi
         */
        $this->createTable('e_admin_message_contact_user', [
            'id' => $this->primaryKey(),
            '_contact' => $this->integer(),
            '_user' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_admin_message_contact_user_contact',
            'e_admin_message_contact_user',
            '_contact',
            'e_admin_message_contact',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_contact_user_user',
            'e_admin_message_contact_user',
            '_user',
            'e_admin_message_contact',
            'id',
            'CASCADE',
            'CASCADE'
        );


        $this->createIndex(
            'fk_admin_message_contact_user_unique',
            'e_admin_message_contact_user',
            ['_contact', '_user'],
            true
        );

        /**
         * Avvalgi messaging jadvalidagi _sender va _recipient bog'lanishlar contact jadvaliga o'zgartirildi
         * Bu admin studentga yoki student adminga xabar jo'natish uchun shu qarorga kelindi
         */

        /**
         * Hamma xabarlar testoviy, o'chirib turamiz boshni qotirmasdan, bo'lmasa foreignkey larni alter qila olmaymiz
         */
        \common\models\system\AdminMessageItem::deleteAll();
        \common\models\system\AdminMessage::deleteAll();

        $this->dropForeignKey(
            'fk_admin_message_sender',
            'e_admin_message'
        );
        $this->addForeignKey(
            'fk_admin_message_sender',
            'e_admin_message',
            '_sender',
            'e_admin_message_contact',
            'id',
            'SET NULL',
            'CASCADE'
        );


        $this->dropForeignKey(
            'fk_admin_message_item_recipient',
            'e_admin_message_item'
        );
        $this->addForeignKey(
            'fk_admin_message_item_recipient',
            'e_admin_message_item',
            '_recipient',
            'e_admin_message_contact',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->dropForeignKey(
            'fk_admin_message_item_sender',
            'e_admin_message_item'
        );
        $this->addForeignKey(
            'fk_admin_message_item_sender',
            'e_admin_message_item',
            '_sender',
            'e_admin_message_contact',
            'id',
            'CASCADE',
            'CASCADE'
        );


        \common\models\system\Contact::indexAdmins();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \common\models\system\AdminMessageItem::deleteAll();
        \common\models\system\AdminMessage::deleteAll();

        $this->dropForeignKey(
            'fk_admin_message_item_recipient',
            'e_admin_message_item'
        );
        $this->dropForeignKey(
            'fk_admin_message_item_sender',
            'e_admin_message_item'
        );
        $this->dropForeignKey(
            'fk_admin_message_sender',
            'e_admin_message'
        );

        $this->addForeignKey(
            'fk_admin_message_item_recipient',
            'e_admin_message_item',
            '_recipient',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_item_sender',
            'e_admin_message_item',
            '_sender',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_admin_message_sender',
            'e_admin_message',
            '_sender',
            'e_admin',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->dropTable('e_admin_message_contact_user');
        $this->dropTable('e_admin_message_contact');
    }
}
