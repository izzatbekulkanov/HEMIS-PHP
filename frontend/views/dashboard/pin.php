<?php

use common\components\Config;

use common\models\structure\EUniversity;
use frontend\models\form\FormStudentLogin;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

/* @var $this \backend\components\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model FormStudentLogin */

$this->params['breadcrumbs'][] = $this->title;
$this->addBodyClass('hold-transition login-page');
$un = EUniversity::findCurrentUniversity();
?>
<div class="login-bg">
    <div class="login-box">
        <div class="box box-success">
            <div class="box-header">
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-default dropdown-toggle btn-transparent"
                            data-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="<?= linkTo(['dashboard/login']) ?>"><?= __('Log in') ?></a></li>
                    </ul>
                </div>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'diploma-form']); ?>
            <div class="box-body">
                <?php if ($un && $un->university): ?>
                    <h4 class="login-box-msg"><?= $un->university->name; ?></h4>
                <?php endif; ?>
                <h5 class="login-box-msg"><?= $this->title ?></h5>


                <?= $form->field($model, 'pin', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-user form-control-feedback"></span>'])
                    ->input('number', ['placeholder' => __('Passport Pin')])->label(false) ?>

                <div class="form-group">
                    <?= getenv('RECAPTCHA_ENABLE') ? $form->field($model, 'reCaptcha')->widget(
                        \himiklab\yii2\recaptcha\ReCaptcha3::class,
                        [
                            'action' => 'diploma'
                        ]
                    )->label(false)->error(false) : $form->field($model, 'reCaptcha')->widget(Captcha::className(),
                        [
                            'captchaAction' => 'dashboard/captcha',
                            'options' => [
                                'placeholder' => __('Tekshiruv kodini kiriting')
                            ],
                            'template' => '<div class="row"><div class="col col-xs-4"><div class="captcha_img">{image}</div></div><div class="col col-xs-8">{input}</div></div>'
                        ]
                    )->label(false); ?>
                </div>

            </div>
            <div class="box-footer">
                <div class="row">
                    <div class="col col-md-4"></div>
                    <div class="col col-md-8">
                        <?= Html::submitButton(__('Download'), ['class' => 'btn btn-primary btn-block btn-flat']) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>