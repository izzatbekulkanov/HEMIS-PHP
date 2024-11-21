<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\structure\EDepartment;
use common\models\system\classifier\SemestrType;

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use common\models\curriculum\MarkingSystem;

$this->params['breadcrumbs'][] = $this->title;
/**
 * @var $model MarkingSystem
 */
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">

            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
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
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->name, ['curriculum/marking-system', 'code' => $data->code], []);
                            },
                        ],
                        [
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, ['curriculum/marking-system', 'code' => $data->code], []);
                            },
                        ],
                        'minimum_limit',
                        'count_final_exams',

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

                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'disabled' => ($model->code == MarkingSystem::MARKING_SYSTEM_RATING || $model->code == MarkingSystem::MARKING_SYSTEM_FIVE || $model->code == MarkingSystem::MARKING_SYSTEM_CREDIT)]) ?>
                <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => !$model->isNewRecord])->label() ?>
                <?= $form->field($model, 'count_final_exams')->textInput(['maxlength' => true, 'disabled' => ($model->code == MarkingSystem::MARKING_SYSTEM_RATING || $model->code == MarkingSystem::MARKING_SYSTEM_FIVE || $model->code == MarkingSystem::MARKING_SYSTEM_CREDIT)])->label() ?>
                <?= $form->field($model, 'minimum_limit')->textInput(['maxlength' => true])->label() ?>

                <?php if ($model->isCreditMarkingSystem()): ?>
                    <?= $form->field($model, 'gpa_limit')->textInput(['maxlength' => true])->label() ?>
                <?php endif; ?>
                <?= $form->field($model, 'description')->textarea(['maxlength' => true, ]) ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/marking-system'], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?php if ($model->code != MarkingSystem::MARKING_SYSTEM_RATING && $model->code != MarkingSystem::MARKING_SYSTEM_FIVE && $model->code != MarkingSystem::MARKING_SYSTEM_CREDIT): ?>
                        <?= $this->getResourceLink(__('Delete'), ['curriculum/marking-system', 'code' => $model->code, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
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

