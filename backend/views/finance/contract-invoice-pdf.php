<?php

use backend\widgets\GridView;
use common\components\Config;
use common\models\curriculum\MarkingSystem;
use Da\QrCode\QrCode;
use yii\helpers\Html;
/* @var $this \backend\components\View */
/* @var $model \common\models\finance\EStudentContractInvoice */
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
    .rotation
    {
        -webkit-transform: rotate(-90deg) !important;
        -moz-transform: rotate(-90deg) !important;

        width:10px !important;
    }
    .invoice-custom{
        font-size: 9pt !important;
    }
    .sign{
        font-size: 7pt;
        font-weight: normal !important;
        text-align: center !important;
        margin-left: 50px !important;
        position: relative;
        left:100px;
        font-family: "Times New Roman";

    }
    address{
        margin-bottom: 4px !important;
        font-family: "Times New Roman" !important;
    }
    .bank_detail {
        background-color: white !important;
        font-family: "Times New Roman" !important;
        font-size: 14px !important;
        padding: 0 !important;
        border: 0;
        overflow: visible;
    }
</style>
<div style="width: 60%; margin: 0 auto; ">

    <h4>
        <?= Yii::$app->formatter->asDate($model->studentContract->date, 'php:Y');?> yil
        <?= Yii::$app->formatter->asDate($model->studentContract->date, 'php:d');?>
        <?= Yii::$app->formatter->asDate($model->studentContract->date, 'php:F');?>dagi
        № <?= $model->studentContract->number;?>-sonli shartnomaga
    </h4>
    <h4>HISOB-FAKTURA</h4>
    <h4>
        <?= Yii::$app->formatter->asDate($model->invoice_date, 'php:Y');?> yil
        <?= Yii::$app->formatter->asDate($model->invoice_date, 'php:d');?>
        <?= Yii::$app->formatter->asDate($model->invoice_date, 'php:F');?>
        № <?= $model->invoice_number;?>
    </h4>





</div>
<div style="width: 20%; position: absolute; top: 50px; right: 65px;">
<p style="text-align:right; position: relative !important; " >
    <?php
    $qrCode = (new QrCode(
        Yii::getAlias('@frontendUrl').'/api/info/invoice?'.'param='. $model->hash
    ))
        ->setSize(100)
        ->setMargin(2)
        ->writeDataUri();
    echo Html::img($qrCode);
    ?>

</p>
</div>

<table style="width: 100%;margin-left: -8px" cellspacing="8">
<tr>
    <th>YETKAZIB BERUVCHI</th>
    <th></th>
    <th>HARIDOR</th>
</tr>

    <tr>
        <td width="48%">
            <div class="col-sm-9 invoice-col">

                <address>
                    <b><?= $univer->name;?></b>
                </address>
                <br>
                <b>Pochta manzili: </b>
                <address>
                    <pre class ="bank_detail" ><?= $model->studentContract->mailing_address;?></pre>

                </address>
                <br>
                <b>To‘lov oluvchining rekvizitlari: </b>
                <address>
                    <pre class ="bank_detail" ><?= $model->studentContract->bank_details;?></pre>
                </address>
            </div>
        </td>
        <td width="2%">
        </td>
        <td style="vertical-align: top;">
            <div class="col-sm-6 invoice-col">

                <address>
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                    _________________________________________________
                </address>
            </div>
        </td>
    </tr>
</table>
<br>
<br>
<table class="bordered" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th width="15%" style="text-align: center;" rowspan="2">Tovarning nomi
            (ish, xizmat)
        </th>
        <th  style="text-align: center;" rowspan="2"><div class="rotation">O‘lchov birligi</div></th>
        <th  style="text-align: center;" class="rotation" rowspan="2"><div class="rotation">Soni</div></th>
        <th width="20%" style="text-align: center;" rowspan="2">Narxi</th>
        <th width="15%" style="text-align: center;" rowspan="2">Yetkazib berish bahosi</th>
        <th  style="text-align: center;" colspan="2">Aksiz solig‘i</th>
        <th style="text-align: center;" colspan="2">QQS</th>
        <th width="15%" style="text-align: center;" rowspan="2">AS va QQS bilan hisoblanganda yetkazib berish bahosi</th>
    </tr>
    <tr>
        <th  style="text-align: center;" class="rotation"><div class="rotation">Stavka</div></th>
        <th  style="text-align: center;" class="rotation"><div class="rotation">Summa</div></th>
        <th  style="text-align: center;" class="rotation"><div class="rotation">Stavka</div></th>
        <th style="text-align: center;" class="rotation"><div class="rotation">Summa</div></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th width="10px" style="text-align: center;">Mutaxassis tayyorlab berish</th>
        <th width="" style="text-align: center;">nafar</th>
        <th width="" style="text-align: center;">1</th>
        <th width="" style="text-align: center;"><?= Yii::$app->formatter->asCurrency($model->invoice_summa)?></th>
        <th width="" style="text-align: center;"></th>
        <th width="" style="text-align: center;"></th>
        <th width="" style="text-align: center;"></th>
        <th width="" style="text-align: center;"></th>
        <th width="" style="text-align: center;"></th>
        <th width="" style="text-align: center;"></th>
    </tr>
    </tbody>
</table>
<br>
<table class="bordered" cellspacing="0" cellpadding="8">
    <tr>
        <td width="18%" style="border-right: 0px;">Summa so‘z bilan:</td>
        <td width="85%">_________________________________________________________________________________________________________so‘m.</td>
    </tr>
</table>

<br>

<table class="invoice-custom" style="width: 96%;margin: 0 auto"  cellspacing="0" cellpadding="4">
    <tr>
        <td width="42%">
            Rektor ______________________________________
            <pre class="sign">                                           <i>(imzo)</i></pre>
        </td>
        <td width="3%"></td>
        <td width="50%">
            Qabul qilib oldim ______________________________________
            <pre class="sign">                                                     <i>(haridor imzosi)</i></pre>

        </td>
    </tr>

    <tr>
        <td>
            M.O‘.

        </td>
        <td></td>
        <td>

        </td>
    </tr>

    <tr>
        <td>
            Bosh hisobchi ______________________________
            <pre class="sign">                                           <i>(imzo)</i></pre>
        </td>
        <td></td>
        <td>
            «_____» ______________ 20____ yildagi
        </td>
    </tr>
    <tr>
        <td>

        </td>
        <td></td>
        <td>
            № __________ sonli ishonchnoma bo‘yicha
        </td>
    </tr>
    <tr>
        <td>

        </td>
        <td></td>
        <td>

        </td>
    </tr>

    <tr>
        <td>
            Topshirdi  __________________________________
            <pre class="sign">                                             <i>(imzo)</i></pre>
        </td>
        <td></td>
        <td>
            ________________________________________________________
            <pre class="sign">                                  <i>(Qabul qilib oluvchining F.I.Sh.)</i></pre>

        </td>
    </tr>


</table>



