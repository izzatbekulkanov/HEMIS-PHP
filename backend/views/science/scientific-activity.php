<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\classifier\ScientificPlatform;
use common\models\curriculum\EducationYear;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
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
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => EducationYear::getEducationYears(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'placeholder' => __('Education Year'),
                            'options' => [
                                'id' => '_education_year_search',
                            ],
                        ])->label(false); ?>
                        <?//= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>

                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_scientific_platform')->widget(Select2Default::classname(), [
                            'data' => ScientificPlatform::getClassifierOptions(),
                            'hideSearch' => true,
                            'allowClear' => true,
                            'options' => [
                                'id' => '_scientific_platform_search',
                            ],
                        ])->label(false); ?>
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
                            'attribute' => '_scientific_platform',
                            'format' => 'raw',
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                            'value' => function ($data) use ($model) {
                                return Html::a($data->scientificPlatform->name, ['science/scientific-activity', 'code' => $data->id], []);
                            }
                        ],
                        [
                            'attribute' => '_education_year',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if($data->educationYear)
                                    return $data->educationYear->name;
                            },
                        ],
                        [
                            'attribute' => 'profile_link',
                            'format' => 'raw',
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                            'value' => function ($data) use ($model) {
                                return Html::a(\yii\helpers\StringHelper::truncate($data->profile_link,21), ['science/scientific-activity', 'code' => $data->id], []);
                            }
                        ],
                        'h_index',
                        'publication_work_count',
                        'citation_count',
                        /*[
                            'attribute' => 'updated_at',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            },
                        ]*/
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

                <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'hideSearch' => true,
                    'allowClear' => false,
                ]) ?>
                <?= $form->field($model, '_scientific_platform')->widget(Select2Default::classname(), [
                    'data' => ScientificPlatform::getClassifierOptions(),
                    'hideSearch' => true,
                    'allowClear' => false,
                ]) ?>


                <?= $form->field($model, 'profile_link')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'h_index')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'publication_work_count')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'citation_count')->textInput(['maxlength' => true]) ?>

            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['science/scientific-activity'], ['class' => 'btn btn-default btn-flat']) ?>
                <?php if (!$model->is_checked): ?>
                    <?= $this->getResourceLink(__('Delete'), ['science/scientific-activity', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
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
