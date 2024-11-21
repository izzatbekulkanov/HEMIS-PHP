<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-4">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Employee'),
                        ['employee/employee-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Employee ID')])->label(false) ?>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Import Employees'),
                        ['employee/employee', 'download' => 1],
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
                'attribute' => 'employee_id_number',
            ],
            [
                'attribute' => 'second_name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->fullName, ['employee/employee', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_admin',
                'header' => __('Role'),
                'format' => 'raw',
                'value' => function (\common\models\employee\EEmployee $data) {
                    return $data->getRolesLabel();
                },
            ],
            [
                'attribute' => 'birth_date',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDate($data->birth_date, 'dd-MM-Y');
                },
            ],
            [
                'attribute' => 'passport_number',
            ],

            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],
            [
                'attribute' => 'id',
                'header' => __('Fixed positions'),
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(count($data->employeeMeta), ['employee/employee', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
<?php Pjax::end() ?>
