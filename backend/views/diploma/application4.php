<?php

/**
 * @var $this View
 * @var $model EStudentDiploma
 * @var $records array
 */

use backend\components\View;
use common\components\Config;
use common\models\archive\EStudentDiploma;
use common\models\structure\EUniversity;
use Da\QrCode\QrCode;
use yii\helpers\Html;
use yii\helpers\Url;
$langUz = Config::LANGUAGE_UZBEK;
$langEn = Config::LANGUAGE_ENGLISH;

$rector_full_name = $model->getTranslation('rector_fullname', Config::LANGUAGE_UZBEK);
$rector_full_name_en = $model->getTranslation('rector_fullname', Config::LANGUAGE_ENGLISH);
$unv_rating_system = sprintf('%s / %s', $model->getTranslation('marking_system', Config::LANGUAGE_UZBEK), $model->getTranslation('marking_system', Config::LANGUAGE_ENGLISH));

$can_continue_studying = sprintf('%s / %s', $model->getTranslation('next_edu_information', Config::LANGUAGE_UZBEK), $model->getTranslation('next_edu_information', Config::LANGUAGE_ENGLISH));
$additional_information_to_degree = sprintf('%s / %s', $model->getTranslation('additional_info', Config::LANGUAGE_UZBEK), $model->getTranslation('additional_info', Config::LANGUAGE_ENGLISH));

$professional_status = sprintf('%s / %s', $model->getTranslation('professional_activity', Config::LANGUAGE_UZBEK), $model->getTranslation('professional_activity', Config::LANGUAGE_ENGLISH));

$unv_accreditation_info = sprintf('%s / %s', $model->getTranslation('university_accreditation', Config::LANGUAGE_UZBEK), $model->getTranslation('university_accreditation', Config::LANGUAGE_ENGLISH));
?>
<style>
    <?= $this->renderFile('@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css'); ?>
    <?= $this->renderFile('@app/assets/app/css/diploma-application.css');?>
</style>
<body>
<div class="p4-table-box">
    <table style="width: 100%">
        <?php foreach ($records as $k => $record): ?>
            <tr>
                <td><?= $record['id'] ?></td>
                <td><?= $record['name'] ?></td>
                <td><?= $record['acload'] ?></td>
                <td><?= $record['point'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<div class="p4-headline-4">4.4 <?= __('Oliy ta\'lim muassasasida baholash tizimi', [], $langUz) ?>/<?= __('Oliy ta\'lim muassasasida baholash tizimi', [], $langEn) ?></div>
<div class="p4-unv-rating-system"><?= $unv_rating_system ?></div>
<div class="p4-headline-5">4.5 <?= __('Diplom haqida qo\'shimcha ma\'lumotlar', [], $langUz) ?>/<?= __('Diplom haqida qo\'shimcha ma\'lumotlar', [], $langEn) ?></div>
<div class="p4-sp-info-to-degree"><?= $additional_information_to_degree ?></div>
<div class="p5-headline">5. <?= __('Diplom bilan beriladigan huquq va imtiyozlar', [], $langUz) ?> / <?= __('Diplom bilan beriladigan huquq va imtiyozlar', [], $langEn) ?></div>
<div class="p5-headline-1">5.1 <?= __('Ta\'limni davom ettirish imkoniyatlari', [], $langUz) ?>/<?= __('Ta\'limni davom ettirish imkoniyatlari', [], $langEn) ?></div>
<div class="p4-can-continue-studying"><?= $can_continue_studying ?></div>
<div class="p5-headline-2">5.2 <?= __('Kasbiga oid huquqiy maqomi', [], $langUz) ?>/<?= __('Kasbiga oid huquqiy maqomi', [], $langEn) ?></div>
<div class="p4-professional-status"><?= $professional_status ?></div>
<div class="p6-headline">6. <?= __('Qo\'shimcha ma\'lumotlar', [], $langUz) ?>/<?= __('Qo\'shimcha ma\'lumotlar', [], $langEn) ?></div>
<div class="p4-unv-accreditation-info"><?= $unv_accreditation_info ?></div>
<div class="p4-sign-place"><?= __('Imzo va Muhr', [], $langUz) ?> / <?= __('Imzo va Muhr', [], $langEn) ?></div>
<div class="p4-diploma-number-label"><?= __('Diplomga ilova', [], $langUz) ?>/<?= __('Diplomga ilova', [], $langEn) ?></div>
<div class="p4-diploma-number"><?= $model->diploma_number ?></div>
<div class="p4-rector-label"><?= __('Rektor', [], $langUz) ?>/<?= __('Rektor', [], $langEn) ?></div>
<div class="p4-rector-full-name"><?= $rector_full_name ?></div>
<div class="p4-doc-number-label"><?= __('Ro\'yxat raqami', [], $langUz) ?>/<?= __('Ro\'yxat raqami', [], $langEn) ?></div>
<div class="p4-doc-number"><?= $model->register_number ?></div>
<div class="p4-issue-date-label"><?= __('Berilgan vaqti', [], $langUz) ?>/<?= __('Berilgan vaqti', [], $langEn) ?></div>
<div class="p4-issue-date"><?= $model->register_date->format('d.m.Y') ?></div>
</body>