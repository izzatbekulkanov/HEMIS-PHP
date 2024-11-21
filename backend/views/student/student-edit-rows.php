<?php

use backend\widgets\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

?>
<div id="modalHeader" class="modal-header" style="opacity:1.00;">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= __('Ushbu talaba uchun quyidagi yozuvlar mavjud') ?></h4>
</div>
<div class="modal-body" style="padding: 15px 0">
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'layout' => '{items}',
            'dataProvider' => new \yii\data\ArrayDataProvider(['models' => $students]),
            'columns' => [
                [
                    'class' => SerialColumn::class,
                ],
                [
                    'attribute' => 'second_name',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->getFullName(), $data->student_id_number), ['student/student-edit', 'id' => $data->id], ['data-pjax' => 0, 'target' => '_blank']);
                    },
                ],
                [
                    'attribute' => 'passport_number',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->passport_number, $data->passport_pin);
                    },
                ],
                [
                    'attribute' => 'year_of_enter',
                    'format' => 'raw',
                    'value' => function (\common\models\student\EStudent $data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->year_of_enter, $data->meta && $data->meta->studentStatus ? $data->meta->studentStatus->name : '');
                    },
                ],
                [
                    'header' => __('Education Type'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->meta && $data->meta->educationType ? $data->meta->educationType->name : '', $data->meta && $data->meta->educationForm ? $data->meta->educationForm->name : '');
                    },
                ],
                [
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a(__("Ma'lumotlarni olish"), linkTo(['student/student-edit', 'from' => $data->id]), ['data-pjax' => 0, 'class' => 'btn btn-default btn-flat']);
                    },
                ],
            ],
        ]
    ); ?>
</div>