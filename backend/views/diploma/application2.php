<?php

/**
 * @var $this View
 * @var $model EStudentDiploma
 */

use backend\components\View;
use common\components\Config;
use common\models\archive\EStudentDiploma;

$langUz = Config::LANGUAGE_UZBEK;
$langEn = Config::LANGUAGE_ENGLISH;

$level_of_education = sprintf(
    '%s / %s',
    $model->getTranslation('education_type_name', Config::LANGUAGE_UZBEK),
    $model->getTranslation('education_type_name', Config::LANGUAGE_ENGLISH)
);
$length_of_full_time_programme = sprintf('%s Yil / %s Year(s)', $model->education_period, $model->education_period);
$admission_requirements = sprintf(
    '%s / %s',
    $model->getTranslation('admission_information', Config::LANGUAGE_UZBEK),
    $model->getTranslation('admission_information', Config::LANGUAGE_ENGLISH)
);
$type_of_study = sprintf(
    '%s / %s',
    $model->getTranslation('education_form_name', Config::LANGUAGE_UZBEK),
    $model->getTranslation('education_form_name', Config::LANGUAGE_ENGLISH)
);
$curriculum_description = sprintf(
    '%s / %s',
    $model->getTranslation('qualification_data', Config::LANGUAGE_UZBEK),
    $model->getTranslation('qualification_data', Config::LANGUAGE_ENGLISH)
);
$curriculum_description = preg_replace('/[<>]/', '', $curriculum_description);
?>
<style>
    <?=$this->renderFile('@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css'); ?>
    <?=$this->renderFile('@app/assets/app/css/diploma-application.css');?>
</style>
<body>
<div class="p3-headline">3. <?= __('Ta\'lim darajasi haqida ma\'lumotlar', [], $langUz) ?> / <?= __('Ta\'lim darajasi haqida ma\'lumotlar', [], $langEn) ?></div>
<div class="p3-headline-1 p-heading-title">3.1 <?= __('Ta\'lim darajasi', [], $langUz) ?>/<?= __('Ta\'lim darajasi', [], $langEn) ?></div>
<div class="p2-level-of-education"><?= $level_of_education ?></div>
<div class="p3-headline-2 p-heading-title">3.2 <?= __('Ta\'limning kunduzgi shakli bo\'yicha dasturni o\'zlashtirish muddati', [], $langUz) ?>/<?= __('Ta\'limning kunduzgi shakli bo\'yicha dasturni o\'zlashtirish muddati', [], $langEn) ?></div>
<div class="p2-length-of-full-time-programme"><?= $length_of_full_time_programme ?></div>
<div class="p3-headline-3 p-heading-title">3.3 <?= __('O\'qishga kiruvchilarga qo\'yiladigan talab(lar)', [], $langUz) ?>/<?= __('O\'qishga kiruvchilarga qo\'yiladigan talab(lar)', [], $langEn) ?></div>
<div class="p2-admission-requirements"><?= $admission_requirements ?></div>
<div class="p4-headline">4. <?= __('Ta\'limning mazmuni va erishilgan natijalar haqida ma\'lumotlar', [], $langUz) ?> / <?= __('Ta\'limning mazmuni va erishilgan natijalar haqida ma\'lumotlar', [], $langEn) ?></div>
<div class="p4-headline-1 p-heading-title">4.1 <?= __('Ta\'lim shakli', [], $langUz) ?>/<?= __('Ta\'lim shakli', [], $langEn) ?></div>
<div class="p2-type-of-study"><?= $type_of_study ?></div>
<div class="p4-headline-2 p-heading-title">4.2 <?= __('Ta\'lim dasturining tavsifi', [], $langUz) ?>/<?= __('Ta\'lim dasturining tavsifi', [], $langEn) ?></div>
<div class="p2-curriculum-description"><?= $curriculum_description ?></div>
</body>