<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use yii\grid\SerialColumn;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Teacher professional development monitoring');
$this->params['breadcrumbs'][] = [
    'url' => ['employee/professional-development'],
    'label' => __('Teacher professional development'),
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6">

            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_faculty')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Faculty-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_department')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EDepartment::getDepartments(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Department-'),
                    ]
                )->label(false); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    '__class' => SerialColumn::class,
                ],
                [
                    'attribute' => 'employee.fullName',
                    'header' => __('Employee'),
                    'format' => 'raw',
                    'contentOptions' => [
                        'class' => 'nowrap',
                    ],
                    'value' => function ($data) {
                        return $data->employee->fullName;
                    },
                ],
                [
                    'attribute' => 'employee._department',
                    'header' => __('Structure Department'),
                    'value' => function ($data) {
                        if ($data->department !== null) {
                            return $data->department->name ?? '-';
                        }
                        return '-';
                    },
                ],
                [
                    'attribute' => 'employeeDevelopment._employee_position',
                    'value' => function ($data) {
                        if ($data->employeeDevelopment) {
                            return $data->employeeDevelopment->employeePosition->name;
                        }
                        return $data->staffPosition->name;
                    },
                ],
                [
                    'attribute' => 'employeeDevelopment._training_place',
                    'value' => 'employeeDevelopment.trainingPlace.name',
                ],
                [
                    'attribute' => 'employeeDevelopment.training_duration',
                    'header' => __('Training Period'),
                    'value' => function ($data) {
                        if ($data->employeeDevelopment === null) {
                            return '-';
                        }
                        return Yii::$app->formatter->asDate($data->employeeDevelopment->begin_date)
                            . '/' . Yii::$app->formatter->asDate($data->employeeDevelopment->end_date);
                    },
                ],
                [
                    'attribute' => 'employeeDevelopment.training_year',
                ],
                [
                    'attribute' => 'months',
                    'header' => __('Muddat'),
                    'value' => function ($data) {
                        return __('{months} month', ['months' => $data->months]);
                    },
                ],

            ],
        ]
    ); ?>
</div>
<?php
$this->registerJs(
    '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
)
?>

<?php Pjax::end() ?>
