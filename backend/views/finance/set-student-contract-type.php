<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\ContractSummaType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\grid\SerialColumn;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\curriculum\Semester;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EStudentSubject;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\ContractType;
use common\models\structure\EDepartment;
use common\models\finance\EStudentContractType;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;
use common\models\finance\EStudentContract;
use common\models\system\classifier\CitizenshipType;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['url' => ['finance/student-contract'], 'label' => __('Finance Student Contract')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-primary ">
            <div class="box-header bg-gray with-border">
                <h3 class="box-title"><?= __('Students in Group') ?></h3>
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getFaculties(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $faculty != null,
                            'options' => [
                                'id' => '_department',

                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?php
                        $specialties = array();
                        if ($searchModel->_department) {
                            $specialties = ESpecialty::getHigherSpecialty($searchModel->_department);
                        }
                        if ($faculty) {
                            $specialties = ESpecialty::getHigherSpecialty($faculty);
                        }
                        ?>
                        <?= $form->field($searchModel, '_specialty_id')->widget(DepDrop::classname(), [
                            'data' => $specialties,
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_specialty',
                                'placeholder' => __('-Choose Specialty-'),
                            ],
                            'pluginOptions' => [
                                'depends' => ['_department'],
                                'url' => Url::to(['/ajax/get_specialty']),
                                'placeholder' => __('-Choose Specialty-'),
                            ],
                        ])->label(false); ?>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_education_form')->widget(Select2Default::class, [
                                'data' => EducationForm::getClassifierOptions(),
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_education_form',
                                    'required' => true,
                                ]
                            ])->label(false);; ?>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                                'data' => EducationYear::getEducationYears(),
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_education_year',
                                    'required' => true,
                                ]
                            ])->label(false);; ?>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_semestr')->widget(Select2Default::class, [
                                'data' => common\models\system\classifier\Semester::getClassifierOptions(),
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_semestr',
                                    'required' => true,
                                ]
                            ])->label(false);; ?>
                        </div>
                        <div class="col col-md-3">
                        <?php
                        $groups = array();
                        if ($searchModel->_department && $searchModel->_specialty_id && $searchModel->_education_form) {
                            $groups = EGroup::getOptionsByFacultyEduForm($searchModel->_department, $searchModel->_specialty_id, $searchModel->_education_form);
                        }
                        ?>
                        <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                            'data' => $groups,
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_group',
                                'placeholder' => __('-Choose Group-'),
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'depends' => ['_department', '_specialty', '_education_form'],
                                'url' => Url::to(['/ajax/get-group-by-specialty-edu-form']),
                                'required' => true
                            ],
                        ])->label(false); ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>


            <?= GridView::widget([
                'id' => 'data-grid',
                'dataProvider' => $dataProvider,
                'columns' => [
                    /*[
                        '__class' => 'yii\grid\CheckboxColumn',
                    ],*/
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function($model, $key, $index, $column) {
                            //$exist = EStudentContractType::getContractType($model->_specialty_id, $model->_student, $model->_education_form);
                            $exist = EStudentContract::getContract($model->_specialty_id, $model->_student, $model->_education_form, $model->_education_year);

                            if($exist !== null) {
                                $onclick = "return false;";
                                $display = "none";
                            }
                            else {
                                $onclick = "return true;";
                                $display = "block";
                            }
                            return ['style' => ['display' => $display]];
                        }

                    ],

                    [
                        'attribute' => '_student',
                        'value' => 'student.fullName',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return sprintf("%s<p class='text-muted'> %s / %s</p>", $data->student->fullName, $data->educationForm->name, $data->student->studentType->name);
                        },
                    ],
                    [
                        'attribute' => '_specialty_id',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return sprintf("%s<p class='text-muted'> %s</p>", $data->specialty->code, $data->paymentForm->name);
                        },
                    ],
                    [
                        'attribute' => 'student._citizenship',
                        'value' => 'student.citizenship.name',
                    ],

                    [
                        'attribute' => '_contract_type',
                        'header' => __('Contract Type'),
                        'filterInputOptions' => [
                            'class' => 'form-control',
                        ],
                        'format' => 'raw',
                        'value' => function ($data) {
                            $list = [];
                            if($data->student->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_FOREIGN)
                                $list = ContractType::getForeignOptions();
                            else
                                $list = ContractType::getClassifierOptions();
                                return Html::dropDownList('_contract_type['.$data->_student.']', $data->_contract_type, $list);
                        }
                    ],
                    [
                        'attribute' => 'contract_form_type',
                        'header' => __('Contract Form Type'),
                        'filterInputOptions' => [
                            'class' => 'form-control',
                        ],
                        'format' => 'raw',
                        'value' => function ($data) {
                            $list = [];
                            if($data->student->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_FOREIGN)
                                $list = EStudentContractType::getContractFormBileteralOptions();
                            else
                                $list = EStudentContractType::getContractFormOptions();

                        return Html::dropDownList('contract_form_type['.$data->_student.']', $data->contract_form_type, $list);
                        }
                    ],

                    [
                        'attribute' => '_contract_summa_type',
                        'header' => __('Contract Summa Type'),
                        'filterInputOptions' => [
                            'class' => 'form-control',
                            //'prompt' => 'Select'
                        ],
                        'format' => 'raw',
                        'value' => function ($data) {
                            if($data->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $data->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $data->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $data->_education_form == EducationForm::EDUCATION_FORM_EVENING)
                                $list = ContractSummaType::getClassifierOtherOptions();
                            elseif($data->_education_form == EducationForm::EDUCATION_FORM_DAYLY || $data->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_DAYLY)
                                $list = ContractSummaType::getSortClassifierOptions();
                            else
                                $list = ContractSummaType::getClassifierOptions();

                            if($data->student->_citizenship == CitizenshipType::CITIZENSHIP_TYPE_FOREIGN)
                                $list = ContractSummaType::getClassifierOtherOptions();


                            return Html::dropDownList('_contract_summa_type['.$data->_student.']', $data->_contract_summa_type, $list);
                        }

                    ],
                ],
            ]); ?>
        </div>
        <div class="box-footer text-right">
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Assign'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign']) ?>
        </div>

    </div>


</div>

<?php
$script = <<< JS
	$("#assign").click(function(){
	    var keys = $('#data-grid').yiiGridView('getSelectedRows');
		var contract_summa_type = $('[name*=\'_contract_summa_type\']').serialize();
		var contract_type = $('[name*=\'_contract_type\']').serialize();
		var contract_form_type = $('[name*=\'contract_form_type\']').serialize();
		var department =  $('#_department').val();
		var specialty =  $('#_specialty').val();
		var education_year =  $('#_education_year').val();
		var education_form =  $('#_education_form').val();
		var group =  $('#_group').val();
		if(keys.length&&contract_summa_type.length&&contract_type.length&&contract_form_type.length&&department&&specialty&&education_year&&education_form&&group)
		$.post({
           url:  '/finance/to-set-student-contract-type',
           data: {selection: keys, contract_summa_type: contract_summa_type, contract_type: contract_type, contract_form_type: contract_form_type, department: department, specialty: specialty, education_year: education_year, education_form: education_form, group: group},
           dataType:"json",
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
<?php Pjax::end() ?>