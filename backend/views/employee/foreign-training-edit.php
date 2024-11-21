<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use backend\widgets\SelectizeDefault;
use common\components\Config;
use common\models\curriculum\EducationYear;
use common\models\employee\EEmployeeAcademicDegree;
use common\models\system\classifier\Gender;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use common\models\system\classifier\CitizenshipType;
use backend\widgets\Select2Default;
use common\models\system\classifier\ScienceBranch;
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
/* @var $model \common\models\employee\EEmployeeTraining */

$this->title = $model->isNewRecord ? __('Insert Training') : $model->employee->getFullName();
$this->params['breadcrumbs'][] = ['url' => ['employee/foreign-training'], 'label' => __('Employee Foreign Training')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>
<div class="row">
    <div class="col col-md-12">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'id' => 'employee_form', 'options' => ['data' => ['pjax' => false]]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                            'data' => EducationYear::getEducationYears(),
                            'allowClear' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, '_employee', ['options' => ['class' => '']])
                            ->widget(SelectizeDefault::className(), [
                                'loadUrl' => Url::current(['employees' => 1]),
                                'options' => [
                                    'disabled' => !$model->isNewRecord,
                                ],
                                'clientOptions' => [
                                    'maxItems' => 1,
                                    'maxOptions' => 10,
                                    'hideSelected' => true,
                                    'preload' => true,
                                    'openOnFocus' => true,
                                    'valueField' => 'id',
                                    'labelField' => 'name',
                                    'searchField' => ['name', 'code'],
                                    'options' => [[
                                        'id' => $model->_employee,
                                        'name' => $model->employee ? $model->employee->getFullName() : '',
                                        'code' => $model->employee ? $model->employee->employee_id_number : '',
                                    ]],
                                    'create' => true,
                                    'render' => [
                                        'option' => new JsExpression("
                            function(item, escape) {
                                return '<div>' +
                                    '<span class=\"item-option\">' + escape(item.name) + '</span>' +
                                    '<span class=\"item-label\">' + escape(item.code) + '</span>' +
                                '</div>';
                            }
                        "),
                                    ]
                                ],
                            ])->error(false) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, '_training_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\TrainingType::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <?= $form->field($model, 'specialty_name', ['options' => ['class' => '']])
                                ->widget(SelectizeDefault::className(), [
                                    'loadUrl' => Url::current(['specialties' => 1]),
                                    'clientOptions' => [
                                        'delimiter' => '#',
                                        'maxItems' => 1,
                                        'maxOptions' => 10,
                                        'hideSelected' => true,
                                        'preload' => true,
                                        'openOnFocus' => true,
                                        'valueField' => 'name',
                                        'labelField' => 'name',
                                        'searchField' => ['name'],
                                        'options' => [[
                                            'name' => $model->specialty_name,
                                        ]],
                                        'create' => true,
                                        'render' => [
                                            'option' => new JsExpression("
                                            function(item, escape) {
                                                return '<div><span class=\"item-option\">' + escape(item.name) + '</span></div>';
                                            }"),
                                        ]
                                    ],
                                ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, '_country')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\Country::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-8 ">
                        <div class="form-group">
                            <?= $form->field($model, 'university', ['options' => ['class' => '']])
                                ->widget(SelectizeDefault::className(), [
                                    'loadUrl' => Url::current(['universities' => 1]),
                                    'clientOptions' => [
                                        'maxItems' => 1,
                                        'maxOptions' => 10,
                                        'hideSelected' => true,
                                        'preload' => true,
                                        'openOnFocus' => true,
                                        'valueField' => 'name',
                                        'labelField' => 'name',
                                        'searchField' => ['name'],
                                        'options' => [[
                                            'name' => $model->university,
                                        ]],
                                        'create' => true,
                                        'render' => [
                                            'option' => new JsExpression("
                                            function(item, escape) {
                                                return '<div><span class=\"item-option\">' + escape(item.name) + '</span></div>';
                                            }"),
                                        ]
                                    ],
                                ]) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <?= $form->field($model, 'training_contract')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'training_date_start')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'readonly' => true,
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'training_date_end')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'readonly' => true,
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['employee/foreign-training', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['id' => 'submitButton', 'class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>