<?php

use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\system\classifier\University;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\system\classifier\Ownership;
use common\models\system\classifier\UniversityForm;

/**
 * @var $model \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = ['label' => __('Structure University'), 'url' => ['university']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="heducation-form-create">
    <div class="heducation-form-form box box-primary">
        <?php $form = ActiveForm::begin(); ?>
        <div class="box-body">
            <?= $form->field($model, 'code')->widget(Select2Default::classname(), [
                'data' => University::getClassifierOptionsByNameWithCode(),
                'options' => [
                    'onchange' => 'return universityCodeChanged()',
                ],
                'allowClear' => false,
                'hideSearch' => false,
                'disabled' => !$model->isNewRecord,
            ]) ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <div class="row">
                <div class="col col-md-6">
                    <?= $form->field($model, 'contact')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col col-md-6">
                    <?= $form->field($model, 'tin')->widget(MaskedInputDefault::className(), [
                        'mask' => '999999999',
                    ]) ?>
                </div>
            </div>
            <?= $form->field($model, '_soato')->widget(Select2Default::classname(), [
                'data' => \common\models\system\classifier\Soato::getParentClassifierOptions(),
                'allowClear' => false,
                'placeholder' => false,
                'hideSearch' => false,
            ]) ?>
            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, '_ownership')->widget(Select2Default::classname(), [
                'data' => Ownership::getClassifierOptions(),
                'allowClear' => false,
                'placeholder' => false,
            ]) ?>
            <?= $form->field($model, '_university_form')->widget(Select2Default::classname(), [
                'data' => UniversityForm::getClassifierOptions(),
                'allowClear' => false,
                'placeholder' => false,
            ]) ?>
            <?= $form->field($model, 'mailing_address')->textarea(['maxlength' => true, 'rows' => 4]) ?>
            <?= $form->field($model, 'bank_details')->textarea(['maxlength' => true, 'rows' => 4]) ?>
            <?= $form->field($model, 'accreditation_info')->textarea(['maxlength' => true, 'rows' => 4]) ?>
        </div>
        <div class="box-footer text-right">
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<script type="text/javascript">
    function universityCodeChanged() {
        var code = $('#euniversity-code').val();
        var name = $('#euniversity-code').find('option[value=' + code + ']').text();

        if (!$('#euniversity-name').val()) {
            $('#euniversity-name').val(name.substring(6));
        }
        return true;
    }
</script>
