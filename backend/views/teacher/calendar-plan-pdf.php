<?php

use backend\assets\BackendAsset;
use backend\widgets\GridView;
use common\components\Config;
use common\models\curriculum\MarkingSystem;
use common\models\system\classifier\TrainingType;
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

<div style="width: 60%; margin: 0 auto">
    <h4>
        <?= mb_strtoupper($univer->name);?>
    </h4>
    <h4>
        KALENDAR REJA
    </h4>
</div>
<div style=" margin: 0 auto">
    <p>
        <b>O‘quv yili:</b> <?= \common\models\curriculum\EducationYear::findOne($educationYear)->name;?>, <b>Semestr:</b> <?= $subject->semester->name;?>, <b>Guruh:</b> <?= $group->name;?>
        <br>
        <b>Mutaxassislik:</b> <?= $group->specialty->name;?><br>
        <b>Kafedra:</b> <?= $subject->department->name; ?><br>
        <b>Fan nomi:</b> <?= $subject->subject->name; ?><br>
        <b>O‘qituvchi:</b> <?= $schedule->employee->fullName ?><br>
        <b>Mashg‘ulot turi:</b> <?= TrainingType::findOne($training_type)->name;?><br>
    </p>
</div>

<table class="bordered" cellspacing="0" cellpadding="5" style="width: 98%;margin-left: 0px">
    <tr>
        <th width="10px" style="text-align: left;">№</th>
        <th width="75%" style="text-align: left;">Mavzu nomi</th>
        <th width="15px" style="text-align: center;">Soat</th>
        <th width="20px" style="text-align: center;">Sana</th>
        <th width="15px" style="text-align: center;">Belgi</th>
    </tr>
    <?php $i=1;?>
    <?php foreach ($dataProviderTopic->getModels() as $item):?>
        <tr>
            <td style="text-align: left;"><?= $i++;?></td>
            <td style="text-align: left;"><?= $item->name;?></td>
            <td style="text-align: center;">
                <?php
                   $result = 0;
                   foreach ($params['lesson_dates'] as $item2) {
                        if ($item->id == $item2->_subject_topic) {
                            if (@$params['check'][Yii::$app->formatter->asDate(@$item2->lesson_date, 'php:Y-m-d')][@$item2->_lesson_pair])
                                $result += 2;
                        }
                    }
                    if($result == 0) $result = '';
                    echo $result;
                ?>
            </td>
            <td  style="text-align: center;">
                <?php
                $dates = "";
                foreach($params['lesson_dates'] as $item2){
                    if ($item->id == $item2->_subject_topic) {
                        if (@$params['check'][Yii::$app->formatter->asDate(@$item2->lesson_date, 'php:Y-m-d')][@$item2->_lesson_pair])
                            $dates .=  Yii::$app->formatter->asDate($item2->lesson_date, 'php:d.m.Y').'<br>';
                    }
                }
                echo $dates;
                ?>
            </td>
            <td  style="text-align: center;">
                <?php
                $result = null;
                foreach ($params['lesson_dates'] as $item2) {
                    if ($item->id == $item2->_subject_topic) {
                        if (@$params['check'][Yii::$app->formatter->asDate(@$item2->lesson_date, 'php:Y-m-d')][@$item2->_lesson_pair])
                            $result = '+';
                    }
                }
                echo $result;
                ?>
            </td>
        </tr>
    <?php endforeach;?>
</table>
<br>
<table cellspacing="0" cellpadding="5" style="width: 98%;margin-left: 0px">
    <tr>
        <td width="48%">O‘qituvchi ________________________</td>
        <td>Kafedra mudiri _______________________</td>
    </tr>
</table>