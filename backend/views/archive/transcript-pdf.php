<?php

use backend\widgets\GridView;
use common\components\Config;
use common\models\curriculum\MarkingSystem;

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
   //     font-size: 12pt !important;
    }

    .bordered td,
    .bordered th {
        border-bottom: 1px solid #5a5858;
        border-right: 1px solid #5a5858;
       // font-size: 12pt !important;


    }
    .bordered tr {
     //   font-size: 14pt !important;

    }
</style>
<div style="width: 60%; margin: 0 auto">
    <h3><?= $model->getTranslation('university_name', Config::LANGUAGE_UZBEK);?> <br>
        <?= $model->getTranslation('university_name', Config::LANGUAGE_ENGLISH);?>
    </h3>
    <h4><?= $model->getTranscriptLabelUz();?> <br><?= $model->getTranscriptLabelEn();?></h4>
</div>
<table style="width: 100%;margin-left: -8px" cellspacing="8">
    <tr>
        <td>Qayd raqami / Reg. Number: <b><?= $model->academic_register_number ?></b></td>
        <td>Berilgan sanasi / Given date: <b><?= Yii::$app->formatter->asDate($model->academic_register_date->getTimestamp()) ?></b></td>
    </tr>
    <tr>
        <td style="vertical-align:top;">Ta’lim oluvchi F.I.Sh. / Full name:</td>
        <td>
            <b><?= $model->getTranslation('student_name', Config::LANGUAGE_UZBEK);?> <br>
            <?= $model->getTranslation('student_name', Config::LANGUAGE_ENGLISH);?></b>
        </td>
    </tr>
    <tr>
        <td colspan="2">Tug‘ilgan sanasi / Date of birth: <b><?= Yii::$app->formatter->asDate($model->student_birthday->getTimestamp()) ?></b></td>
    </tr>
    <tr>
        <td colspan="2">Akademik daraja / Academic degree:
            <b><?= $model->getTranslation('education_type_name', Config::LANGUAGE_UZBEK);?> /
            <?= $model->getTranslation('education_type_name', Config::LANGUAGE_ENGLISH);?></b></td>
    </tr>
    <tr>
        <td colspan="2">Fakultet / College:
            <b>
                <?= $model->getTranslation('faculty_name', Config::LANGUAGE_UZBEK);?> /
                <?= $model->getTranslation('faculty_name', Config::LANGUAGE_ENGLISH);?>
            </b>
        </td>
    </tr>
    <tr>
        <td colspan="2">Yo‘nalish / Major:
            <b><?= $model->specialty->code . ' - '. $model->getTranslation('specialty_name', Config::LANGUAGE_UZBEK);?> /
                <?= $model->specialty->code . ' - '. $model->getTranslation('specialty_name', Config::LANGUAGE_ENGLISH);?>
            </b>
        </td>
    </tr>

    <tr>
        <td colspan="2">O‘qishga qabul qilingan sana / Date of admission: <b><?= $model->year_of_entered; ?></b></td>
    </tr>
    <tr>
        <td colspan="2">Ta’lim oluvchining maqomi / Student status: <b>
                <?= $model->getTranslation('student_status', Config::LANGUAGE_UZBEK);?>
                / <?= $model->getTranslation('student_status', Config::LANGUAGE_ENGLISH);?></b></td>
    </tr>
    <tr>
        <td colspan="2">O‘qishni tamomlagan sanasi / Date of graduation: <b><?= $model->year_of_graduated; ?></b></td>
    </tr>

</table>
<br>
<?php
    $all_total_acload = $all_credit = $all_grade = $totalGpa = 0;
?>
<table class="bordered" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th width="10px" style="text-align: center;"><?= __('T/r') ?></th>
        <th width="" style="text-align: center;">Fan nomi / Name of course</th>
        <th width="18%" style="text-align: center;">Yuklama / Kredit <br>(Acload/ Credit)</th>
        <th width="12%" style="text-align: center;">Ball / Baho <br>
            (Score/Grade)
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->studentTranscriptSubjects as $i => $subject): ?>
        <?php if ($semester != $subject->_semester): ?>
            <tr>
                <td colspan="4" style="text-align: center; background-color: #eeecec">
                    <b><?= $subject->semester_name; ?></b>
                </td>
            </tr>
            <?php
            $semester = $subject->_semester;
            ?>
        <?php endif; ?>
        <tr>
            <td style="text-align: center;"><?= $i + 1 ?></td>
            <td style=""><?= $subject->subject_name ?></td>
            <td style="text-align: center">
                <?= $subject->total_acload . ' / ' . round($subject->credit,0) ?>
                <?php
                    $all_total_acload += $subject->total_acload;
                    $all_credit += round($subject->credit,0);
                 ?>
            </td>
            <td style="text-align: center">
                <?= round($subject->total_point,0) . ' / ' . round($subject->grade,0) ?>
                <?php
                    $totalGpa += round($subject->credit * $subject->grade, 0);
                ?>
            </td>

        </tr>
    <?php endforeach; ?>
    <?php if($model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT) :?>
        <tr>
            <td colspan="2" rowspan="2">
                <b>
                    Baholash tizimi / Grading system comparison: <br>
                    A = 4.26 - 4.50 (86-90); &nbsp;&nbsp;   A+ = 4.51 - 5.00 (90-100) <br>
                    B = 3.51 - 4.00 (71-80); &nbsp;&nbsp;   B+ = 4.01 - 4.25 (81-85) <br>
                    C = 3.00 - 3.25 (60-65); &nbsp;&nbsp;   C+ = 3.26 - 3.50 (66-70) <br>
                </br>
            </td>
            <td style="text-align: center"><b>Jami / Total </b></td>
            <td style="text-align: center"><b>GPA</b> </td>
        </tr>
        <tr>
            <td style="text-align: center"><b><?= $all_credit > 0 ? ($all_total_acload .' / '.$all_credit) : "";?> </b></td>
            <td style="text-align: center"><b><?=  $all_credit > 0 ? round($totalGpa / $all_credit, 2) : "";?> </b></td>
        </tr>
    <?php endif;?>
    </tbody>
</table>

<br>

<br>
<p style="text-align: center">
    <?= $model->dean ? sprintf("%s &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; %s", __('Dean:'), $model->dean) : '' ?>
</p>


