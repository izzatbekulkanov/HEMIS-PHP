<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\components\Config;
use common\models\system\Admin;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this \backend\components\View */
/* @var $model common\models\system\Admin */

$this->title = $model->isNewRecord ? __('Create Account') : __('Edit Account');
$this->params['breadcrumbs'][] = ['url' => ['employee/employee'], 'label' => __('Employee Employee')];
$this->params['breadcrumbs'][] = ['url' => ['employee/employee', 'id' => $employee->id], 'label' => $employee->fullName];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

<div class="row">
    <div class="col col-md-8">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-8">
                        <div class="row">
                            <div class="col col-md-12">
                                <?= $form->field($model, 'login')->textInput(['maxlength' => true])->label() ?>
                            </div>
                        </div>
                        <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                        <div class="row">
                            <div class="col col-md-12">
                                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col col-md-12">
                                <?= $form->field($model, 'telephone')->widget(MaskedInputDefault::className(), [
                                    'mask' => '|+|9|98 99 999-99-99',
                                    'options' => []
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col col-md-6">
                                <?= $form->field($model, 'status')->widget(Select2Default::classname(), [
                                    'data' => Admin::getStatusOptions(),
                                    'placeholder' => false,
                                    'allowClear' => false,
                                ]) ?>
                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($model, 'language')->widget(Select2Default::classname(), [
                                    'data' => Config::getLanguageOptions(),
                                    'placeholder' => false,
                                    'allowClear' => false,
                                ]) ?>
                            </div>
                        </div>

                    </div>
                    <div class="col col-md-4">
                        <div class="form-group field-admin-image">
                            <label class="control-label" for="admin-image"><?= __('Image') ?></label>
                            <div class="upload-img-ready">
                                <?= Html::img($employee->getImageUrl(null, 150)); ?>
                            </div>
                        </div>

                    </div>
                </div>
                <?php if ($model->isNewRecord): ?>
                    <div class="row">
                        <div class="col col-md-8">
                            <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'confirmation')->passwordInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php $label = '<label class="control-label cb-checkbox">' . __('Change Password') . Html::checkbox("Admin[change_password]", false, ['id' => 'change_password']) . '</label>' ?>
                    <div class="row checkbo">
                        <div class="col col-md-8">
                            <br>
                            <?= $form->field($model, 'password', ['template' => "$label{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('New Password')])->label($label) ?>
                            <?= $form->field($model, 'confirmation', ['template' => "{label}{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('Password Confirmation')]) ?>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['employee/employee', 'id' => $employee->id, 'account' => 1, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete']) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Update'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
    <?php if (!$model->isSuperAdmin()): ?>
        <div class="col col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('User Roles') ?></h3>
                </div>

                <?php
                if ($this->_user()->role->isSuperAdminRole()) {
                    $dataprovider = (new AdminRole())->search([]);
                } else
                    $dataprovider = (new AdminRole())->search_staff([]);
                ?>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'layout' => "<div class='box-body no-padding'>{items}</div>",
                    'dataProvider' => $dataprovider,
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function (AdminRole $data) {
                                return $data->name;
                            },
                        ],
                        [
                            'attribute' => 'code',
                        ],
                        [
                            'attribute' => 'active',
                            'format' => 'raw',
                            'value' => function (AdminRole $data) use ($model) {
                                return CheckBo::widget([
                                    'type' => 'switch',
                                    'options' => [
                                        'checked' => $model->hasRole($data),
                                        'value' => $data->id,
                                    ],
                                    'name' => "Admin[roleIds][]",
                                ]);
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#change_password").on("change", function () {
        $("input[name=\'Admin[password]\'],input[name=\'Admin[confirmation]\']").attr("disabled", !this.checked);
    })
')
?>

