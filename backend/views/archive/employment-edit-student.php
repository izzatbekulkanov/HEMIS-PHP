<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;

use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\GraduateInactiveType;
use common\models\system\classifier\GraduateFieldsType;
use common\models\system\classifier\Gender;

use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\curriculum\EducationYear;
use common\models\archive\EStudentEmployment;

use kartik\depdrop\DepDrop;
use kartik\select2\Select2;

/* @var $this \backend\components\View */
/* @var $model \common\models\archive\EStudentEmployment */

$this->title = __('Edit Employment Information');
$this->params['breadcrumbs'][] = [
    'url' => ['archive/employment'],
    'label' => __('Archive Employment'),
];

$this->params['breadcrumbs'][] = @$meta->student->fullName;
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initEmploymentForm()");
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
<?php echo $form->errorSummary($model) ?>
<?php echo $form->field($model, 'id')->hiddenInput(['id' => 'id'])->label(false); ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('Student information') ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => true,
                    'readonly' => true,
                    'options' => [
                        'id' => '_department',
                        'disabled' => true,
                        'readonly' => true,
                    ],
                ]) ?>
            </div>
            <div class="col-md-6">
                <?php
                $specialties = array();
                if ($model->_department) {
                    $specialties = ESpecialty::getHigherSpecialty($model->_department);
                }

                ?>
                <?= $form->field($model, '_specialty')->widget(DepDrop::classname(), [
                    'data' => $specialties,
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'id' => '_specialty',
                        'disabled' => true,
                        'readonly' => true,
                        'placeholder' => __('-Choose Specialty-'),
                    ],
                    'pluginOptions' => [
                        'depends' => ['_department'],
                        'url' => Url::to(['/ajax/get_specialty']),
                        'placeholder' => __('-Choose Specialty-'),
                    ],
                ]); ?>


            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => true,
                    'readonly' => true,
                    'options' => [
                        'id' => '_education_year',
                    ],
                ]) ?>
            </div>

            <div class="col-md-3">
                <?= $form->field($model, '_education_form')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationForm::getClassifierOptions(),
                        'allowClear' => false,
                        'placeholder' => false,
                        'disabled' => true,
                        'readonly' => true,
                    ]
                ); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, '_education_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationType::getClassifierOptions(),
                        'allowClear' => false,
                        'placeholder' => false,
                        'disabled' => true,
                        'readonly' => true,
                    ]
                ); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, '_gender')->widget(
                    Select2Default::classname(),
                    [
                        'data' => Gender::getClassifierOptions(),
                        'allowClear' => false,
                        'placeholder' => false,
                        'disabled' => true,
                        'readonly' => true,
                    ]
                ); ?>
            </div>

        </div>

    </div>
</div>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('Employment Information') ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <? //= $form->field($model, '_employment_status')->radioList(EStudentEmployment::getEmploymentStatusOptions(),['class'=>'custom-control custom-radio custom-control-inline']); ?>
                <?= $form->field($model, '_employment_status')->widget(Select2Default::classname(), [
                    'data' => EStudentEmployment::getEmploymentStatusOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,

                    'options' => [
                        'id' => '_employment_status',
                    ],

                ]) ?>

            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'company_name')->textInput(['maxlength' => true, 'id' => 'company_name']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'position_name')->textInput(['maxlength' => true, 'id' => 'position_name']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'employment_doc_number')->textInput(['maxlength' => true, 'id' => 'employment_doc_number']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'employment_doc_date')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('Enter date'),
                        'id' => 'employment_doc_date'
                        //'value' => empty($model->birth_date) ? null : Yii::$app->formatter->asDate($model->birth_date, 'php:Y-m-d'),
                        //'disabled' => true
                    ],
                ]); ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'start_date')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('Enter date'),
                        'id' => 'start_date'
                    ],
                ]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?php
                $field_types = [];
                if ($model->_employment_status == EStudentEmployment::EMPLOYMENT_STATUS_MASTER || $model->_employment_status == EStudentEmployment::EMPLOYMENT_STATUS_ORDINATOR || $model->_employment_status == EStudentEmployment::EMPLOYMENT_STATUS_DOCTORATE || $model->_employment_status == EStudentEmployment::EMPLOYMENT_STATUS_SECOND_HIHGER) {
                    $field_types = GraduateFieldsType::getFieldTypeOptions();
                } elseif ($model->_employment_status == EStudentEmployment::EMPLOYMENT_STATUS_EMPLOYEE) {
                    $field_types = GraduateFieldsType::getFieldTypeOptions('other');
                }

                ?>
                <?= $form->field($model, '_graduate_fields_type')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map($field_types, 'code', 'name'),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_graduate_fields_type',
                    ],

                ]) ?>


            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'workplace_compatibility')->widget(Select2Default::classname(), [
                    'data' => EStudentEmployment::getWorkplaceCompatibilityStatusOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options' => [
                        'id' => 'workplace_compatibility',
                    ],

                ]) ?>
            </div>
            <div class="col-md-4">

                <?= $form->field($model, '_graduate_inactive')->widget(Select2Default::classname(), [
                    'data' => GraduateInactiveType::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,

                    'options' => [
                        'id' => '_graduate_inactive',
                    ],

                ]) ?>

            </div>

        </div>

    </div>
    <div class="box-footer text-right">
        <?= $this->getResourceLink(__('Cancel'), ['archive/employment'], ['class' => 'btn btn-default btn-flat']) ?>
        <? if (!$model->isNewRecord): ?>
            <?= $this->getResourceLink(__('Delete'), ['archive/employment-edit', 'employment' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
        <? endif; ?>
        <?= Html::submitButton(
            '<i class="fa fa-check"></i> ' . __('Save'),
            ['class' => 'btn btn-primary btn-flat']
        ) ?>
    </div>
</div>


<?php ActiveForm::end(); ?>
<div class="row">
    <div class="col-md-5">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>

<script>
    function initEmploymentForm() {
        $('#_employment_status').change(function () {
            initEmploymentStatus();
        });
        initEmploymentStatus();
    }

    function initEmploymentStatus() {
        var id = parseInt($('#_employment_status').val());
        var model = !isNaN($('#id').val()) ? parseInt($('#id').val()) : 0;
        if (!isNaN(id)) {
            if (id != <?=EStudentEmployment::EMPLOYMENT_STATUS_REASON?>) {
                $.ajax({
                    url: '<?=Url::current(['list' => 1]);?>',
                    type: "GET",
                    data: {status: id, employment: model},
                    dataType: "json",
                    success: function (data) {
                        if (data.success) {
                            $("#_graduate_fields_type").html(data._graduate_fields_type);

                        } else {
                            alert(data.error);
                        }
                    }
                });
                $("#_graduate_inactive").attr('disabled', true);
                $("#company_name").attr('disabled', false);
                $("#position_name").attr('disabled', false);
                $("#employment_doc_number").attr('disabled', false);
                $("#employment_doc_date").attr('disabled', false);
                $("#start_date").attr('disabled', false);
                $("#_graduate_fields_type").attr('disabled', false);
                $("#workplace_compatibility").attr('disabled', false);
            } else {
                $("#_graduate_inactive").attr('disabled', false);
                $("#company_name").attr('disabled', true);
                $("#position_name").attr('disabled', true);
                $("#employment_doc_number").attr('disabled', true);
                $("#employment_doc_date").attr('disabled', true);
                $("#start_date").attr('disabled', true);
                $("#_graduate_fields_type").attr('disabled', true);
                $("#workplace_compatibility").attr('disabled', true);
            }
        }

    }
</script>

