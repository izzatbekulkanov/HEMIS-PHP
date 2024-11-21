<?php

use common\models\finance\EStudentContract;
use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use common\models\archive\EStudentDiploma;

$this->addBodyClass('login-page');
$this->title = __('Find Diploma');
?>

<!-- title row -->
<div class="col col-sm-1"></div>
<div class="col col-sm-10">
    <div class="login-bg">
        <div class="login-box" style="width: auto;max-width:none">
            <div class="box box-default">
                <div class="box-body no-padding">
                    <?= GridView::widget(
                        [
                            'id' => 'data-grid',
                            'layout' => '{items}',
                            'dataProvider' => new \yii\data\ArrayDataProvider(['models' => $contracts]),
                            'columns' => [
                                [
                                    'class' => SerialColumn::class,
                                ],

                                [
                                    'attribute' => '_student',
                                    'format' => 'raw',
                                    'value' => function (EStudentContract $data) {
                                        return sprintf("%s<p class='text-muted'> %s</p>", $data->student->getFullName(), $data->student->student_id_number);
                                    },
                                ],
                                [
                                    'attribute' => '_education_year',
                                    'format' => 'raw',
                                    'value' => function (EStudentContract $data) {
                                        return sprintf("%s<p class='text-muted'> %s</p>", $data->educationYear->name, @$data->level->name);
                                    },
                                ],
                                [
                                    'attribute' => 'number',
                                    'format' => 'raw',
                                    'value' => function (EStudentContract $data) {
                                        return sprintf("%s<p class='text-muted'> %s</p>", $data->number, Yii::$app->formatter->asDate($data->date, 'php:d.m.Y'));
                                    },
                                ],
                                [
                                    'attribute' => 'summa',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return sprintf("%s<p class='text-muted'> %s / %s </p>", $data->summa !== null ? Yii::$app->formatter->asCurrency($data->summa) : '-', Yii::$app->formatter->asDecimal(@$data->discount, 0) . '%', @$data->month_count);
                                    },
                                ],
                                [
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Html::a(__('Download'), currentTo(['c' => $data->hash]), ['class' => 'btn btn-primary pull-right']);
                                    },
                                ],
                            ],
                        ]
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

