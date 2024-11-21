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
use common\models\system\classifier\StipendRate;

$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_stipend_rate')->widget(
                            Select2Default::classname(),

                            [
                                'data' => StipendRate::getClassifierOptions(),
                                'allowClear' => true,
                                'options' => [
                                    'id' => '_stipend_rate_search'
                                ],

                            ]
                        )->label(false); ?>
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
                            'attribute' => '_stipend_rate',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->stipendRate->name, ['finance/scholarship-amount', 'code' => $data->id], []);
                            },
                        ],
                        [
                            'attribute' => 'stipend_value',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a(Yii::$app->formatter->asCurrency($data->stipend_value), ['finance/scholarship-amount', 'code' => $data->id], []);
                            },
                        ],
                        [
                            'attribute' => 'begin_date',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDate($data->begin_date->getTimestamp());
                            },
                        ],
                        [
                            'attribute' => 'updated_at',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
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
                <?= $form->field($model, '_stipend_rate')->widget(Select2Default::classname(), [
                    'data' => StipendRate::getClassifierOptions(),
                    'allowClear' => true,
                    'options' => [
                        'placeholder' => true
                    ],
                ]) ?>
                <?= $form->field($model, 'stipend_value')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'begin_date')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                        'id' => 'begin_date',
                    ],
                ]); ?>
                <?//= $form->field($model, 'current_status')->checkbox(['class' => 'icheckbox']) ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['finance/scholarship-amount'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['finance/scholarship-amount', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
