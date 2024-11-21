<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\curriculum\ECurriculum;

use common\models\system\classifier\Language;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

$this->title = $model->isNewRecord ? __('Create Group') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['student/group'], 'label' => __('Group')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-9" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => ECurriculum::getFullOptions($faculty),
                            'allowClear' => true,
                            'hideSearch' => false,
							'options' => [
								'options' => 
									ECurriculum::getAcceptStatus(),
								
							]
                        ]); ?>
                        <?= $form->field($model, '_education_lang')->widget(Select2Default::classname(), [
                            'data' => Language::getClassifierOptions(),
                            'allowClear' => true,
                            'options' => [
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['student/group-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
