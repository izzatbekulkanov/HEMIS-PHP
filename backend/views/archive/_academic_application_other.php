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


    <?= $this->render('_academic_application_other_blank', [
        'model' => $model,
        'student' => $student,
    ]); ?>

    <div class="p3-student-full-name text-center"><?= $student_full_name ?></div>
    <div class="p3-student-fullname-line"></div>
    <div class="p3-student-fullname-tip text-center" style="border-top: 1px dotted #ccc;">(Familiyasi, ismi, оtasining ismi)</div>

    <div class="p5-table-box">
        <table class="table table-bordered">
            <tr>
                <td>T/r</td>
                <td>О‘zlashtirgan fanlar nomi</td>
                <td>О‘quv rejasida belgilangan soatlar miqdori</td>
                <td>О‘zlashtirish ko‘rsatkichi (reyting/baho/kredit)</td>
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