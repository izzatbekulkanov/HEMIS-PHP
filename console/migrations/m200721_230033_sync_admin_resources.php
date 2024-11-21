<?php

use yii\db\Migration;

/**
 * Class m200721_230033_sync_admin_resources
 */
class m200721_230033_sync_admin_resources extends Migration
{
    /**
     * Yangi rollar va resurslarni indexsatsiya qilish uchun ushbu migration ishlatiladi
     * Agar yangi rol yaratilsa yoki mavjud rolga biror resursni biriktirish kerak bo'lsa bu sozlamani
     * common/data/admin-roles.json fayliga kiriting hamda AccessResources::parsePermissions(true);
     * metodini ishga tushiring. Hammasi OK bo'ladi.
     *
     *
     *
     */
    public function safeUp()
    {
        //rollarni va resurslarni yuklaymiz
        common\components\AccessResources::parsePermissions(true);

        //tarjimalarni ham yuklaymiz
        backend\models\FormUploadTrans::parseTranslations();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
