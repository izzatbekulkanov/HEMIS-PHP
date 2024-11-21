<?php

use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\curriculum\RatingGrade;
use common\models\performance\estudentptt;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\ExamType;

/* @var $this \backend\components\View */
/* @var $model \common\models\archive\EStudentAcademicSheetMeta */
$semester = -1;
$this->title = $model->student->getFullName();
$grade = [
    '5' => 0,
    '4' => 0,
    '3' => 0,
];
$i = 0;
$exams = [];
?>
<style>


    .bordered {
        border-top: 1px solid #5a5858;
        border-left: 1px solid #5a5858;
    }

    .bordered td,
    .bordered th {
        border-bottom: 1px solid #5a5858;
        border-right: 1px solid #5a5858;
    }

    .center {
        text-align: center;
    }

    table.bordered td, table.bordered th {
        padding: 5px 5px;
    }

    h3 {
        text-transform: uppercase;
    }

    p {
        line-height: 22px;
    }
</style>
<div style="width: 60%; margin: 0 auto" class="center">
    <h3><?= \common\models\structure\EUniversity::findCurrentUniversity()->name ?></h3>
    <h4><?= __('TALABA O\'QUV VARAQASI') ?></h4>
    <h4><?= __('Baholash daftarchasi raqami') ?>: <?= $model->student->student_id_number ?></h4>
</div>
<table style="width: 100%; margin-left: -8px" cellspacing="8">
    <tr>
        <td colspan="2">
            <b>1. <?= __('Talabaning F.I.O') ?></b>:
            <?= $model->student->getFullName() ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <b>2. <?= __('Home Address') ?></b>:
            <?= $model->student->home_address ?></td>
    </tr>
    <tr>
        <td width="50%">
            <b>3. <?= __("O'qishga kirgan sanasi") ?></b>:
            <?= $model->student->year_of_enter ?>
        </td>
        <td width="50%">
            <b><?= __("Decree") ?></b>:
            <?php if ($model->student->_decree_enroll): ?>
                №<?= $model->student->decreeEnroll->number ?> / <?= Yii::$app->formatter->asDate($model->student->decreeEnroll->date->getTimestamp()) ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>4. <?= __("Education Form") ?></b>:
            <?= $model->educationForm->name ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>5. <?= __("Ta’lim yo‘nalishi (mutaxassislik)") ?></b>:
            <?= $model->specialty->code . ' - ' . $model->specialty->mainSpecialty->name ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>6. <?= __("O‘qishdan chetlashtirildi") ?></b>:
            <?php if ($decree = EDecree::getDecreeByType($model->student, DecreeType::TYPE_EXPEL)): ?>
                <?= $decree->getShortInformation() ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>7. <?= __("O‘qishga tiklandi") ?></b>:
            <?php if ($decree = EDecree::getDecreeByType($model->student, DecreeType::TYPE_RESTORE)): ?>
                <?= $decree->getShortInformation() ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>8. <?= __("Akademik ta’til") ?></b>:
            <?php if ($decree = EDecree::getDecreeByType($model->student, DecreeType::TYPE_ACADEMIC_LEAVE)): ?>
                <?= $decree->getShortInformation() ?>
            <?php endif; ?>
        </td>
    </tr>
</table>
<h4><?= __('O‘quv rejaning bajarilishi') ?></h4>
<table class="bordered" cellspacing="0">
    <thead>
    <tr>
        <th width="5%" class="center"><?= __('№') ?></th>
        <th width="50%"><?= __('Subject Name') ?></th>
        <th width="15%" class="center"><?= __('Total hours / credit') ?></th>
        <th width="15%" class="center"><?= __('To\'plagan ball (baho)') ?></th>
        <th width="15%" class="center"><?= __("Rating") ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->getStudentSubjectsWithAcademicRecord() as $subject): ?>
        <?php
        if ($subject->curriculumSubjectExamType) {
            if ($subject->curriculumSubjectExamType->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                $exams[] = $subject;
            }
        }
        $rating = '';
        $i++;
        ?>
        <?php if ($semester != $subject->curriculumSubject->_semester): ?>
            <tr>
                <td colspan="5" style=" background-color: #eeecec">
                    <b><?= __('Level') ?>: </b><?= $subject->curriculumSubject->semester->level->name ?><br>
                    <b><?= __('Education Year') ?>: </b><?= $subject->educationYear->name ?><br>
                    <b><?= __('Semester') ?>: </b><?= $subject->curriculumSubject->semester->name ?>
                </td>
            </tr>
            <?php
            $semester = $subject->curriculumSubject->_semester;
            ?>
        <?php endif; ?>
        <tr>
            <td class="center"><?= $i ?></td>
            <td>
                <?= $subject->curriculumSubject->subject->name ?>
            </td>
            <td class="center">
                <?= $subject->curriculumSubject->total_acload . ' / ' . $subject->curriculumSubject->credit ?>
            </td>
            <?php if ($subject->academicRecord): ?>
                <td class="center">
                    <?php
                    $rating = '';
                    if ($subject->curriculum->markingSystem->isRatingSystem()) {
                        $rating = round($subject->academicRecord->total_acload * $subject->academicRecord->total_point / 100);
                    }
                    $g = round($subject->academicRecord->grade) . '';
                    if (isset($grade[$g])) $grade[$g]++;

                    if ($subject->curriculum->markingSystem->isFiveMarkSystem()) {
                        echo $g;
                    } else {
                        echo sprintf('%s / %s', round($subject->academicRecord->total_point), $g);
                    }
                    ?>
                </td>
                <td class="center">
                    <?= $rating ?>
                </td>
            <?php else: ?>
                <td class="center"></td>
                <td class="center"></td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<br>
<table style="width: 100%; margin-left: -8px" cellspacing="8">
    <tr>
        <td width="55%">
            <b>9. <?= __('O‘qish davomida topshirilgan fanlar soni') ?></b>:
        </td>
        <td width="45%"><?= __('{count} ta', ['count' => $i]) ?></td>
    </tr>
    <tr>
        <td width="55%">
        </td>
        <td width="45%">"5" – <?= __('{count} ta', ['count' => $grade['5']]) ?></td>
    </tr>
    <tr>
        <td width="55%">
        </td>
        <td width="45%">"4" – <?= __('{count} ta', ['count' => $grade['4']]) ?></td>
    </tr>
    <tr>
        <td width="55%">
        </td>
        <td width="45%">"3" – <?= __('{count} ta', ['count' => $grade['3']]) ?></td>
    </tr>
    <tr>
        <td width="55%">
            <b>10. <?= __('BMI (magistrlik dissertatsiyasi)ni bajarishga va topshirishga ruxsat berilgan buyruq raqami va sanasi') ?></b>
        </td>
        <td width="45%">
            <?= $model->student->graduateWork && $model->student->graduateWork->decree ? "№ " . $model->student->graduateWork->decree->number . " / " . Yii::$app->formatter->asDate($model->student->graduateWork->decree->date->getTimestamp(), 'php:d.m.Y') : '' ?>
        </td>
    </tr>
</table>
<br>
<h4>
    11. <?= __('Yakuniy davlat attestatsiyalari natijalari') ?>
</h4>
<table class="bordered" cellspacing="0">
    <thead>
    <tr>
        <th width="5%" class="center"><?= __('№') ?></th>
        <th width="50%"><?= __('Subject Name') ?></th>
        <th width="22.5%"><?= __('DAK qarori sanasi') ?></th>
        <th width="22.5%"><?= __("O'zlashtirish ko'rsatkichi") ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->getStudentSubjectsWithAcademicRecord(RatingGrade::RATING_GRADE_STATE) as $i => $subject): ?>
        <tr>
            <td><?= ($i + 1) ?></td>
            <td><?= $subject->subject->name ?></td>
            <td></td>
            <td class="center"><?= $subject->academicRecord ? sprintf('%s / %s', round($subject->academicRecord->total_point), round($subject->academicRecord->grade)) : '' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<br>
<h4>
    12. <?= __('Bitiruv malakaviy ishi (magistrlik dissertatsiyasi) himoyasi') ?>
</h4>
<table class="bordered" cellspacing="0">
    <thead>
    <tr>
        <th width="5%" class="center"><?= __('№') ?></th>
        <th width="50%"><?= __('Topic') ?></th>
        <th width="22.5%"><?= __('DAK qarori sanasi') ?></th>
        <th width="22.5%"><?= __("O'zlashtirish ko'rsatkichi") ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <?php if ($model->student->graduateWork): ?>
            <td>1</td>
            <td><?= $model->student->graduateWork->work_name ?></td>
            <?php if ($cr = $model->student->graduateWork->certificateCommitteeResult): ?>
                <td class="center">
                    <?= Yii::$app->formatter->asDate($cr->order_date->getTimestamp()) ?>
                </td>
                <td class="center">
                    <?= sprintf('%s / %s', $cr->ball, $cr->grade) ?>
                </td>
            <?php else: ?>
                <td></td>
                <td></td>
            <?php endif; ?>
        <?php endif; ?>
    </tr>
    </tbody>
</table>
<br>
<h4>
    13. <?= __('Davlat attestatsiya komissiyasining qarori') ?>
</h4>
<?php
$diploma = $model->studentDiploma;
?>
<p>
    <b>
        <?= __('DAK qarori sanasi') ?>:
    </b> <?= $diploma ? Yii::$app->formatter->asDate($diploma->order_date->getTimestamp()) : '' ?><br>
    <b>
        <?= __('Berilgan daraja') ?>:
    </b> <?= $model->educationType->name ?><br>
    <b>
        <?= __('Ta’lim yo‘nalishi (mutaxassislik)') ?>:
    </b> <?= $diploma ? sprintf('%s - %s', $diploma->specialty_code, $diploma->specialty_name) : '' ?><br>
    <b>
        <?= __('Diplomni hisobga olish kitobidagi qayd raqami') ?>:
    </b> <?= $diploma ? $diploma->register_number : '' ?>
    <br>
</p>
<br>
<table style="width: 100%;margin-left: -8px" cellspacing="8" class="center">
    <tr>
        <td width="33%"><?= __('Dean:') ?><br>&nbsp;</td>
        <td width="33%">____________________________________<br><?= mb_strtolower(__('Imzo')) ?></td>
        <td width="33%"><?= $model->department->dean ? $model->department->dean->getShortName() : '' ?><br>&nbsp;</td>
    </tr>
</table>