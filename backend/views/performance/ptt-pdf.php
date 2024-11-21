<?php

use backend\widgets\GridView;
use common\models\performance\estudentptt;

/* @var $this \backend\components\View */
/* @var $model EStudentPtt */
$semester = -1;
?>
<style>
    h4, h3 {
        text-align: center;
    }

    .bordered {
        border-top: 1px solid #5a5858;
        border-left: 1px solid #5a5858;
    }

    .bordered td,
    .bordered th {
        border-bottom: 1px solid #5a5858;
        border-right: 1px solid #5a5858;
    }
</style>
<div style="width: 60%; margin: 0 auto">
    <h3><?= \common\models\structure\EUniversity::findCurrentUniversity()->name ?></h3>
    <h4><?= __('Shaxsiy jadval №{number}', ['number' => $model->number]) ?></h4>
</div>
<table style="width: 100%;margin-left: -8px" cellspacing="8">
    <tr>
        <td colspan="2"><b><?= __('Faculty') ?>:</b> <?= $model->department->name ?></td>
    </tr>
    <tr>
        <td colspan="2"><b><?= __("Bachelor Specialty") ?>:</b>
            <?= $model->specialty->code . ' - ' . $model->specialty->mainSpecialty->name ?>
        </td>
    </tr>

    <tr>
        <td width="33%"><b><?= __("Education Type") ?>:</b> <?= $model->educationType->name ?></td>
        <td width="69%"><b><?= __("Education Form") ?>:</b> <?= $model->educationForm->name ?></td>
    </tr>
    <tr>
        <td width="33%"><b><?= __("Level") ?>:</b> <?= $model->student->meta->level->name ?></td>
        <td width="69%"><b><?= __("Group") ?>:</b> <?= $model->group->name ?></td>
    </tr>
    <tr>
        <td colspan="2"><b><?= __("Fullname of Student") ?>:</b> <?= $model->student->getFullName() ?></td>
    </tr>
    <tr>
        <td colspan="2"><b><?= __("Send Date") ?>:</b> <?= Yii::$app->formatter->asDate($model->date->getTimestamp()) ?>
        </td>
    </tr>
</table>
<br>
<table class="bordered" cellpadding="12" cellspacing="0">
    <thead>
    <tr>
        <th width="5%" style="text-align: center;"><?= __('T/r') ?></th>
        <th width="25%" style="text-align: center;"><?= __('Subject') ?></th>
        <th width="12%" style="text-align: center;"><?= __('Umumiy soat / kredit') ?></th>
        <th width="12%" style="text-align: center;"><?= __('Ball / baho') ?></th>
        <th width="21%" style="text-align: center;"><?= __("O'qituvchi F.I.SH") ?></th>
        <th width="12%" style="text-align: center;"><?= __("Imzo") ?></th>
        <th width="12%" style="text-align: center;"><?= __("Date") ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->studentPttSubjects as $i => $subject): ?>
        <?php if ($semester != $subject->curriculumSubject->_semester): ?>
            <tr>
                <td colspan="7" style="text-align: center; background-color: #eeecec">
                    <b><?= $subject->curriculumSubject->semester->name ?></b>
                </td>
            </tr>
            <?php
            $semester = $subject->curriculumSubject->_semester;
            ?>
        <?php endif; ?>
        <tr>
            <td style="text-align: center"><?= $i + 1 ?></td>
            <td><?= $subject->curriculumSubject->subject->name ?></td>
            <td style="text-align: center"><?= $subject->curriculumSubject->total_acload . ' / ' . $subject->curriculumSubject->credit ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p><i>
        <?= __('Asos: {university} rektorining {date} sanadagi {number} sonli buyrug\'i', [
            'university' => \common\models\structure\EUniversity::findCurrentUniversity()->name,
            'date' => Yii::$app->formatter->asDate($model->decree->date->getTimestamp()),
            'number' => $model->decree->number
        ]) ?>
    </i>
</p>
<br>
<p style="text-align: center">
    <?= $model->department->dean ? sprintf("%s &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; %s. %s. %s", __('Dean:'), $model->department->dean->first_name[0], $model->department->dean->third_name[0], $model->department->dean->second_name) : '' ?>
</p>
