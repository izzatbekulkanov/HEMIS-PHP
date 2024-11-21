<?php

use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\RatingGrade;
use common\models\student\EStudentMeta;
use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\helpers\Html;
use common\models\archive\EStudentDiploma;


?>
<style>
    @media (min-width: 480px) {
        table th {
            width: 25%;
        }
    }
</style>
<section class="invoice">

    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                <b><?= $model->university_name;?></b>
            </h4>
            <p>
                № <?= $model->reference_number;?> <br>
                Hujjat yaratilgan sana: <?= Yii::$app->formatter->asDate($model->reference_date, 'php:d.m.Y');?>
            </p>

        </div>
        <!-- /.col -->
    </div>
    <div class="clearfix"></div>

    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                <b>O‘QISH JOYIDAN MA’LUMOTNOMA <br> СПРАВКА С МЕСТА УЧЕБЫ</b>
            </h4>

            <table class="table table-striped table-bordered table-responsive">
                <tr>
                    <td width="50%" style="text-align: left;">F.I.SH. / Ф.И.О.:</td>
                    <td width="50%" style="text-align: left;"><?=$model->getFullName();?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">JSH SHIR / ПИН ФЛ:</td>
                    <td width="50%" style="text-align: left;"><?=$model->passport_pin;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">Tug‘ilgan sanasi / Дата рождения:</td>
                    <td width="50%" style="text-align: left;"><?= Yii::$app->formatter->asDate($model->birth_date, 'php:d.m.Y');?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">Fuqaroligi / Гражданство:</td>
                    <td width="50%" style="text-align: left;"><?= $model->citizenship->name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">Ta’lim turi / Вид образования:</td>
                    <td width="50%" style="text-align: left;"><?= $model->educationType->name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;" >Ta’lim shakli / Форма обучения:</td>
                    <td width="50%" style="text-align: left;" ><?= $model->educationForm->name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">Qabul turi / Тип приёма:</td>
                    <td width="50%" style="text-align: left;"><?= $model->paymentForm->name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">O‘qishga kirgan yili / Год зачисления:</td>
                    <td width="50%" style="text-align: left;"><?= $model->year_of_enter;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;" >Oliy ta’lim muassasasi / Высшее образовательное учреждение:</td>
                    <td width="50%" style="text-align: left;"><?= $model->university_name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">Fakultet / Факультет:</td>
                    <td width="50%" style="text-align: left;"><?= $model->department->name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">Yo‘nalish / Направление:</td>
                    <td width="50%" style="text-align: left;"><?= $model->specialty->name;?></td>
                </tr>
                <tr>
                    <td width="50%" style="text-align: left;">O‘quv kursi / Курс обучения:</td>
                    <td width="50%" style="text-align: left;"><?= $model->level->name;?></td>
                </tr>
            </table>


        </div>
        <!-- /.col -->
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-xs-12">

            <p>
                Ma’lumot so‘ralgan joyga taqdim etish uchun berildi. <br>
                Справка выдано для представления по месту требования
            </p>

        </div>
        <!-- /.col -->
    </div>


</section>