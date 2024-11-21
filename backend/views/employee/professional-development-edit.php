<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;
use common\models\employee\EEmployeeMeta;
use common\models\employee\EEmployeeProfessionalDevelopment;
use common\models\system\classifier\Qualification;
use common\models\system\classifier\TeacherPositionType;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title = $model->isNewRecord ? __('Create professional development') : $model->employee->getFullName();
$this->params['breadcrumbs'][] = [
    'url' => ['employee/professional-development'],
    'label' => __('Teacher professional development'),
];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, '_employee')->widget(
                            Select2Default::classname(),
                            [
                                'data' => $teachers,
                                'allowClear' => false,
                                'hideSearch' => false,
                            ]
                        ) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, '_employee_position')->widget(
                            Select2Default::classname(),
                            [
                                'data' => TeacherPositionType::getTeacherOptions(),
                                'allowClear' => false,
                                'placeholder' => false,
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'training_title')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($model, '_training_place')->widget(
                            Select2Default::classname(),
                            [
                                'data' => Qualification::getClassifierOptions(),
                                'allowClear' => false,
                                'placeholder' => false,
                            ]
                        ) ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($model, 'training_year')->input('number', ['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-3">
                        <?= $form->field($model, 'begin_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'id' => 'begin_date',
                            ],
                        ]); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($model, 'end_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'id' => 'end_date',
                            ],
                        ]); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'document')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer text-right">
            <?php if (!$model->isNewRecord): ?>
                <?= $this->getResourceLink(
                    __('Delete'),
                    ['employee/professional-development-edit', 'id' => $model->id, 'delete' => 1],
                    ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0],
                    'employee/professional-development-delete'
                ) ?>
            <?php endif; ?>
            <?= Html::submitButton(
                '<i class="fa fa-check"></i> ' . __('Save'),
                ['class' => 'btn btn-primary btn-flat']
            ) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>


