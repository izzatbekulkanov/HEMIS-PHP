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
/* @var $model \common\models\employee\EEmployeeAcademicDegree */

$this->title = $model->isNewRecord ? __('Create Academic Degree') : $model->employee->getFullName();
$this->params['breadcrumbs'][] = ['url' => ['employee/academic-degree'], 'label' => __('Employee Academic Degree')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initEmployeeForm()");


if (is_array($model->specialty_code)) {
    $model->specialty_code = implode(',', $model->specialty_code);
}

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'id' => 'employee_form', 'options' => ['data' => ['pjax' => false]]]); ?>
<div class="row">
    <div class="col col-md-12">
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
                        <div class="form-group">
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
                                                }"),
                                        ]
                                    ],
                                ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= $form->field($model, 'specialty_code', ['options' => ['class' => '']])
                                ->widget(SelectizeDefault::className(), [
                                    'loadUrl' => Url::current(['specialties' => 1]),
                                    'clientOptions' => [
                                        'maxItems' => 1,
                                        'maxOptions' => 10,
                                        'hideSelected' => true,
                                        'preload' => true,
                                        'openOnFocus' => true,
                                        'valueField' => 'code',
                                        'labelField' => 'code',
                                        'searchField' => ['name', 'code'],
                                        'options' => [[
                                            'code' => $model->specialty_code,
                                            'name' => $model->specialty_name,
                                            'short' => \yii\helpers\StringHelper::truncateWords($model->specialty_name, 6),
                                        ]],
                                        'create' => true,
                                        'render' => [
                                            'option' => new JsExpression("
                                                function(item, escape) {
                                                    return '<div>' +
                                                        '<span class=\"item-option\">' + escape(item.code) + '</span>' +
                                                        '<span class=\"item-label\">' + escape(item.short) + '</span>' +
                                                    '</div>';
                                                }"),
                                        ]
                                    ],
                                ]) ?>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, 'specialty_name')->textInput(['maxlength' => true]) ?>
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
                    <div class="col-md-8">
                        <?= $form->field($model, 'university')->textInput(['maxlength' => true]) ?>
                    </div>

                    <div class="col-md-4">
                        <?= $form->field($model, 'diploma_type')->widget(Select2Default::classname(), [
                            'data' => EEmployeeAcademicDegree::getTypeOptions(),
                            'allowClear' => false,
                        ]) ?>

                        <?= $form->field($model, 'diploma_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <div style="<?= $model->diplomaTypeIsRank() ? '' : 'display:none' ?>">
                            <?= $form->field($model, '_academic_rank')->widget(Select2Default::classname(), [
                                'data' => AcademicRank::getClassifierOptions(),
                                'allowClear' => false,
                            ]) ?>
                        </div>
                        <div style="<?= $model->diplomaTypeIsRank() ? 'display:none' : '' ?>">
                            <?= $form->field($model, '_academic_degree')->widget(Select2Default::classname(), [
                                'data' => AcademicDegree::getClassifierOptions(),
                                'allowClear' => false,
                            ]) ?>
                        </div>
                        <?= $form->field($model, 'diploma_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'readonly' => true,
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'council_number')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'council_date')->widget(DatePickerDefault::classname(), [
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
                    <?= $this->getResourceLink(__('Delete'), ['employee/academic-degree', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['id' => 'submitButton', 'class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div class="row">
    <div class="col-md-5">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>

<script>
    let specs = <?=json_encode(ArrayHelper::map(ScienceBranch::find()->all(), 'code', 'name'))?>;

    function initEmployeeForm() {
        let s = $('#eemployeeacademicdegree-specialty_code');
        s.on('change', function () {
            let val = $(this).val();
            if (specs.hasOwnProperty(val)) {
                $('#eemployeeacademicdegree-specialty_name').val(specs[val]);
            }
        })
        $('#eemployeeacademicdegree-diploma_type').on('change', function () {
            if ($(this).val() === 'rank') {
                $('#eemployeeacademicdegree-_academic_rank').parent().parent().show();
                $('#eemployeeacademicdegree-_academic_degree').parent().parent().hide();
            } else {
                $('#eemployeeacademicdegree-_academic_rank').parent().parent().hide();
                $('#eemployeeacademicdegree-_academic_degree').parent().parent().show();
            }
        })
    }
</script>