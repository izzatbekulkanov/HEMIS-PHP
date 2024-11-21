<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\FormAdminReset */

$this->title = __('Reset Password');

$this->params['breadcrumbs'][] = $this->title;
$this->addBodyClass('hold-transition login-page')
?>
<div class="login-box">
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg"><?= __('Enter password and confirmation below') ?></p>

        <?php $form = ActiveForm::begin(['id' => 'reset-form', 'enableAjaxValidation' => true]); ?>
        <?= $form->field($model, 'password', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-lock form-control-feedback"></span>'])
            ->passwordInput(['placeholder' => __('Password')])->label(false) ?>
        <?= $form->field($model, 'confirmation', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-lock form-control-feedback"></span>'])
            ->passwordInput(['placeholder' => __('Confirmation')])->label(false) ?>
        <div class="row">
            <div class="col col-md-6 checkbo">
                <label class="control-label cb-checkbox" for="rememberMe">
                    <?= Html::a(__('Cancel'), linkTo(['/dashboard/login']), ['class' => 'btn btn-default btn-block btn-flat', 'name' => 'login-button']) ?>
                </label>
            </div>
            <div class="col col-md-6">
                <?= Html::submitButton(__('Reset'), ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJs('
    $(document).ready(function () {
        var input = $("#adminresetform-login");
        input.focus().val(input.val());
    })
')
?>
