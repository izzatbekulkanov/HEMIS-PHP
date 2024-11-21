<?php

use backend\widgets\GridView;
use common\components\Config;
use common\models\curriculum\MarkingSystem;

/* @var $this \backend\components\View */
/* @var $model EStudentPtt */

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

    <h4>DARS JADVALI <br><?= $model->curriculum->specialty->fullName;?></h4>
</div>
<table style="width: 100%;margin-left: -8px" cellspacing="8">
    <tr>
        <td><b>Ta’lim turi: </b> <?= $model->curriculum->educationType->name; ?></td>
        <td><b>Ta’lim shakli: </b> <?= $model->curriculum->educationForm->name ?></td>
        <td><b>Guruh: </b> <?= $group->name ?></td>
    </tr>
    <tr>
        <td><b>O‘quv yili: </b> <?= $semester->educationYear->name; ?></td>
        <td><b>Kurs: </b> <?= $semester->level->name; ?></td>
        <td><b>Semestr: </b> <?= $semester->name ?></td>
    </tr>
    <tr>
        <td></td>
        <td><b>Hafta: </b> <?= Yii::$app->formatter->asDate($model->start_date).' - '.Yii::$app->formatter->asDate($model->end_date) ?></td>
        <td></td>
    </tr>

</table>
<br>

<table class="bordered" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th width="10px" style="text-align: center;"><?= __('T/r') ?></th>
        <th width="12%" style="text-align: center;">Hafta kuni</th>
        <th width="17%" style="text-align: center;">Juftlik</th>
        <th width="" style="text-align: center;">Dars ma’lumotlari<br>
        <th width="12%" style="text-align: center;">Aud.</th>
    </tr>
    </thead>
    <tbody>
    <?php
        $rows_0 = "";
        $rows = "";
        $rows_1 = "";
        $i = 1;
    ?>
    <?php foreach ($query as $key => $item): ?>
            <tr>
                <?php
                if($rows_0 != Yii::$app->formatter->asDate($item->lesson_date)){
                    echo '<td' .($dates[Yii::$app->formatter->asDate($item->lesson_date)] > 1 ? ' rowspan="' .($dates[Yii::$app->formatter->asDate($item->lesson_date)]).'" style="vertical-align: middle;">':'>') .$i.'</td>';
                    $rows_0 = Yii::$app->formatter->asDate($item->lesson_date);
                    $i++;
                }
                ?>
                <?php
                if($rows_1 != Yii::$app->formatter->asDate($item->lesson_date)){
                    echo '<td' .($dates[Yii::$app->formatter->asDate($item->lesson_date)] > 1 ? ' rowspan="' .($dates[Yii::$app->formatter->asDate($item->lesson_date)]).'" style="vertical-align: middle;">':'>') .ucwords(Yii::$app->formatter->asDate($item->lesson_date, 'php:l')).'</td>';
                    $rows_1 = Yii::$app->formatter->asDate($item->lesson_date);

                }
                ?>
                <td><?php echo $item->lessonPair->name.' ('.$item->lessonPair->period.')';?></td>
                <td><?php echo $item->trainingType->name.': '.@$item->subject->name. '<br> O‘qituvchi: '.@$item->employee->shortName;?></td>
                <td><?php echo @$item->auditorium->name . '<br>' . @$item->additional;?></td>
            </tr>
    <?php endforeach; ?>

    </tbody>
</table>



