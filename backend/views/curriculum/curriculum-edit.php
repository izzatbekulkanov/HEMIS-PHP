<?php

use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;
use common\models\curriculum\MarkingSystem;
use common\models\structure\EDepartment;
use common\models\student\EQualification;
use common\models\student\ESpecialty;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

//use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? __('Create Curriculum') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/curriculum'], 'label' => __('Curriculum')];
$this->params['breadcrumbs'][] = $this->title;

$faculty = "";
if ($this->_user()->role->isDeanRole()) {
    $faculty = $this->_user()->employee->deanFaculties->id;
}

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-9" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                        <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN) { ?>
                            <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => false,
                                'options' => [
                                    'id' => '_department',
                                ],
                            ]) ?>
                        <?php } ?>

                        <?php
                        $specialties = ESpecialty::find()
                            ->where(['_department' => $model->_department, 'active' => ESpecialty::STATUS_ENABLE])
                            ->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
                            ->all();
                        ?>
                        <?= $form->field($model, '_specialty_id')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($specialties, 'id', 'fullName'),
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose-'),
                                'id' => '_specialty_id',

                            ],
                            'pluginOptions' => [
                                'depends' => ['_department'],
                                'placeholder' => __('-Choose-'),
                                'url' => Url::to(['/ajax/get_specialty']),

                            ],
                        ]) ?>
                        <?= $form->field($model, '_qualification')->widget(
                            DepDrop::classname(),
                            [
                                'data' => (!$model->_specialty_id) ? [] : EQualification::getSelectOptions(
                                    $model->_specialty_id ?? ""
                                ),
                                'language' => 'en',
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT
                                ],
                                'options' => [
                                    'placeholder' => __('-Choose-'),
                                    'id' => '_qualification',

                                ],
                                'pluginOptions' => [
                                    'depends' => ['_specialty_id'],
                                    'placeholder' => __('-Choose-'),
                                    'url' => Url::to(['/ajax/get-qualifications']),
                                ],
                            ]
                        ) ?>
                        <div class="row">
                            <div class="col col-md-4">
                                <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                                    'data' => EducationYear::getEducationYears(),
                                    'allowClear' => false,
                                ]) ?>
                            </div>

                            <div class="col col-md-4">
                                <?= $form->field($model, '_education_form')->widget(Select2Default::classname(), [
                                    'data' => EducationForm::getClassifierOptions(),
                                    'allowClear' => false,
                                ]) ?>
                            </div>
                            <div class="col col-md-4">
                                <?= $form->field($model, '_marking_system')->widget(Select2Default::classname(), [
                                    'data' => MarkingSystem::getOptions(),
                                    'allowClear' => false,
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-md-6">
                                <?= $form->field($model, 'education_period')->textInput(['id' => 'education_period']) ?>
                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($model, 'semester_count')->textInput(['id' => 'semester_count']) ?>
                            </div>
                        </div>
                        <?php if($model->isNewRecord): ?>
                        <div class="row">
                            <div class="col-md-6">


                                <?= $form->field($model, 'autumn_start_date')->widget(DatePicker::classname(), [
                                    'type' => DatePicker::TYPE_RANGE,
                                    'attribute' => 'autumn_start_date',
                                    'attribute2' => 'autumn_end_date',
                                    'options' => ['placeholder' => $model->getAttributeLabel('autumn_start_date'), 'disabled' => !$model->isNewRecord],
                                    'options2' => ['placeholder' => $model->getAttributeLabel('autumn_end_date'), 'disabled' => !$model->isNewRecord],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'weekStart' => '1',
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true,

                                    ]
                                ])->label(__('Autumn Period')); ?>
                            </div>

                            <div class="col-md-6">
                                <?= $form->field($model, 'spring_start_date')->widget(DatePicker::classname(), [
                                    'type' => DatePicker::TYPE_RANGE,
                                    'attribute' => 'spring_start_date',
                                    'attribute2' => 'spring_end_date',
                                    'options' => ['placeholder' => $model->getAttributeLabel('spring_start_date'), 'disabled' => !$model->isNewRecord],
                                    'options2' => ['placeholder' => $model->getAttributeLabel('spring_end_date'), 'disabled' => !$model->isNewRecord],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'weekStart' => '1',
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true
                                    ]
                                ])->label(__('Spring Period')); ?>
                            </div>
                        </div>
                        <?php endif;?>


                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['curriculum/curriculum-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>

</div>
<?php ActiveForm::end(); ?>

<?php
$message = json_encode([__('Count of semester is not divided to education years.')]);
$script = <<< JS
    $(function() {
        $('#semester_count').on('change', function () {
            var education_period = $("#education_period").val();
            if($(this).val() / education_period !=2){
                alert({$message}[0]);
                $(this).addClass('has-error');
                $(this).val('');
                $(this).attr('required', 'true');
            }
            else{
                 $(this).parent().removeClass('has-error');
                $(this).nextAll('div').remove();
            }
        });
    });
              
JS;
$this->registerJs($script);
?>

<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
