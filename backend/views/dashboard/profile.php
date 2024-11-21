<?php

use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model common\models\Admin */

$this->title = __('My Profile');
$this->params['breadcrumbs'][] = $model->full_name;

?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true,]); ?>
<div class="row">
    <div class="col col-md-6">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><?= __('My Profile') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-7">
                        <div class="row">
                            <div class="col col-md-6">
                                <?= $form->field($model, 'login')->textInput(['maxlength' => true, 'readonly' => true])->label() ?>
                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($model, 'language')->widget(Select2::classname(), [
                                    'data' => Config::getLanguageOptions(),
                                    'options' => ['class' => 'select2'],
                                    'theme' => Select2::THEME_DEFAULT,
                                    'hideSearch' => true,
                                    'pluginLoading' => false,
                                    'pluginOptions' => [
                                        'allowClear' => false
                                    ],
                                ]) ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'readonly' => true, 'disabled' => true]) ?>
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'telephone')->widget(MaskedInputDefault::className(), [
                            'mask' => '|+|9|98 99 999-99-99',
                        ]) ?>
                    </div>
                    <div class="col col-md-5">
                        <?php ?>
                        <?= $form->field($model, 'image')
                            ->widget(Upload::className(), [
                                'url' => ['dashboard/file-upload', 'type' => 'profile'],
                                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'sortable' => true,
                                'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple' => false,
                                'useCaption' => false,
                                'clientOptions' => [],
                            ])->label(); ?>
                    </div>
                </div>


                <br>
                <?php $label = '<label class="control-label cb-checkbox">' . __('Change Password') . Html::checkbox("Admin[change_password]", false, ['id' => 'change_password']) . '</label>' ?>
                <div class="row checkbo">
                    <div class="col col-md-12">
                        <?= $form->field($model, 'password', ['template' => "$label{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('New Password')])->label($label) ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($model, 'confirmation', ['template' => "{label}{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('Password Confirmation')]) ?>
                    </div>
                </div>

            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton(__('Update'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
    <div class="col col-md-3"></div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#change_password").on("change", function () {
        $("input[name=\'Admin[password]\'],input[name=\'Admin[confirmation]\']").attr("disabled", !this.checked);
    })
')

?>

