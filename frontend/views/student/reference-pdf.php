<?php

use backend\assets\BackendAsset;
use backend\widgets\GridView;
use common\components\Config;
use common\models\curriculum\MarkingSystem;
use Da\QrCode\QrCode;
use yii\helpers\Html;
/* @var $this \backend\components\View */
/* @var $model \common\models\archive\EStudentReference */
?>
<style>
    *{
        font-family: "Times New Roman" !important;
    }
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
<div class="box-body" style="width: 15%; margin: 0 auto">
    <?
        if ($file = Config::get(Config::CONFIG_SYS_UI_LOGO)) {
            $filePath = Yii::getAlias('@static/uploads/') . $file['path'];
            if (file_exists($filePath)) {
                echo Html::img($filePath, ['class'=>'logo']);
            }
        }
        else{
            $filePath = Yii::getAlias('@backend/assets/app/') . 'img/gerb.png';
            if (file_exists($filePath)) {
                echo Html::img($filePath, ['class'=>'logo']);
            }
        }
    ?>
    <?  /* <img class='logo' src="<?= $this->getSystemLogo() ?>"> */?>
</div>
<div style="width: 60%; margin: 0 auto">
    <h3>
        <?= mb_strtoupper($model->university_name);?>
    </h3>

</div>
<div style=" margin: 0 auto">
    <p>
        № <?= $model->reference_number;?> <br>
        Hujjat yaratilgan sana: <?= Yii::$app->formatter->asDate($model->reference_date, 'php:d.m.Y');?>
    </p>
</div>

<div style="width: 60%; margin: 0 auto">
    <h3>
        O‘QISH JOYIDAN MA’LUMOTNOMA <br> СПРАВКА С МЕСТА УЧЕБЫ
    </h3>

</div>

<table class="bordered" cellspacing="0" cellpadding="5" style="width: 98%;margin-left: 0px">
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
        <td width="50%" style="text-align: left;"><?= $model->citizenship_name;?></td>
    </tr>
    <tr>
        <td width="50%" style="text-align: left;">Ta’lim turi / Вид образования:</td>
        <td width="50%" style="text-align: left;"><?= $model->education_type_name;?></td>
    </tr>
    <tr>
        <td width="50%" style="text-align: left;" >Ta’lim shakli / Форма обучения:</td>
        <td width="50%" style="text-align: left;" ><?= $model->education_form_name;?></td>
    </tr>
    <tr>
        <td width="50%" style="text-align: left;">Qabul turi / Тип приёма:</td>
        <td width="50%" style="text-align: left;"><?= $model->payment_form_name;?></td>
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
        <td width="50%" style="text-align: left;"><?= $model->department_name;?></td>
    </tr>
    <tr>
        <td width="50%" style="text-align: left;">Yo‘nalish / Направление:</td>
        <td width="50%" style="text-align: left;"><?= $model->specialty_name;?></td>
    </tr>
    <tr>
        <td width="50%" style="text-align: left;">O‘quv kursi / Курс обучения:</td>
        <td width="50%" style="text-align: left;"><?= $model->level_name;?></td>
    </tr>
</table>
<div style=" margin: 0 auto">
    <p>
        Ma’lumot so‘ralgan joyga taqdim etish uchun berildi. <br>
        Справка выдано для представления по месту требования

    </p>
</div>


<div style="width: 20%; position: absolute; right: 65px;">
    <p style="text-align:right; position: relative !important; " >
        <?php
        $qrCode = (new QrCode(
            Yii::getAlias('@frontendUrl').'/api/info/reference?'.'param='. $model->hash
        ))
            ->setSize(100)
            ->setMargin(2)
            ->writeDataUri();
        echo Html::img($qrCode);
        ?>

    </p>
</div>

