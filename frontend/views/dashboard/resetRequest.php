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
    <div class="login-logo">
        <a href="<?= linkTo(['/']) ?>"><?= __('Dashboard') ?></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg"><?= __('Enter email address to reset password') ?></p>

        <?php $form = ActiveForm::begin(['id' => 'reset-form', 'enableAjaxValidation' => true]); ?>
        <?= $form->field($model, 'email', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-envelope form-control-feedback"></span>'])
            ->textInput(['placeholder' => __('Email')])->label(false) ?>

        <div class="row">
            <div class="col col-md-6 checkbo">
                <?= Html::a(__('Cancel'), linkTo(['/dashboard/login']), ['class' => 'btn btn-default btn-block btn-flat', 'name' => 'login-button']) ?>
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
