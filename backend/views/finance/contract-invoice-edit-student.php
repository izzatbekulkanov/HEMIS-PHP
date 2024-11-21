<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use backend\widgets\DatePickerDefault;

$this->params['breadcrumbs'][] = ['url' => ['finance/contract-invoice'], 'label' => __('Finance Contract Invoice Edit')];

$this->params['breadcrumbs'][] = ['url' => ['finance/contract-invoice-edit'], 'label' => __('Finance Contract Invoice Data')];

$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h4><?= __('Student Contract Information') ?></h4>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'layout' => "<div class='box-body no-padding'>{items}</div>",
        'dataProvider' => new \yii\data\ArrayDataProvider(['models' => [isset($meta) ? $meta : $model]]),
        'columns' => [
            [
                'header' => __('Student Contract Number'),
                'format' => 'raw',
                'value' => function ($data) {
                        if(isset($data->number))
                            return sprintf("%s / %s<p class='text-muted'> %s </p>", $data->number, Yii::$app->formatter->asDate($data->date, 'php:d.m.Y'), $data->summa !==null ? Yii::$app->formatter->asCurrency($data->summa) : '-');
                        else
                            return sprintf("%s / %s<p class='text-muted'> %s </p>", $data->studentContract->number, Yii::$app->formatter->asDate($data->studentContract->date, 'php:d.m.Y'), $data->studentContract->summa !==null ? Yii::$app->formatter->asCurrency($data->studentContract->summa) : '-');
                },
            ],
            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s<p class="text-muted">%s / %s</p>', $data->student->getFullName(), $data->student->student_id_number,  $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_specialty',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->educationType->name, $data->specialty->code);
                },
            ],
            [
                'attribute' => '_group',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->group->name, $data->department->name);
                },
            ],

        ],
    ]); ?>
</div>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?//= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'toggleAttribute' => 'active',

                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'invoice_number',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->invoice_number, linkTo(['contract-invoice-edit', 'invoice' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => 'invoice_date',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDate($data->invoice_date->getTimestamp());
                            },
                        ],
                        [
                            'attribute' => 'invoice_summa',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a(Yii::$app->formatter->asCurrency($data->invoice_summa), linkTo(['contract-invoice-edit', 'invoice' => $data->id]), []);
                            },
                        ],

                        //'current_status:boolean',
                        [
                            'attribute' => 'updated_at',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            },
                        ],
                        [
                            'attribute' => 'filename',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a(
                                    '<i class="fa fa-download"></i>',//.__('Download'),
                                    linkTo(['contract-invoice-edit', 'invoice' => $data->id, 'download' => 1]),
                                    [
                                        'class' => 'btn btn-default btn-flat',
                                        'data-pjax' => 0
                                    ]);
                                },
                        ]



                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <?php //echo $form->errorSummary($model)?>
            <div class="box-body">
                <?= $form->field($model, 'invoice_number')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'invoice_date')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                        'id' => 'invoice_date',
                    ],
                ]); ?>
                <?= $form->field($model, 'invoice_summa')->textInput(['maxlength' => true]) ?>

            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['finance/contract-invoice-edit', 'contract' => $model->_student_contract], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['finance/contract-invoice-edit', 'invoice' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
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
