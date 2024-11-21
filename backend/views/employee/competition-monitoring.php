<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Teacher competition monitoring');
$this->params['breadcrumbs'][] = [
    'url' => ['employee/competition'],
    'label' => __('Teacher competition'),
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
                    'attribute' => 'employee.position',
                    'value' => 'staffPosition.name',
                ],
                [
                    'attribute' => 'contract_date',
                    'header' => __('Staff Date'),
                    'format' => 'date',
                ],
                [
                    'attribute' => 'employeeCompetition._employee_position',
                    'header' => __('Competition position'),
                    'value' => 'employeeCompetition.employeePosition.name',
                ],
                [
                    'attribute' => 'employeeCompetition.election_date',
                    'header' => __('Competition date'),
                    'format' => 'date',
                ],
                [
                    'attribute' => 'position_difference',
                    'header' => __('Position difference'),
                    'value' => function ($data) {
                        return $data->employeeCompetition && $data->employeeCompetition->_employee_position === $data->staffPosition->code ? '0' : '1';
                    },
                ],
                [
                    'attribute' => 'months',
                    'header' => __('Muddat'),
                    'value' => function ($data) {
                        /*if ($data->employeeCompetition && $data->employeeCompetition->_employee_position === $data->staffPosition->code) {
                            return __('{months} month', ['months' => $data->months]);
                        }
                        $yearMonths = (date('Y') - date('Y', $data->created_at->getTimestamp())) * 12;
                        $months = (date('m') - date('m', $data->created_at->getTimestamp()));*/

                        return __('{months} month', ['months' => $data->months ?? 0]);
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
