<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\infrastructure\Building;
use common\models\system\classifier\AuditoriumType;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_building')->widget(Select2Default::class, [
                            'data' => Building::getOptions(),

                            'options' => [
                                'id' => '_building_search',
                            ]
                        ])->label(false) ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_auditorium_type')->widget(Select2Default::class, [
                            'data' => AuditoriumType::getClassifierOptions(),
                            'options' => [
                                'id' => '_auditorium_type_search',
                            ]
                        ])->label(false) ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name'), 'id' => '_name_search'])->label(false) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sticky' => '#sidebar',
                    'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        /*[
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, Url::current(['id' => $data->code]), []);
                            },
                        ],*/
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->name, Url::current(['id' => $data->code]), []);
                            },
                        ],
                        [
                            'attribute' => '_building',
                            'value' => 'building.name',
                        ],
                        [
                            'attribute' => '_auditorium_type',
                            'value' => 'auditoriumType.name',
                        ],
                        [
                            'attribute' => 'volume',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['action' => linkTo(['infrastructure/auditorium', 'id' => $model->code]), 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <?= $form->field($model, '_building')->widget(Select2Default::classname(), [
                    'data' => Building::getOptions(),
                    'placeholder' => false,
                    'allowClear' => false,
                ]) ?>
                <?= $form->field($model, '_auditorium_type')->widget(Select2Default::classname(), [
                    'data' => AuditoriumType::getClassifierOptions(),
                    'allowClear' => false,
                    'placeholder' => false,
                ]) ?>
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'volume')->textInput() ?>

            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['infrastructure/auditorium'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['infrastructure/auditorium', 'id' => $model->code, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete'], 'infrastructure/auditorium-delete') ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php Pjax::end() ?>
