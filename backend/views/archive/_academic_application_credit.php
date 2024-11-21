

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

$student_full_name = $student->student->fullName;
?>
<?php if(!isset($_GET['ready-pdf'])):?>
    <?php
    $this->params['breadcrumbs'][] = ['url' => ['archive/academic-information'], 'label' => __('Academic Information')];
    $this->params['breadcrumbs'][] = $this->title;
    ?>
<?php endif;?>
<style>
    <?//=$this->renderFile('@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css'); ?>
    <?php if(isset($_GET['ready'])):?>
        <?//=$this->renderFile('@app/assets/app/css/diploma-application.css');?>
    <?php endif;?>
</style>
<body>
<section class="invoice">
    <div class="p1-heading text-center"><b>Transkript</b></div>
    <br>

<div class="p3-table-box">
    <table class="table table-bordered">
        <tr>
            <td style="width:30%; text-align: left;">F.I.SH / Full name:</td>
            <td><?= $student_full_name ?></td>
         </tr>
        <tr>
            <td style="text-align: left;">Tugilgan sanasi / Date of birth:</td>
            <td><?= Yii::$app->formatter->asDate($student->student->birth_date, 'dd.MM.Y') ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">Akademik daraja / Degree Course:</td>
            <td><?=$student->educationType->name ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">Fakultet / College:</td>
            <td><?=$student->department->name ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">Yo'nalish / Department:</td>
            <td><?= $student->specialty->mainSpecialty ? $student->specialty->mainSpecialty->name : $student->specialty->name ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">O'qishga qabul qilingan sanasi / Date of admission:</td>
            <td><?=$student->student->year_of_enter ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">Ta'lim oluvchining maqomi / Student status:</td>
            <td><?=$student->studentStatus->name ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">O'qishni tamomlagan sanasi / Date of graduation:</td>
            <td><? ?></td>
        </tr>

    </table>
</div>

<div class="p5-table-box">
    <table class="table table-bordered">
        <tr>
            <td>T/r</td>
            <td>Fan kodi / Fan nomi / Subject</td>
            <td>Kreditlar / Credits/ ECTS da ifodalanishi</td>
            <td>Ball/Grade</td>
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
    <?php if(!isset($_GET['ready'])):?>
        <div class="row no-print">
            <div class="col-xs-12">

                <?=
                Html::a(__('Generate PDF'),
                    [
                        'archive/academic-information',
                        'code' => $student->id,
                         'generate-pdf' => 1,
                         'ready' => 1,
                    ], ['data-pjax' => 0, 'class'=>'btn btn-primary pull-left', 'style'=>'margin: 0 5px']);
                ?>
                <? /*if ($selected->filename) { ?>
                <a class="download-item"
                   href="<?= Url::current(['download' => 1]) ?>">
                    <i class="fa fa-paperclip "></i> <?= $selected->filename['name']; ?>
                </a>
                <?php
            }*/
                ?>
            </div>
        </div>
    <?php endif;?>
</section>
</body>