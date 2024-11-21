<?php
use common\models\curriculum\MarkingSystem;
use common\components\Config;
use yii\db\Migration;

/**
 * Class m210531_160339_alter_table_marking_system
 */
class m210531_160339_alter_table_marking_system extends Migration
{
    public function safeUp()
    {
        $this->addColumn('h_marking_system', 'description', $this->text()->null());

        if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_RATING)) {
            $type->updateAttributes(['description' => 'O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta\'lim vazirligining 2009 yil 11 iyundagi 204-sonli buyrug‘i bilan tasdiqlangan (Adliya vazirligi tomonidan 2009-yil 10-iyulda 1981-raqam bilan ro‘yxatdan o\'tkazilgan) “Oliy ta\'lim muassasalarida talabalar bilimini nazorat qilish va baholashning reyting tizimi to‘g‘risida Nizom”ga asosan talabalar bilimini baholash amalga oshiriladi.']);
            $type->setTranslation('description','O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta\'lim vazirligining 2009 yil 11 iyundagi 204-sonli buyrug‘i bilan tasdiqlangan (Adliya vazirligi tomonidan 2009-yil 10-iyulda 1981-raqam bilan ro‘yxatdan o\'tkazilgan) “Oliy ta\'lim muassasalarida talabalar bilimini nazorat qilish va baholashning reyting tizimi to‘g‘risida Nizom”ga asosan talabalar bilimini baholash amalga oshiriladi.',Config::LANGUAGE_UZBEK);
            $type->setTranslation('description','According to the "Regulations on the rating system of control and evaluation of student knowledge in higher education institutions", approved by the order of the Ministry of Higher and Secondary Special Education of the Republic of Uzbekistan dated June 11, 2009 No 204 (Registered by the Ministry of Justice on July 10, 2009 No 1981) assessment of student knowledge is carried out.',Config::LANGUAGE_ENGLISH);
            $type->updateAttributes(['_translations'=>$type->_translations]);
        }
        if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_FIVE)) {
            $type->updateAttributes(['description' => 'O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta\'lim vazirligining 2018 yil 9 avgustdagi 19-2018-sonli buyrug‘i bilan tasdiqlangan (Adliya vazirligi tomonidan 2018-yil 26-sentabrda 3069-raqam bilan ro‘yxatdan o\'tkazilgan) “Oliy ta’lim muassasalarida talabalar bilimini nazorat qilish va baholash tizimi to‘g‘risidagi Nizom”ga asosan talabalar bilimini baholash amalga oshiriladi.']);
            $type->setTranslation('description','O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta\'lim vazirligining 2018 yil 9 avgustdagi 19-2018-sonli buyrug‘i bilan tasdiqlangan (Adliya vazirligi tomonidan 2018-yil 26-sentabrda 3069-raqam bilan ro‘yxatdan o\'tkazilgan) “Oliy ta’lim muassasalarida talabalar bilimini nazorat qilish va baholash tizimi to‘g‘risidagi Nizom”ga asosan talabalar bilimini baholash amalga oshiriladi.',Config::LANGUAGE_UZBEK);
            $type->setTranslation('description','According to the "Regulations on the system of control and evaluation of student knowledge in higher education institutions", approved by the order of the Ministry of Higher and Secondary Special Education of the Republic of Uzbekistan  dated August 9, 2018 No 19-2018 (Registered by the Ministry of Justice on September 26, 2018 No 3069) assessment of student knowledge is carried out.',Config::LANGUAGE_ENGLISH);
            $type->updateAttributes(['_translations'=>$type->_translations]);
        }
        if ($type = MarkingSystem::findOne(MarkingSystem::MARKING_SYSTEM_CREDIT)) {
            $type->updateAttributes(['description' => 'O‘zbekiston Respublikasi Vazirlar Mahkamasining 2020 yil 31 dekabrdagi 824-sonli qarori bilan tasdiqlangan “Oliy ta’lim muassasalarida o‘quv jarayoniga kredit-modul tizimini joriy etish tartibi to‘g‘risida Nizom”ga asosan talabalar bilimini baholash amalga oshiriladi.']);
            $type->setTranslation('description','O‘zbekiston Respublikasi Vazirlar Mahkamasining 2020 yil 31 dekabrdagi 824-sonli qarori bilan tasdiqlangan “Oliy ta’lim muassasalarida o‘quv jarayoniga kredit-modul tizimini joriy etish tartibi to‘g‘risida Nizom”ga asosan talabalar bilimini baholash amalga oshiriladi.',Config::LANGUAGE_UZBEK);
            $type->setTranslation('description','According to the "Regulations on the procedure for introducing a credit-module system in the educational process in higher education institutions", approved by the decree of the Cabinet of Ministries of the Republic of Uzbekistan dated December 31, 2020 No 824 assessment of student knowledge is carried out.',Config::LANGUAGE_ENGLISH);
            $type->updateAttributes(['_translations'=>$type->_translations]);
        }
    }

    public function safeDown()
    {
        $this->dropColumn('h_marking_system', 'description');
    }
}
