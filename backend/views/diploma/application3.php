<?php

/**
 * @var $this View
 * @var $model EStudentDiploma
 * @var $records array|\common\models\archive\EAcademicRecord[]
 */

use backend\components\View;
use common\components\Config;
use common\models\archive\EStudentDiploma;
use common\models\structure\EUniversity;
use Da\QrCode\QrCode;
use yii\helpers\Html;
use yii\helpers\Url;

$student_full_name = $model->getTranslation('student_name', Config::LANGUAGE_UZBEK) . ' / ' . $model->getTranslation(
        'student_name',
        Config::LANGUAGE_ENGLISH
    )
?>
<style>
    <?=$this->renderFile('@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css'); ?>
    <?=$this->renderFile('@app/assets/app/css/diploma-application.css');?>
</style>
<body>
<div class="p3-student-full-name"><?= $student_full_name ?></div>
<div class="p3-student-fullname-line"></div>
<div class="p3-student-fullname-tip"><?= __('Talabaning F.I.O', [], Config::LANGUAGE_UZBEK) ?>/<?= __('Talabaning F.I.O', [], Config::LANGUAGE_ENGLISH) ?></div>
<div class="p4-headline-3">4.3 <?= __('Ta\'lim dasturini o\'zlashtirish natijalari haqida ma\'lumotlar', [], Config::LANGUAGE_UZBEK) ?>/<?= __('Ta\'lim dasturini o\'zlashtirish natijalari haqida ma\'lumotlar', [], Config::LANGUAGE_ENGLISH) ?></div>
<div class="p3-table-box">
    <table>
        <tr>
            <td>T/r</td>
            <td>Fan (modul)ning nomi / Name of the course (module)</td>
            <td>Soatlarning umumiy miqdori / Total hours in the curriculum</td>
            <td>Baholash/Grade (reyting, ball, kredit, baho / rating, score, credit, mark)</td>
        </tr>
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
</body>