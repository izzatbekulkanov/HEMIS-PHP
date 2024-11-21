<?php

use backend\widgets\DatePickerDefault;
use common\components\Config;
use common\models\system\classifier\EmployeeType;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\EmploymentForm;
use common\models\system\classifier\EmploymentStaff;
use common\models\system\classifier\TeacherStatus;
use common\models\employee\EEmployee;
use common\models\structure\EDepartment;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\depdrop\DepDrop;
use kartik\date\DatePicker;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title = $model->employee->getFullName();
$this->params['breadcrumbs'][] = ['url' => ["employee/$type"]];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, '_employee')->widget(Select2Default::classname(), [
                            'data' => EEmployee::getEmployees(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'disabled' => $employee != null,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, '_position')->widget(Select2Default::classname(), [
                            'data' => $type == 'teacher' ? TeacherPositionType::getTeacherOptions() : TeacherPositionType::getDirectionOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'placeholder' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                            'data' => $type == 'teacher' ? EDepartment::getDepartments() : EDepartment::getDirections(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'placeholder' => false,
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <?php
                        $options = EmployeeType::getClassifierOptions();
                        ?>
                        <?php if ($type == 'teacher'): ?>
                            <?= $form->field($model, '_employee_type')->widget(Select2Default::classname(), [
                                'data' => $options,
                                'allowClear' => false,
                                'placeholder' => false,
                                'disabled' => true,
                            ]) ?>
                        <?php else: unset($options[EmployeeType::EMPLOYEE_TYPE_TEACHER]) ?>
                            <?= $form->field($model, '_employee_type')->widget(Select2Default::classname(), [
                                'data' => $options,
                                'allowClear' => false,
                                'placeholder' => false,
                            ]) ?>
                        <?php endif ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, '_employment_form')->widget(Select2Default::classname(), [
                            'data' => EmploymentForm::getClassifierOptions(),
                            'allowClear' => false,
                            'placeholder' => false,
                        ]) ?>
                    </div>

                    <div class="col-md-4">
                        <?= $form->field($model, '_employment_staff')->widget(Select2Default::classname(), [
                            'data' => EmploymentStaff::getClassifierOptions(),
                            'allowClear' => false,
                            'placeholder' => false,
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <? /*<div class="col-md-4">
                        <?= $form->field($model, '_employee_status')->widget(Select2Default::classname(), [
                            'data' => TeacherStatus::getClassifierOptions(),
                            'allowClear' => false,
                            'placeholder' => false,
                            //'disabled' => true,
                        ]) ?>
                    </div> */?>
                    <div class="col-md-4">
                        <?= $form->field($model, 'contract_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'contract_date')->widget(DatePickerDefault::classname(), [
                        ]); ?>
                    </div>
                </div>
                <div class="row">

                    <div class="col-md-4">
                        <?= $form->field($model, 'decree_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'decree_date')->widget(DatePickerDefault::classname(), [
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ["employee/$type", 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>


