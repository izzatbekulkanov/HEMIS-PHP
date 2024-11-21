<?php

/**
 * @var $this View
 * @var $model EStudentDiploma
 */

use backend\components\View;
use common\components\Config;
use common\models\archive\EStudentDiploma;
use common\models\structure\EUniversity;
use Da\QrCode\QrCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$langUz = Config::LANGUAGE_UZBEK;
$langEn = Config::LANGUAGE_ENGLISH;
$universityNameUz = $model->getTranslation('university_name', Config::LANGUAGE_UZBEK);
$universityNameEn = $model->getTranslation('university_name', Config::LANGUAGE_ENGLISH);

$rector_full_name = $model->getTranslation('rector_fullname', Config::LANGUAGE_UZBEK);
$rector_full_name_en = $model->getTranslation('rector_fullname', Config::LANGUAGE_ENGLISH);
$registrationNumber = $model->register_number;
$unv_requisites_phones = EUniversity::findCurrentUniversity()->mailing_address;
$global_qualifications = sprintf(
    '%s / %s',
    $model->getTranslation('qualification_name', Config::LANGUAGE_UZBEK),
    $model->getTranslation('qualification_name', Config::LANGUAGE_ENGLISH)
);
$exam_languages = sprintf('%s/%s', $model->getTranslation('education_language', Config::LANGUAGE_UZBEK), $model->getTranslation('education_language', Config::LANGUAGE_ENGLISH));
$last_education = sprintf(
    '%s / %s',
    $model->getTranslation('last_education', Config::LANGUAGE_UZBEK),
    $model->getTranslation('last_education', Config::LANGUAGE_ENGLISH)
);
$specialty_name = sprintf(
    '%s - %s / %s - %s',
    $model->specialty_code,
    $model->getTranslation('specialty_name', Config::LANGUAGE_UZBEK),
    $model->specialty_code,
    $model->getTranslation('specialty_name', Config::LANGUAGE_ENGLISH)
);
$qualifications = sprintf(
    '%s, %s / %s, %s',
    $model->getTranslation('education_type_name', Config::LANGUAGE_UZBEK),
    $model->order_date->format('d.m.Y'),
    $model->getTranslation('education_type_name', Config::LANGUAGE_ENGLISH),
    $model->order_date->format('d.m.Y')
);
$awarding_institution = sprintf('%s, %s / %s, %s', $universityNameUz, $model->getTranslation('given_hei_information', Config::LANGUAGE_UZBEK), $universityNameEn, $model->getTranslation('given_hei_information', Config::LANGUAGE_ENGLISH));
$prev_awarding_institution = sprintf(
    '%s / %s',
    $model->getTranslation('last_education', Config::LANGUAGE_UZBEK),
    $model->getTranslation('last_education', Config::LANGUAGE_ENGLISH)
);
$moved_hei = '';
if (!empty($model->moved_hei)) {
$moved_hei = sprintf(
    '%s / %s',
    $model->getTranslation('moved_hei', Config::LANGUAGE_UZBEK),
    $model->getTranslation('moved_hei', Config::LANGUAGE_ENGLISH)
);
}
$fullNameUz = preg_split('/\s+/', $model->getTranslation('student_name', $langUz));
$fullNameEn = preg_split('/\s+/', $model->getTranslation('student_name', $langEn));
if ($model->student !== null) {
    $firstNameUzParts = preg_split('/\s+/', $model->student->getTranslation('second_name', $langUz));
    $firstNameEnParts = preg_split('/\s+/', $model->student->getTranslation('second_name', $langEn));
}
$firstNameUz = implode(' ', array_slice($fullNameUz, 0, isset($firstNameUzParts) ? count($firstNameUzParts) : 1));
$firstNameEn = implode(' ', array_slice($fullNameEn, 0, isset($firstNameEnParts) ? count($firstNameEnParts) : 1));
$secondNameUz = implode(' ', array_slice($fullNameUz, isset($firstNameUzParts) ? count($firstNameUzParts) : 1));
$secondNameEn = implode(' ', array_slice($fullNameEn, isset($firstNameEnParts) ? count($firstNameEnParts) : 1));
?>
<style>
    <?=$this->renderFile('@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css'); ?>
    <?=$this->renderFile('@app/assets/app/css/diploma-application.css');?>
</style>
<body>
<div class="p1-diploma-number"><?= $model->diploma_number ?></div>
<div class="p1-unv-address-requisites-en">(<?= __('diplomsiz ilova haqiqiy hisoblanmaydi', [], $langUz) ?>/<?= __('diplomsiz ilova haqiqiy hisoblanmaydi', [], $langEn) ?>)</div>
<div class="p1-unv-address-requisites-uz-header"><?= __('Oliy ta’lim muassasasining rekvizitlari: pochta manzili, shahar, ko‘cha, uy raqami, telefonlari, elektron pochta manzili', [], $langUz) ?></div>
<div class="p1-unv-address-requisites-uz"><?= $model->getTranslation('post_address', Config::LANGUAGE_UZBEK) ?></div>
<div class="p1-unv-address-requisites-en-header"><?= __('Oliy ta’lim muassasasining rekvizitlari: pochta manzili, shahar, ko‘cha, uy raqami, telefonlari, elektron pochta manzili', [], $langEn) ?></div>
<div class="p1-unv-address-requisites-phones"><?= $model->getTranslation('post_address', Config::LANGUAGE_ENGLISH) ?></div>
<!-- Page 1 (#2) -->
<div class="p1-heading">1. <?= __('Diplom sohibi haqida ma\'lumotlar', [], $langUz) ?> / <?= __('Diplom sohibi haqida ma\'lumotlar', [], $langEn) ?></div>
<div class="p1-heading-1 p-heading-title">1.1 <?= __('Familiyasi', [], $langUz) ?>/<?= __('Familiyasi', [], $langEn) ?></div>
<div class="p1-heading-2 p-heading-title">1.2 <?= __('Ismi, otasining ismi', [], $langUz) ?>/<?= __('Ismi, otasining ismi', [], $langEn) ?></div>
<div class="p1-last-name"><?= $firstNameUz ?> / <?= $firstNameEn ?></div>
<div class="p1-first-name"><?= $secondNameUz ?> / <?= $secondNameEn ?></div>
<div class="p1-heading-3 p-heading-title">1.3 <?= __('Tug\'ilgan sana', [], $langUz) ?>/<?= __('Tug\'ilgan sana', [], $langEn) ?></div>
<div class="p1-heading-4 p-heading-title">1.4 <?= __('Talabaning identifikatsion raqami va kodi', [], $langUz) ?>/<?= __('Talabaning identifikatsion raqami va kodi', [], $langEn) ?></div>
<div class="p1-birth-day"><?= $model->student_birthday->format('d.m.Y') ?></div>
<div class="p1-pin-fl"><?= $model->student_id_number ?> </div>
<div class="p1-heading-5 p-heading-title">1.5 <?= __('Avvalgi ta\'lim ma\'lumoti', [], $langUz) ?>/<?= __('Avvalgi ta\'lim ma\'lumoti', [], $langEn) ?></div>
<div class="p1-prev-unv-info"><?= $last_education ?></div>
<div class="p2-heading">2. <?= __('Malakasi haqida ma\'lumotlar', [], $langUz) ?> / <?= __('Malakasi haqida ma\'lumotlar', [], $langEn) ?></div>
<div class="p2-heading-1 p-heading-title">2.1 <?= __('Malakasining (darajasining) nomi, berilgan sanasi', [], $langUz) ?>/<?= __('Malakasining (darajasining) nomi, berilgan sanasi', [], $langEn) ?></div>
<div class="p1-qualifications"><?= $qualifications ?></div>
<div class="p1-gov-state-att"><?= __('Davlat attestatsiya komissiyasining qarori', [], $langUz) ?>/<?= __('Davlat attestatsiya komissiyasining qarori', [], $langEn) ?></div>
<div class="p1-gov-state-att-commission"><?= $model->order_date->format('d.m.Y') ?></div>
<div class="p2-heading-2 p-heading-title">2.2 <?= __('Ta\'lim yo\'nalishi, mutaxassisligi', [], $langUz) ?>/<?= __('Ta\'lim yo\'nalishi, mutaxassisligi', [], $langEn) ?></div>
<div class="p1-field-of-study"><?= $specialty_name ?></div>
<div class="p2-heading-3 p-heading-title">2.3 <?= __('Kvalifikatsiyasi', [], $langUz) ?>/<?= __('Kvalifikatsiyasi', [], $langEn) ?></div>
<div class="p1-global-qualifications"><?= $global_qualifications ?></div>
<div class="p2-heading-4 p-heading-title">2.4 <?= __('Diplom bergan oliy ta\'lim muassasasining nomi, tashkiliy-huquqiy shakli, ta\'lim muassasasining turi', [], $langUz) ?>/<?= __('Diplom bergan oliy ta\'lim muassasasining nomi, tashkiliy-huquqiy shakli, ta\'lim muassasasining turi', [], $langEn) ?></div>
<div class="p1-awarding_institution"><?= $awarding_institution ?></div>
<div class="p2-heading-5 p-heading-title">2.5 <?= __('Avval tahsil olgan oliy ta\'lim muassasasining nomi va tashkiliy-huquqiy shakli, o\'qitish davri', [], $langUz) ?>/<?= __('Avval tahsil olgan oliy ta\'lim muassasasining nomi va tashkiliy-huquqiy shakli, o\'qitish davri', [], $langEn) ?></div>
<div class="p1-prev_awarding_institution"><?= $moved_hei ?></div>
<div class="p2-heading-6 p-heading-title">2.6 <?= __('Ta\'lim (imtihon) til(lar)i', [], $langUz) ?>/<?= __('Ta\'lim (imtihon) til(lar)i', [], $langEn) ?></div>
<div class="p1-exam-languages"><?= $exam_languages ?></div>
</body>