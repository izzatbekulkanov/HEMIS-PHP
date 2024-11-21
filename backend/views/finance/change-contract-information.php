<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;
use common\models\finance\EStudentContractType;
use common\models\system\classifier\ContractType;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\finance\EStudentContract;
use common\models\student\EGroup;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title =  __('View Contract Information') ;
$this->params['breadcrumbs'][] = [
    'url' => ['finance/student-contract'],
    'label' => __('Student Contract'),
];

$this->params['breadcrumbs'][] = $selected->student->fullName;
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initContractForm()");
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
<?php echo $form->errorSummary($selected)?>
<?php echo $form->field($selected, '_curriculum')->hiddenInput(['id'=>'_curriculum'])->label(false);?>
    <div class="row">
        <div class="col col-md-12">
            <div class="box box-default ">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($selected, 'number')->textInput([
                                'maxlength' => true,
                                'id' => 'number',
                            //    'disabled' => ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE),
                            //    'readonly' => ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE),
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'date')->widget(DatePickerDefault::classname(), [
                                'options' => [
                                    'placeholder' => __('YYYY-MM-DD'),
                                    'id' => 'date',
                              //      'disabled' => ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE),
                              //      'readonly' => ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE),
                                ],
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'summa')->textInput(
                                    [
                                        'maxlength' => true,
                                        'id' => 'summa',
                                        'disabled' => true,
                                        'readonly' => true,
                                        'value' => ($selected->summa > 0) ? Yii::$app->formatter->asCurrency($selected->summa) : "",
                                    ]) ?>
                        </div>
                        <div class="col-md-3" id="discount_attributes">
                            <?= $form->field($selected, 'discount')->textInput(['maxlength' => true, 'id' => 'discount', /*'disabled'=>!$selected->isBaseContractType() || (!$selected->isEducationFormDayly() && !$selected->isEducationFormSecondHigherDayly())*/]) ?>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($selected, '_contract_type')->widget(Select2Default::classname(), [
                                'data' => \common\models\system\classifier\ContractType::getClassifierOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_contract_type',
                                ],
                                // 'disabled' => true,
                                //'readonly' => true,

                            ]) ?>


                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, '_contract_summa_type')->widget(Select2Default::classname(), [
                                'data' => \common\models\system\classifier\ContractSummaType::getClassifierOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                //'disabled' => ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING),
                               // 'readonly' => true,
                                'options' => [
                                    'id' => '_contract_summa_type',
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'contract_form_type')->widget(Select2Default::classname(), [
                                'data' => EStudentContractType::getContractFormOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,

                            ]) ?>


                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'month_count')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => EStudentContract::getMonthCountOptions(),
                                    'allowClear' => false,
                                    'placeholder' => false,
                                    'options' => [
                                        'id' => 'month_count',
                                    ],
                                ]
                            ); ?>
                        </div>

                        <? /*<div class="col-md-3" id="discount_attributes" style="display: <?= !$selected->isBaseContractType() ? 'none' : 'block' ?>"> */?>


                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?php
                            $education_years = [];
                            if ($selected->_curriculum) {
                                $education_years = Semester::getSemesterByCurriculum($selected->_curriculum);
                            }
                            ?>
                            <?= $form->field($selected, '_education_year')->widget(Select2Default::classname(), [
                                //'data' => EducationYear::getEducationYears(),
                                'data' => ArrayHelper::map($education_years, '_education_year', 'educationYear.name'),
                                'allowClear' => false,
                                'hideSearch' => false,
                               // 'disabled' => true,
                               // 'readonly' => true,
                                'options' => [
                                    'id' => '_education_year',
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-3">
                            <?php
                            /*$levels = [];
                            if ($selected->_education_year) {
                                $levels = Semester::getCourseOptions($selected->_curriculum, $selected->_education_year);
                            }*/
                            ?>
                            <?= $form->field($selected, '_level')->widget(Select2Default::classname(), [
                                //'data' => EducationYear::getEducationYears(),
                                'data' => ArrayHelper::map ($education_years,'_level', 'level.name'),
                                'allowClear' => false,
                                'hideSearch' => false,
                                // 'disabled' => true,
                                // 'readonly' => true,
                                'options' => [
                                    'id' => '_level',
                                ],
                            ]) ?>

                            <?/*= $form->field($selected, '_level')->widget(DepDrop::classname(), [
                                'data' => ArrayHelper::map ($education_years,'_level', 'level.name'),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => true,
                                'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'id' => '_level',
                                   // 'disabled' => true,
                                   // 'readonly' => true,
                                    'placeholder' => __('-Choose Level-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_curriculum','_education_year'],
                                    'url' => Url::to(['/ajax/get_courses']),
                                    'placeholder' => __('-Choose Level-'),
                                ],
                            ]); */?>


                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, '_graduate_type')->widget(Select2Default::classname(), [
                                'data' => EStudentContract::getGraduateTypeOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_graduate_type',
                                ],
                            ]) ?>


                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'education_period')->widget(Select2Default::classname(), [
                                'data' => EStudentContract::getEducationPeriodOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => 'education_period',
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($selected, '_department')->widget(Select2Default::classname(), [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => false,
                                'hideSearch' => false,
                                //'disabled' => true,
                                //'readonly' => true,
                                'options' => [
                                    'id' => '_department',
                                   // 'disabled' => true,
                                   // 'readonly' => true,
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-6">
                            <?php
                            $specialties = array();
                            if ($selected->_department) {
                                $specialties = ESpecialty::getHigherSpecialtyByType($selected->_education_type, $selected->_department);
                            }

                            ?>
                            <?= $form->field($selected, '_specialty')->widget(DepDrop::classname(), [
                                'data' => $specialties,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'id' => '_specialty',
                                //    'disabled' => true,
                                  //  'readonly' => true,
                                    'placeholder' => __('-Choose Specialty-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_department'],
                                    'url' => Url::to(['/ajax/get_specialty']),
                                    'placeholder' => __('-Choose Specialty-'),
                                ],
                            ]); ?>


                        </div>
                        <div class="col-md-3">
                            <?php
                            $groups = array();
                            if ($selected->_department && $selected->_specialty) {
                                $specialties = EGroup::getOptionsByFacultyEduForm($selected->_department, $selected->_specialty, $selected->_education_form);
                            }

                            ?>
                            <?= $form->field($selected, '_group')->widget(DepDrop::classname(), [
                                'data' => $specialties,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'id' => '_group',
                                    //    'disabled' => true,
                                    //  'readonly' => true,
                                    'placeholder' => __('-Choose Group-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_department', '_specialty', '_education_form'],
                                    'url' => Url::to(['/ajax/get-group-by-specialty-edu-form']),
                                    'placeholder' => __('-Choose Group-'),
                                ],
                            ]); ?>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($selected, '_education_type')->widget(
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
                            <?= $form->field($selected, '_education_form')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => EducationForm::getClassifierOptions(),
                                    'allowClear' => false,
                                    'placeholder' => false,
                                    'disabled' => true,
                                    'readonly' => true,
                                    'options' => [
                                        'id' => '_education_form',
                                    ],
                                ]
                            ); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'start_date')->widget(DatePickerDefault::classname(), [
                                'options' => [
                                    'placeholder' => __('YYYY-MM-DD'),
                                    'id' => 'start_date',
                                    'value' => $selected->start_date === null ? date('Y') . '-09-15' : Yii::$app->formatter->asDate($selected->start_date, 'php:Y-m-d'),
                                ],
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($selected, 'end_date')->widget(DatePickerDefault::classname(), [
                                'options' => [
                                    'placeholder' => __('YYYY-MM-DD'),
                                    'id' => 'end_date',
                                    'value' => $selected->end_date === null ? date('Y') . '-10-01' : Yii::$app->formatter->asDate($selected->end_date, 'php:Y-m-d'),
                                ],
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($selected, 'mailing_address')->textarea(['maxlength' => true, 'rows' => 4, 'value'=>($selected->mailing_address =="") ? $univer->mailing_address :$selected->mailing_address ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($selected, 'bank_details')->textarea(['maxlength' => true, 'rows' => 4, 'value'=>($selected->bank_details =="") ? $univer->bank_details :$selected->bank_details]) ?>
                        </div>

                </div>
            </div>
            <div class="box-footer text-right">
                <?= $this->getResourceLink(__('Cancel'), ['finance/student-contract'], ['class' => 'btn btn-default btn-flat']) ?>
                <?php if ($selected->contract_status == EStudentContractType::CONTRACT_REQUEST_STATUS_SEND): ?>
                    <?= $this->getResourceLink(__('Delete'), ['finance/student-contract', 'code' => $selected->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton(
                    '<i class="fa fa-check"></i> ' . __('Save'),
                    ['class' => 'btn btn-primary btn-flat']
                ) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>

<script>
    function initContractForm() {
        $('#_contract_type').change(function () {
            initContractType();
        });
        initContractType();
    }

    function initContractType() {
        var id = parseInt($('#_contract_type').val());
        if (id === <?=ContractType::CONTRACT_TYPE_BASE?> || id === <?=ContractType::CONTRACT_TYPE_RECOMMEND?> || id === <?=ContractType::CONTRACT_TYPE_FOREIGN?>) {
            //$("#discount_attributes").show();
            $("#number").attr('disabled', true);

            $("#date").attr('disabled', true);
            $("#month_count").attr('disabled', false);
            $("#_graduate_type").attr('disabled', false);

        }
        else {
            //$("#discount_attributes").hide();
            $("#number").attr('disabled', false);
            $("#date").attr('disabled', false);
            $("#month_count").attr('disabled', true);
            $("#_graduate_type").attr('disabled', true);
            $("#number").attr('required', true);
            $("#date").attr('required', true);

        }
    }

</script>
