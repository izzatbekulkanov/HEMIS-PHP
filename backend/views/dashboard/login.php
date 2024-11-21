<?php

use common\components\Config;
use common\models\structure\EUniversity;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

/* @var $this \backend\components\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\FormAdminLogin */

//$this->title = __('Login');
$this->title = Yii::$app->name;
$this->params['breadcrumbs'][] = $this->title;
$this->addBodyClass('hold-transition login-page');
$un = EUniversity::findCurrentUniversity();
?>
<div class="login-box">
    <div class="box box-success">
        <div class="box-header">

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-default dropdown-toggle btn-transparent"
                        data-toggle="dropdown">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach (\common\components\Config::getLanguageOptions() as $lang => $label): ?>
                        <li>
                            <a href="<?= \yii\helpers\Url::current(['language' => $lang]) ?>">
                                <?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="divider"></li>
                    <li><a href="<?= linkTo(['dashboard/reset']) ?>"><?= __('Reset Password') ?></a></li>
                </ul>
            </div>
        </div>
        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableAjaxValidation' => false]); ?>
        <div class="box-body">
            <img class='logo' src="<?= $this->getSystemLogo() ?>">
            <?php if ($un && $un->university): ?>
                <h4 class="login-box-msg"><?= $un->university->name; ?></h4>
            <?php endif; ?>
            <h5 class="login-box-msg"><?= __('{name} axborot tizimi', ['name' => Yii::$app->name]) ?></h5>


            <?= $form->field($model, 'login', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-user form-control-feedback"></span>'])
                ->textInput(['placeholder' => __('Login / EmployeeID')])->label(false) ?>
            <?= $form->field($model, 'password', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-lock form-control-feedback"></span>'])
                ->passwordInput(['placeholder' => __('Password')])->label(false) ?>

            <div class="form-group">
                <?= getenv('RECAPTCHA_DISABLE_BACKEND') ? $form->field($model, 'reCaptcha')->widget(Captcha::className(),
                    [
                        'captchaAction' => 'ajax/captcha',
                        'options' => [
                            'placeholder' => __('Tekshiruv kodini kiriting')
                        ],
                        'template' => '<div class="row"><div class="col col-xs-4"><div class="captcha_img">{image}</div></div><div class="col col-xs-8">{input}</div></div>'
                    ]
                )->label(false) : $form->field($model, 'reCaptcha')->widget(
                    \himiklab\yii2\recaptcha\ReCaptcha3::class,
                    [
                        'action' => 'adminLogin'
                    ]
                )->label(false); ?>
            </div>
        </div>
        <div class="box-footer">
            <div class="row">
                <div class="col col-md-6 checkbo">
                    <?php if ($model->fails < 3): ?>
                        <label class="control-label cb-checkbox " for="rememberMe" style="margin-top: 6px">
                            <?= __('Remember Me') . Html::checkbox("FormAdminLogin[rememberMe]", false, ['id' => 'rememberMe']) ?>
                        </label>
                    <?php else: ?>
                        <?php // Html::a(__('Reset Password'), linkTo(['/dashboard/reset']), ['class' => 'btn btn-default btn-block btn-flat']) ?>
                    <?php endif; ?>
                </div>
                <div class="col col-md-6">
                    <?= Html::submitButton(__('Sign in'), ['class' => 'btn btn-primary btn-block btn-flat']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
