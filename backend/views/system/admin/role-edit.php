<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\Admin;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this \backend\components\View */
/* @var $model common\models\system\AdminRole */

$this->title = $model->isNewRecord ? __('Create Administrator Role') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['system/role'], 'label' => __('System Role')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$searchModel = new AdminResource();
$dataProvider = $searchModel->searchForRole($model, Yii::$app->request->get());
?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-5" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => !$model->isNewRecord])->label() ?>
                        <?= $form->field($model, 'status')->widget(Select2::classname(), [
                            'data' => AdminRole::getStatusOptions(),
                            'options' => ['class' => 'select2', 'disabled' => $model->isSuperAdminRole()],
                            'theme' => Select2::THEME_DEFAULT,
                            'pluginLoading' => false,
                            'hideSearch' => true,
                            'pluginOptions' => [
                                'allowClear' => false
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?php $this->getResourceLink(__('Delete'), ['system/role-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Update'), ['class' => 'btn btn-primary btn-flat']) ?>
                <?php else: ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (!$model->isSuperAdminRole()): ?>
        <div class="col col-md-7">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Permission to Resources') ?></h3>
                </div>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'group',
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->getNameLabel();
                            },
                        ],
                        [
                            'attribute' => 'path',
                        ],
                        [
                            'attribute' => 'activated',
                            'format' => 'raw',
                            'value' => function (AdminResource $data) use ($model) {
                                return CheckBo::widget([
                                    'type' => 'switch',
                                    'options' => [
                                        'onclick' => "",
                                        'checked' => $model->hasResource($data),
                                        'value' => $data->id,
                                        'disabled' => true,
                                    ],
                                    'name' => "AdminRole[resourceIds][]",
                                ]);
                            },
                        ],
                    ],
                ]); ?>
                <div class="box-footer text-right">
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Update'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
