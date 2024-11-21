<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\AdminResource;

use common\models\system\classifier\EducationType;
use common\models\structure\EDepartment;

use kartik\select2\Select2;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

$this->title = $model->isNewRecord ? __('Create Specialty') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['student/special'], 'label' => __('Special')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-9" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        
						<?= $form->field($model, '_education_type')->widget(Select2::classname(), [
							'data' => ArrayHelper::map(EducationType::find()->where(['status'=>true])->where(['in', 'code', array('11','12') ])->all(), 'code', 'name'),
							'language' => 'en',
							'options' => [
								'placeholder' => Yii::t('app', '-Tanlang-'),
							],
							'pluginOptions' => [
								'allowClear' => true
							],
						]) ?>
						
						<?=	$form->field($model, 'code')->widget(Select2::classname(), [
								'options' => ['multiple'=>false, 'placeholder' => __('-Choose-'),],
								'pluginOptions' => [
									'allowClear' => true,
									'minimumInputLength' => 3,
									'language' => [
										'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
									],
									'ajax' => [
										'url' => Url::to(['ajax/get_specialty_from_classifier']),
										'dataType' => 'json',
										'data' => new JsExpression('function(params) { return {q:params.term}; }')
									],
									'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
									'templateResult' => new JsExpression('function(code) { return code.info; }'),
									'templateSelection' => new JsExpression('function (code) { return code.info; }'),
								],
							]);
						?>
					
						<?= $form->field($model, '_department')->widget(Select2::classname(), [
							'data' => ArrayHelper::map(EDepartment::find()->where(['_structure_type'=>'11'])->orderBy(['code' => SORT_ASC])->all(), 'id', 'name'),
							'language' => 'en',
							'options' => [
								'placeholder' => __('-Choose-'),
							],
							'pluginOptions' => [
								'allowClear' => true
							],
						]) ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['student/special-edit', 'id' => $model->code, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>

</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
