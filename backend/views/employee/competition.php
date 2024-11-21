<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Teacher competition');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create'),
                        ['employee/competition-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                    <?= $this->getResourceLink(
                        '<i class="fa fa-"></i> ' . __('Monitoring'),
                        ['employee/competition-monitoring'],
                        ['class' => 'btn btn-flat btn-info ', 'data-pjax' => 0]
                    ) ?>
                </div>
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
            <div class="col col-md-3">
                <?= $form->field($searchModel, 'election_date')->widget(
                    Select2Default::classname(),
                    [
                        'data' => ArrayHelper::map($dataProvider->getModels(), function ($m) {return $m->election_date->format('Y');}, function ($m) {return $m->election_date->format('Y');}),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Election date-'),
                    ]
                )->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'toggleAttribute' => 'active',
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    '__class' => SerialColumn::class,
                ],
                [
                    'attribute' => 'employee.name',
                    'header' => __('Employee'),
                    'format' => 'raw',
                    'contentOptions' => [
                        'class' => 'nowrap',
                    ],
                    'value' => function ($data) {
                        return Html::a(
                            $data->employee->fullName,
                            ['employee/competition-edit', 'id' => $data->id],
                            ['data-pjax' => 0]
                        );
                    },
                ],
                [
                    'attribute' => 'employee._department',
                    'header' => __('Structure Department'),
                    'value' => function ($data) {
                        if ($data->employeeCathedra !== null) {
                            return $data->employeeCathedra->department->name ?? '-';
                        }
                        return '-';
                    },
                ],
                [
                    'attribute' => '_employee_position',
                    'header' => __('Competition position'),
                    'value' => 'employeePosition.name',
                ],
                [
                    'attribute' => 'employee._academic_degree',
                    'value' => 'employee.academicDegree.name',
                ],
                [
                    'attribute' => 'employee._academic_rank',
                    'value' => 'employee.academicRank.name',
                ],
                [
                        'attribute' => 'election_date',
                        'format' => 'date'
                ]

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
