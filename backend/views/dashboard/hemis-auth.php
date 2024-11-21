<?php

use common\components\Config;
use common\models\structure\EUniversity;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \backend\components\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\FormHemisAuth */

$this->title = __('Authenticate to HEMIS system');
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
                </ul>
            </div>
        </div>
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <div class="box-body">
            <?php if ($un && $un->university): ?>
                <h4 class="login-box-msg"><?= $un->university->name; ?></h4>
            <?php endif; ?>
            <h5 class="login-box-msg"><?= $this->title ?></h5>
            <p>
                <?=__('Ushbu tizim Oliy va o\'rtamaxsus ta\'lim vazirligining HEMIS axborot tizimi bilan integratsiyalashgan holda ishlashi belgilangan. Funksional vazifalar to\'g\'ri ishlashi uchun tizimni HEMIS tizimda autentifikatsiyadan o\'tkizishingiz shart.')?>
            </p>
            <?= $form->field($model, 'login', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-envelope form-control-feedback"></span>'])
                ->textInput(['placeholder' => __('Login')])->label(false) ?>
            <?= $form->field($model, 'password', ['options' => ['class' => 'form-group has-feedback'], 'template' => '{input}<span class="glyphicon glyphicon-lock form-control-feedback"></span>'])
                ->passwordInput(['placeholder' => __('Password')])->label(false) ?>
        </div>
        <div class="box-footer">
            <div class="row">
                <div class="col col-md-12">
                    <?= Html::submitButton(__('Authenticate'), ['class' => 'btn btn-success btn-block btn-flat']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>