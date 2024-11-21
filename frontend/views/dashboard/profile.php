<?php

use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\components\Config;
use common\models\system\classifier\Accommodation;
use common\models\system\classifier\Soato;
use frontend\models\system\Student;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model \frontend\models\form\FormStudentProfile */

$this->title = __('My Profile');
$this->params['breadcrumbs'][] = $model->getFullName();

?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true,]); ?>
<div class="row">
    <div class="col col-md-6">
        <div class="box box-default">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('My Profile') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'disabled' => true])->label() ?>
                        <?= $form->field($model, 'second_name')->textInput(['maxlength' => true, 'disabled' => true])->label() ?>
                        <?= $form->field($model, 'login')->textInput(['maxlength' => true, 'disabled' => true, 'value' => $model->{Student::getLoginIdAttribute()}])->label(__('Login')) ?>
                        <?= $form->field($model, 'passport_number')->textInput(['maxlength' => true, 'disabled' => true])->label() ?>

                        <br>
                        <?php $label = '<label class="control-label cb-checkbox">' . __('Change Password') . Html::checkbox("FormStudentProfile[change_password]", false, ['id' => 'change_password']) . '</label>' ?>
                        <div class="row checkbo">
                            <div class="col col-md-12">
                                <?= $form->field($model, 'password', ['template' => "$label{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('New Password')])->label($label) ?>
                            </div>
                            <div class="col col-md-12">
                                <?= $form->field($model, 'confirmation', ['template' => "{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('Password Confirmation')]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col col-md-5">
                        <?php ?>
                        <?= $form->field($model, 'image')
                            ->widget(Upload::className(), [
                                'url' => ['dashboard/file-upload', 'type' => 'profile'],
                                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'sortable' => true,
                                'maxFileSize' => 10 * 1024 * 1024, // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple' => false,
                                'useCaption' => false,
                                'clientOptions' => [],
                            ])->label(); ?>
                    </div>
                </div>

            </div>
            <?php if (false): ?>
                <div class="box-header bg-gray">
                    <h3 class="box-title"><?= __('Current Address Information') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">

                        <div class="col-md-6">
                            <?= $form->field($model, '_current_province')->widget(Select2Default::classname(), [
                                'data' => Soato::getParentClassifierOptions(),
                                'allowClear' => false,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_current_province',
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, '_current_district')->widget(DepDrop::classname(), [
                                'data' => ArrayHelper::map($model->_current_province ? Soato::getChildrenOption($model->_current_province) : [], 'code', 'name'),
                                'language' => 'en',
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'placeholder' => __('-Choose-'),
                                    'id' => '_current_district',
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_current_province'],
                                    'placeholder' => __('-Choose-'),
                                    'url' => currentTo(['region' => 1])
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'current_address')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, '_accommodation')->widget(Select2Default::classname(), [
                                'data' => Accommodation::getClassifierOptions(),
                                'allowClear' => false,
                                'hideSearch' => false,
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'phone')->widget(MaskedInputDefault::className(), [
                                'mask' => '9{9,12}',
                            ]) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
        $("input[name=\'FormStudentProfile[password]\'],input[name=\'FormStudentProfile[confirmation]\']").attr("disabled", !this.checked);
    })
')

?>

