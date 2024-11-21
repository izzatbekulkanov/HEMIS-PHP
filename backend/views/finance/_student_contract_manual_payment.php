<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\EducationYear;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;


?>
<div class="row">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>
    <div class='box-body'>
        <div class="row">
            <div class="col col-md-12">
                <?= $form->field($model, 'payment_number')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'payment_date')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                        'id' => 'payment_date',
                    ],
                ]); ?>
                <?= $form->field($model, 'summa')->textInput(['maxlength' => true, 'id' => 'summa']) ?>
                <?= $form->field($model, 'payment_comment')->textarea(['maxlength' => true, 'rows' => 4]) ?>

            </div>
        </div>
    </div>

    <div class='box-footer text-right'>
        <button type="button" class="btn btn-flat btn-default"
                data-dismiss="modal"><?= __('Close') ?></button>
        <?php if(!$model->isNewRecord): ?>
        <?= $this->getResourceLink(__('Delete'), ['finance/student-contract-manual-edit', 'contract' => $model->_student_contract, 'code' => $model->id, 'payment' => 1, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
        <?php endif;?>
        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>


    </div>
    <?php ActiveForm::end(); ?>

</div>

