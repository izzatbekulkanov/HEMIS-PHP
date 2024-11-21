<?php

use common\models\system\classifier\SubjectGroup;
use common\models\curriculum\Semester;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

//$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => $this->title];
//$this->params['breadcrumbs'][] = $subject->curriculum->name;
//$this->params['breadcrumbs'][] = $subject->semester->name;
//$this->params['breadcrumbs'][] = $subject->subject->name;

?>
<div class="row">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>
    <div class='box-body'>
        <div class="row">
            <div class="col col-md-12">
                <?= $form->field($model, '_group')->widget(Select2Default::classname(), [
                    'data' => $groups,
                    'allowClear' => true,
                    'hideSearch' => false,
                ]) ?>

            </div>
        </div>
    </div>

    <div class='box-footer text-right'>

        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
        <button type="button" class="btn btn-flat btn-default"
                data-dismiss="modal"><?= __('Close') ?></button>

    </div>
    <?php ActiveForm::end(); ?>

</div>

