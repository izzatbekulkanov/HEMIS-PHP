<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

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
                                return Html::a($data->name, ['infrastructure/building', 'id' => $data->code], []);
                            },
                        ],
                        'address'
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['action' => linkTo(['infrastructure/building', 'id' => $model->code]), 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>


            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['infrastructure/building'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['infrastructure/building', 'id' => $model->code, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete'], 'infrastructure/building-delete') ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php Pjax::end() ?>
