<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\system\classifier\Locality;
use common\models\system\classifier\ProjectCurrency;
use common\models\system\classifier\ProjectType;
use common\models\structure\EDepartment;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;


/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployee */

$this->title = $model->isNewRecord ? __('Create Project') : $model->getShortTitle();
$this->params['breadcrumbs'][] = ['url' => ['science/project'], 'label' => __('Science Project')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();


\yii\widgets\MaskedInputAsset::register($this);
?>


<? //php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data' => ['pjax' => false]]]); ?>
    <div class="row">
        <div class="col col-md-12">
            <div class="box box-default ">
                <div class="box-body">
                    <div class="row">
                        <div class="col col-md-12">
                            <div class="row">
                                <div class="col-md-10">
                                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'id' => 'name']) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= $form->field($model, 'project_number')->textInput(['maxlength' => true, 'id' => 'project_number']) ?>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                                        'data' => EDepartment::getDepartments(),
                                        'allowClear' => false,
                                        'hideSearch' => false,
                                        'placeholder' => false,
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, '_project_type')->widget(Select2Default::classname(), [
                                        'data' => ProjectType::getClassifierOptions(),
                                        'allowClear' => false,
                                        'placeholder' => false,
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, '_locality')->widget(Select2Default::classname(), [
                                        'data' => Locality::getClassifierOptions(),
                                        'allowClear' => false,
                                        'placeholder' => false,
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, '_project_currency')->widget(Select2Default::classname(), [
                                        'data' => ProjectCurrency::getClassifierOptions(),
                                        'allowClear' => false,
                                        'placeholder' => false,
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <?= $form->field($model, 'contract_number')->textInput(['maxlength' => true, 'id' => 'name']) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'contract_date')->widget(DatePickerDefault::classname(), [
                                        'options' => [
                                            'placeholder' => __('YYYY-MM-DD'),
                                            'id' => 'contract_date',
                                        ],
                                    ]); ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'start_date')->widget(DatePickerDefault::classname(), [
                                        'options' => [
                                            'placeholder' => __('YYYY-MM-DD'),
                                            'id' => 'start_date',
                                        ],
                                    ]); ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'end_date')->widget(DatePickerDefault::classname(), [
                                        'options' => [
                                            'placeholder' => __('YYYY-MM-DD'),
                                            'id' => 'end_date',
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord): ?>
                        <?= $this->getResourceLink(__('Delete'), ['science/project-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>