<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use common\models\academic\EDecree;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\LocalityType;
use common\models\structure\EDepartment;
use common\models\system\classifier\MasterSpeciality;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\depdrop\DepDrop;

/* @var $this \backend\components\View */
/* @var $model EDecree */

$this->title = $model->isNewRecord ? __('Create Decree') : $model->getShortTitle();
$this->params['breadcrumbs'][] = ['url' => ['decree/index'], 'label' => __('Decree Index')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>
<div class="row">
    <div class="col col-md-9">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getFaculties(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'disabled' => $this->_user()->role->isDeanRole(),
                        ]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, '_decree_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\DecreeType::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'header')->textarea(['maxlength' => true, 'rows' => 4]) ?>
                        <?= $form->field($model, 'body')->textarea(['maxlength' => true, 'rows' => 16]) ?>
                        <?= $form->field($model, 'trailer')->textarea(['maxlength' => true, 'rows' => 4]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col col-md-3" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <?= $form->field($model, 'date')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                    ],
                ]); ?>

                <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'status')->widget(Select2Default::classname(), [
                    'data' => EDecree::getStatusOptions(),
                    'allowClear' => false,
                    'placeholder' => false,
                ]) ?>

                <?= $form->field($model, 'file')
                    ->widget(\backend\widgets\UploadDefault::class, [
                        'url' => ['dashboard/file-upload', 'type' => 'file'],
                        'acceptFileTypes' => new JsExpression('/(\.|\/)(pdf)$/i'),
                        'accept' => '.pdf',
                        'sortable' => true,
                        'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                        'maxNumberOfFiles' => 1,
                        'multiple' => false,
                        'clientOptions' => [],
                    ]); ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['decree/delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
