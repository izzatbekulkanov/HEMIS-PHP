<?php

use common\models\structure\EDepartment;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-5">
                <div class="form-group">
                    <?php if ($type == 'teacher'): ?>
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getDepartments(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => ['placeholder' => __('Choose Department')],
                        ])->label(false); ?>
                    <?php else: ?>
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getDirections(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => ['placeholder' => __('Choose Direction')],
                        ])->label(false); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col col-md-5">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Employee ID')])->label(false) ?>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Import Staff'),
                        ['employee/' . $type, 'download' => 1],
                        ['class' => 'btn btn-flat btn-success  btn-block', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => '_employee',
                'format' => 'raw',
                'value' => function ($data) use ($type) {
                    return Html::a(@$data->employee->fullName, ["employee/$type", 'id' => $data->id, 'edit' => 1], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_department',
                'value' => 'department.name',
                'header' => __('Direction'),
                'visible' => $type != 'teacher'
            ],

            [
                'attribute' => '_department',
                'value' => 'department.name',
                'header' => __('Structure Department'),
                'visible' => $type == 'teacher'
            ],
            [
                'attribute' => '_position',
                'value' => 'staffPosition.name',
            ],
            [
                'attribute' => '_employment_staff',
                'value' => 'employmentStaff.name',
            ],
            [
                'attribute' => '_employee_status',
                'value' => 'employeeStatus.name',
            ],
            [
                'attribute' => 'decree_number',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->decree_number, Yii::$app->formatter->asDate($data->decree_date, 'php:d.m.Y'));
                },

            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
