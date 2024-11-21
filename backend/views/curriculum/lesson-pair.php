<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\field\FieldRange;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\curriculum\LessonPair
 * @var $university \common\models\curriculum\EducationYear
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <?php
                echo $form->errorSummary($model);
                ?>
                <?php if($model->isNewRecord) {
                    if($searchModel->_education_year)
                        $model->_education_year = $searchModel->_education_year;
                }?>
                <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => false,
                ]) ?>

                <?= $form->field($model, 'name')->textInput() ?>
                <?php
                echo FieldRange::widget([
                    //  'form' => $form,
                    'model' => $model,
                    'label' => __('Enter time range'),
                    'attribute1' => 'start_time',
                    'attribute2' => 'end_time',
                    'type' => FieldRange::INPUT_TIME,
                    'widgetOptions1' => [
                        //'size' => 'sm',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'showMeridian' => false,
                            'minuteStep' => 5,
                        ],
                        'options'=>[
                            'readonly'=> true,
                            'value' => ($model->isNewRecord) ? null : $model->start_time,
                        ],
                        'addonOptions' => [
                            'asButton' => true,
                            'buttonOptions' => ['class' => 'btn btn-primary']
                        ]
                    ],
                    'widgetOptions2' => [
                        //'size' => 'sm',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'showMeridian' => false,
                            'minuteStep' => 5,
                        ],
                        'options'=>[
                            'readonly'=>true,
                            'value' => ($model->isNewRecord) ? null : $model->end_time,
                        ],
                        'addonOptions' => [
                            'asButton' => true,
                            'buttonOptions' => ['class' => 'btn btn-primary']
                        ]
                    ],
                ]);
                ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/lesson-pair'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['curriculum/lesson-pair', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete'], 'curriculum/lesson-pair-delete') ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_education_year_search',
                            ]
                        ])->label(false) ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name'), 'id'=>'_name_search'])->label(false) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sticky' => '#sidebar',
                    'sortable' => true,
                    'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->name, Url::current(['id' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => 'start_time',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->period, Url::current(['id' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => '_education_year',
                            'value' => 'educationYear.name',
                        ],

                    ],
                ]); ?>
            </div>
        </div>
    </div>

</div>

<?php Pjax::end() ?>
