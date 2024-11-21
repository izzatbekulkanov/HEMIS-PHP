<?php

use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\RatingGrade;
use common\models\student\EStudentMeta;
use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\helpers\Html;
use common\models\archive\EStudentDiploma;

$subjects = EStudentMeta::getStudentSubjects($selected->student->meta)->andWhere(
    ['in', 'e_curriculum_subject._rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]]
)->all();

$totalBall = array_reduce($subjects, function ($acc, ECurriculumSubject $curriculumSubject) use ($selected) {
    $r = $curriculumSubject->getStudentSubjectRecord($selected->_student);
    return $acc + ($r ? $r->total_point : 0);
}, 0);

$avgBall = $totalBall / count($subjects);

?>
<style>
    @media (min-width: 480px) {
        table th {
            width: 25%;
        }
    }
</style>
<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                <b>Bitiruvchining shaxsiy ma'lumotlari</b>
            </h4>
            <table class="table table-striped table-bordered table-responsive">
                <tbody>
                <tr>
                    <th>Familiyasi </th>
                    <td><?=@$selected->student->second_name;?></td>
                </tr>
                <tr>
                    <th>Ismi: </th>
                    <td><?=@$selected->student->first_name;?></td>
                </tr>
                <tr>
                    <th>Pasport: </th>
                    <td><?=@$selected->student->passport_number;?></td>
                </tr>
                <tr>
                    <th>Tug'ilgan sanasi: </th>
                    <td><?= Yii::$app->formatter->asDate(@$selected->student_birthday, 'php:d.m.Y');?></td>
                </tr>
                </tbody>
            </table>

        </div>
        <!-- /.col -->
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                <b>Bitiruvchi tamomlagan OTMning ma'lumotlari</b>
            </h4>
            <table class="table table-striped table-bordered table-responsive hei-info">
                <tbody>
                <tr>
                    <th>OTM nomi </th>
                    <td><?=$selected->university_name;?></td>
                </tr>
                <tr>
                    <th>Ta'lim yo'nalishi: </th>
                    <td><?=$selected->specialty_name;?></td>
                </tr>
                <tr>
                    <th>Ilmiy darajasi: </th>
                    <td><?=$selected->education_type_name;?></td>
                </tr>
                <tr>
                    <th>Ta’lim shakli: </th>
                    <td><?=$selected->education_form_name;?></td>
                </tr>
                <tr>
                    <th>Diplom raqami: </th>
                    <td><?=$selected->diploma_number;?></td>
                </tr>
                <tr>
                    <th>Diplom berilgan sana: </th>
                    <td><?= Yii::$app->formatter->asDate($selected->register_date, 'php:d.m.Y');?></td>
                </tr>
                <tr>
                    <th>O'rtacha bali: </th>
                    <td><?= sprintf('%.2f', $avgBall) ?></td>
                </tr>
                <tr>
                    <th>Diplom turi: </th>
                    <td><?= $selected->categoryLabel;?></td>
                </tr>
                <tr>
                    <th>O’qishga kirgan yili: </th>
                    <td><?=$selected->student->year_of_enter;?></td>
                </tr>
                <tr>
                    <th>O’qish muddati: </th>
                    <td><?=$selected->education_period;?></td>
                </tr>
                </tbody>
            </table>

        </div>
        <!-- /.col -->
    </div>

</section>