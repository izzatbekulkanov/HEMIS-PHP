<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\archive\EDiplomaBlank;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model EDiplomaBlank
 * @var $searchModel EDiplomaBlank
 * @var $university \common\models\structure\EUniversity
 */

$this->title = __('Diploma Blank');
$this->params['breadcrumbs'][] = $this->title;

?>
<?php
Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php
                    $form = ActiveForm::begin(); ?>
                    <div class="col col-md-1">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-rotate-right"></i>&nbsp;&nbsp;',
                                ['archive/diploma-blank', 'sync' => HEMIS_INTEGRATION ? 1 : 0],
                                [
                                    'class' => 'btn btn-flat btn-success btn-confirm',
                                    'data-pjax' => 0,
                                    'data-message' => __('Are you sure you are re-sync diploma blanks?')
                                ]
                            ) ?>
                        </div>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, 'type')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDiplomaBlank::getTypeOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, 'category')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDiplomaBlank::getCategoryOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, 'status')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDiplomaBlank::getStatusOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, 'number')->textInput(
                            ['placeholder' => __('Search by number')]
                        )->label(false); ?>
                    </div>
                    <?php
                    ActiveForm::end(); ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'sticky' => '#sidebar',
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'attribute' => 'number',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        $data->number,
                                        ['archive/diploma-blank', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                },
                            ],
                            [
                                'attribute' => 'year',
                            ],
                            [
                                'attribute' => 'type',
                                'value' => 'typeLabel',
                            ],
                            [
                                'attribute' => 'category',
                                'value' => 'categoryLabel',
                            ],
                            [
                                'attribute' => 'status',
                                'value' => 'statusLabel',
                            ],
                        ],
                    ]
                ); ?>
            </div>
        </div>
    </div>
    <?php
    if (Yii::$app->request->get('id')): ?>
        <div class="col col-md-4" id="sidebar">
            <?php
            $form2 = ActiveForm::begin(
                ['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]
            ); ?>
            <div class="box box-default ">
                <div class="box-body">
                    <?= $form2->field($model, 'number')->textInput(['disabled' => true]); ?>
                    <?= $form2->field($model, 'year')->textInput(['disabled' => true]); ?>
                    <?= $form2->field($model, 'type')->widget(
                        Select2Default::class,
                        [
                            'options' => ['id' => 'type'],
                            'data' => EDiplomaBlank::getTypeOptions(),
                            'disabled' => true
                        ]
                    ) ?>

                    <?= $form2->field($model, 'category')->widget(
                        Select2Default::class,
                        [
                            'options' => ['id' => 'category'],
                            'data' => EDiplomaBlank::getCategoryOptions(),
                            'disabled' => true
                        ]
                    ) ?>

                    <?= $form2->field($model, 'status')->widget(
                        Select2Default::class,
                        [
                            'options' => ['id' => 'status'],
                            'data' => EDiplomaBlank::getStatusOptions(),
                        ]
                    ) ?>

                    <?= $form2->field($model, 'reason')->textarea([]); ?>

                </div>

                <div class="box-footer text-right">
                    <?php $this->getResourceLink(
                        __('Cancel'),
                        ['archive/diploma-blank'],
                        ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]
                    ) ?>
                    <?= Html::submitButton(
                        '<i class="fa fa-check"></i> ' . __('Save'),
                        ['class' => 'btn btn-primary btn-flat']
                    ) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
        </div>
    <?php
    endif; ?>
</div>
<?php
Pjax::end() ?>
