<?php

use common\models\archive\EStudentAcademicInformationDataMeta;
use common\models\curriculum\RatingGrade;
use common\models\system\classifier\ExamType;
use common\models\curriculum\MarkingSystem;


/* @var $this \backend\components\View */
/* @var $model \common\models\archive\EAcademicInformationData */
/* @var $meta \common\models\archive\EStudentAcademicInformationDataMeta */
$semester = -1;
$this->title = $model->student->getFullName();
$meta = EStudentAcademicInformationDataMeta::findOne([
    'id' => $model->_student_meta,
    'active' => true,
    //'_student_status' => [StudentStatus::STUDENT_TYPE_EXPEL, StudentStatus::STUDENT_TYPE_GRADUATED]
]);
$i = 0;
$exams = [];
?>

<div class="subjects p3-student-full-name center">
    <div class="name">
        <b><?= $model->second_name. ' '.$model->first_name.' '.$model->third_name ?></b>
    </div>
    <div class="tip">
    <?= mb_strtolower('(Familiyasi, ismi, оtasining ismi)') ?>
    </div>
</div>

<div style="width: 60%; margin: 0 auto" class="subjects center">
    <h4><b>I. O‘zlashtirgan fanlar ro‘yxati:</b></h4>
</div>

<table class="subjects bordered" cellspacing="0">
    <thead>


    <tr>
        <th width="30px" class="center"><?= __('№') ?></th>
        <th width="425px">О‘zlashtirgan fanlar nomi</th>
        <th width="145px" class="center">О‘quv rejasida <br>belgilangan <br> soatlar miqdori / kredit</th>
        <th width="165px" class="center">О‘zlashtirish <br>ko‘rsatkichi <br>(reyting/baho)</th>
    </tr>
    <tr>
        <td class="center">1</td>
        <td class="center">2</td>
        <td class="center">3</td>
        <td class="center">4</td>
    </tr>


    </thead>
    <tbody>
    <tr>
        <th colspan="4" class="center">
            О‘quv rejasidagi fanlar:
        </th>
    </tr>
    <?php foreach ($meta->getStudentSubjectsWithAcademicInformationData() as $subject): ?>
        <?php
        if ($subject->curriculumSubjectExamType) {
            if ($subject->curriculumSubjectExamType->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                $exams[] = $subject;
                // continue;
            }
        }
        $rating = 0;
        $i++;
        ?>

        <tr>
            <td class="center"><?= $i ?></td>
            <td>
                <?= $subject->subject_name; ?>
            </td>
            <td class="center">
                <?
                    $total_acload = $subject->total_acload;
                    $c = round($subject->credit) . '';
                ?>
                <?= sprintf('%s / %s', $total_acload, $c) ?>
            </td>
            <td class="center">

                    <?php
                        $rating = $subject->total_acload ? round($subject->total_point * 100 / $subject->total_acload) : 0;
                        $g = round($subject->grade) . '';
                    ?>
                    <?php if($marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                        <?= sprintf('%s', $g) ?>
                    <?php else: ?>
                        <?= sprintf('%s / %s', $rating, $g) ?>
                    <?php endif;?>
            </td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <th colspan="4" class="center">
            <b>Qo‘shimcha o‘zlashtirilgan fanlar:</b>
        </th>
    </tr>
    <tr>
        <td class="center"></td>
        <td class="center"><? echo "&nbsp;"?> </td>
        <td class="center"> </td>
        <td class="center"></td>
    </tr>
    </tbody>
</table>

<div style="width: 60%; margin: 0 auto" class="subjects center">
    <h4><b>II. Kurs loyihasi (ishi):</b></h4>
</div>

<table class="subjects bordered" cellspacing="0">
    <thead>
    <tr>
        <td width="30px" class="center">1</td>
        <td width="425px" class="center">2</td>
        <td width="145px" class="center">3</td>
        <td width="165px" class="center">4</td>
    </tr>


    </thead>
    <tbody>

    <?php foreach ($meta->getStudentSubjectsWithAcademicInformationData(RatingGrade::RATING_GRADE_COURSE) as $subject): ?>
        <?php
        if ($subject->curriculumSubjectExamType) {
            if ($subject->curriculumSubjectExamType->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                $exams[] = $subject;
                // continue;
            }
        }
        $rating = 0;
        $i++;
        ?>

        <tr>
            <td class="center"><?= $i ?></td>
            <td>
                <?= $subject->subject_name; ?>
            </td>
            <td class="center">
                <?
                $total_acload = $subject->total_acload;
                $c = round($subject->credit) . '';
                ?>
                <?= sprintf('%s / %s', $total_acload, $c) ?>
            </td>
            <td class="center">

                <?php
               // $rating = $subject->total_acload ? round($subject->total_point * 100 / $subject->total_acload) : 0;
                $g = round($subject->grade) . '';
                ?>
                <?php if($marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                    <?= sprintf('%s', $g) ?>
                <?php else: ?>
                    <?= sprintf('%s / %s ', round($subject->total_point), $g) ?>
                <?php endif;?>

            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>


<div style="width: 60%; margin: 0 auto" class="subjects center">
    <h4><b>III. Malakaviy amaliyotlar:</b></h4>
</div>

<table class="subjects bordered" cellspacing="0">
    <thead>
    <tr>
        <td width="30px" class="center">1</td>
        <td width="425px" class="center">2</td>
        <td width="145px" class="center">3</td>
        <td width="165px" class="center">4</td>
    </tr>


    </thead>
    <tbody>

    <?php foreach ($meta->getStudentSubjectsWithAcademicInformationData(RatingGrade::RATING_GRADE_PRACTICUM) as $subject): ?>
        <?php
        if ($subject->curriculumSubjectExamType) {
            if ($subject->curriculumSubjectExamType->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                $exams[] = $subject;
                // continue;
            }
        }
        $rating = 0;
        $i++;
        ?>

        <tr>
            <td class="center"><?= $i ?></td>
            <td>
                <?= $subject->subject_name; ?>
            </td>
            <td class="center">
                <?
                $total_acload = $subject->total_acload;
                $c = round($subject->credit) . '';
                ?>
                <?= sprintf('%s / %s', $total_acload, $c) ?>
            </td>
            <td class="center">

                <?php
                $rating = $subject->total_acload ? round($subject->total_point * 100 / $subject->total_acload) : 0;
                $g = round($subject->grade) . '';
                ?>
                <?php if($marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                    <?= sprintf('%s', $g) ?>
                <?php else: ?>
                    <?= sprintf('%s / %s', $rating, $g) ?>
                <?php endif;?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>


<div style="width: 60%; margin: 0 auto" class="subjects center">
    <h4><b>IV. Davlat va yakuniy attestatsiyalar:</b></h4>
</div>

<table class="subjects bordered" cellspacing="0">
    <thead>
    <tr>
        <td width="30px" class="center">1</td>
        <td width="425px" class="center">2</td>
        <td width="145px" class="center">3</td>
        <td width="165px" class="center">4</td>
    </tr>


    </thead>
    <tbody>

    <?php foreach ($meta->getStudentSubjectsWithAcademicInformationData(RatingGrade::RATING_GRADE_STATE) as $subject): ?>
        <?php
        if ($subject->curriculumSubjectExamType) {
            if ($subject->curriculumSubjectExamType->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                $exams[] = $subject;
                // continue;
            }
        }
        $rating = 0;
        $i++;
        ?>

        <tr>
            <td class="center"><?= $i ?></td>
            <td>
                <?= $subject->subject_name; ?>
            </td>
            <td class="center">
                <?
                    $total_acload = $subject->total_acload;
                    $c = round($subject->credit) . '';
                ?>
                <?= sprintf('%s / %s', $total_acload, $c) ?>
            </td>
            <td class="center">

                <?php
                //$rating = $subject->total_acload ? round($subject->total_point * 100 / $subject->total_acload) : 0;
                $g = round($subject->grade) . '';
                ?>

                <?php if($marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                    <?= sprintf('%s', $g) ?>
                <?php else: ?>
                    <?= sprintf('%s / %s', round($subject->total_point), $g) ?>
                <?php endif;?>

            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

<br>

<table style="width: 100%;margin-left: -8px" cellspacing="8" class="subjects center">
    <tr>
        <td rowspan="5" style="text-align:center;" width="20%">M.O‘.
        </td>
        <td style="text-align:left;" width="44%">Rektor:
        </td>
        <td style="text-align: left;" width="44%">
            <?= $model->rector_fullname;?>
            <br>
        </td>
    </tr>
    <tr>
        <td colspan="3">

        </td>
    </tr>
    <tr>
        <td style="text-align: left;">Dekan:
            <br>
        </td>
        <td style="text-align: left;"><?= $model->dean_fullname;?>
        </td>
    </tr>
    <tr>
        <td colspan="3">

        </td>
    </tr>
    <tr>
        <td style="text-align: left;">Kotib(a):
        </td>
        <td style="text-align: left;"><?= $model->secretary_fullname;?>
        </td>
    </tr>
</table>
