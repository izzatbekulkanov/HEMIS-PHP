<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\DecreeType;
use common\models\academic\EDecree;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\grid\SerialColumn;
use common\models\student\EGroup;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationType;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\StipendRate;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;
use kartik\date\DatePicker;
use common\models\finance\EStudentScholarship;
use common\models\finance\EStudentContract;
use common\models\system\classifier\ContractSummaType;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['url' => ['finance/scholarship'], 'label' => __('Finance Scholarship')];
$this->params['breadcrumbs'][] = $this->title;
//$this->params['breadcrumbs'][] = $type_model->name.' => '.$this->title;

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row">
        <div class="col col-md-12 col-lg-12">
            <div class="box box-primary ">
                <div class="box-header bg-gray with-border">
                    <h3 class="box-title"><?= __('Students in Group') ?></h3>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>

                        <div class="col col-md-4">

                                <?= $form->field($searchModel, '_education_type')->widget(Select2Default::class, [
                                    'data' => EducationType::getHighers(),
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => '_education_type',
                                        'required' => true,
                                    ]
                                ])->label(false); ?>
                                <?php echo $form->field($searchModel, '_education_form')->hiddenInput(['value'=>EducationForm::EDUCATION_FORM_DAYLY, 'id'=>'_education_form'])->label(false);?>

                        </div>

                        <div class="col col-md-4">
                            <?php
                            $curriculums = array();
                            if ($searchModel->_education_type) {
                                $curriculums = ECurriculum::getOptionsByEduTypeForm($searchModel->_education_type, [EducationForm::EDUCATION_FORM_DAYLY, EducationForm::EDUCATION_FORM_SECOND_HIGHER_DAYLY], $faculty);
                            }

                            ?>
                            <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::class, [
                                'data' => $curriculums,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_curriculum',
                                    'required' => true,
                                ]
                            ])->label(false); ?>

                            <?/*= $form->field($searchModel, '_curriculum')->widget(DepDrop::classname(), [
                                'data' => $curriculums,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'id' => '_curriculum',
                                    'placeholder' => __('-Choose Curriculum-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_education_type', '_education_form'],
                                    'url' => Url::to(['/ajax/get-curriculums']),
                                    'placeholder' => __('-Choose Curriculum-'),
                                ],
                            ])->label(false); */?>
                        </div>
                        <div class="col col-md-4">
                            <?php
                            $groups = array();
                            if ($searchModel->_curriculum) {
                                $groups = EGroup::getOptions($searchModel->_curriculum);
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
                                    'depends' => ['_curriculum'],
                                    'url' => Url::to(['/ajax/get-group-by-curruculum']),
                                    'required' => true
                                ],
                            ])->label(false); ?>
                        </div>

                        <div class="col col-md-4">
                            <?php
                            $semesters = array();
                            if ($searchModel->_curriculum) {
                                $semesters = Semester::getSemesterByCurriculum($searchModel->_curriculum);
                            }
                            ?>
                            <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                                'data' => ArrayHelper::map($semesters,'code', 'name'),
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_semester'
                                ],
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_payment_form')->widget(Select2Default::class, [
                                'data' => PaymentForm::getClassifierOptions(),
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_payment_form',
                                    'required' => true,
                                ]
                            ])->label(false);; ?>
                        </div>
                        <div class="col col-md-4">
                            <?php
                            $decrees = EDecree::getOptionsByCurriculum($this->_user(), $searchModel->_curriculum ? $searchModel->curriculum : null, DecreeType::TYPE_SCHOLARSHIP);
                            ?>
                            <?= $form->field($searchModel, '_decree')->widget(Select2Default::class, [
                                'data' => $decrees['options'],
                                'options' => [
                                    'id' => '_decree',
                                    'required' => true,
                                    //'onchange' => 'decreeChanged(this.value)'
                                ],
                                'disabled' => $searchModel->_group == null,
                                'hideSearch' => false,
                                'allowClear' => false,
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
                                $exist = EStudentScholarship::getScholarByStudent($model->_student, $model->_semestr, $model->_education_year);
                                $contract = EStudentContract::getContract($model->_specialty_id, $model->_student, $model->_education_form, $model->_education_year);
                                if($exist !== null) {
                                    $onclick = "return false;";
                                    $display = "none";
                                }
                                elseif($contract !== null) {
                                    if($contract->_contract_summa_type  == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF){
                                        $onclick = "return false;";
                                        $display = "none";
                                    }
                                    elseif($contract->_contract_summa_type  == ContractSummaType::CONTRACT_SUMMA_TYPE_ON){
                                        $onclick = "return true;";
                                        $display = "block";
                                    }
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

                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width:25%'],
                            'value' => function ($data) {
                                return sprintf("%s<p class='text-muted'> %s / %s / %s / %s</p>",
                                    $data->student->fullName,
                                    $data->educationType->name,
                                    $data->specialty->code,
                                    $data->paymentForm->name,
                                    $data->student->socialCategory->name
                                );
                            },
                        ],
                        [
                            'attribute' => '_group',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width:15%'],
                            'value' => function ($data) {
                                return sprintf("%s<p class='text-muted'> %s / %s </p>", $data->group->name, Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr)->name, $data->educationYear->name);
                            },
                        ],
                        /*[
                            'attribute' => 'student._social_category',
                            'value' => 'student.socialCategory.name',
                        ],*/

                        [
                            'attribute' => '_stipend_rate',
                            'header' => __('Stipend Rate'),
                            'filterInputOptions' => [
                                'class' => 'form-control',
                            ],
                            'headerOptions' => ['style' => 'text-align:center;'],
                            'contentOptions' => ['style' => 'width:14%'],
                            'format' => 'raw',
                            'value' => function ($data) {
                                $options = [];
                                /*if($type_model->code == StipendRate::STIPEND_RATE_BASE)
                                    $options = StipendRate::getBaseStatusOptions();
                                elseif($type_model->code == StipendRate::STIPEND_RATE_FAMOUS)
                                    $options = StipendRate::getFamousStatusOptions();
                                elseif($type_model->code == StipendRate::STIPEND_RATE_INVALID)
                                    $options = StipendRate::getInvalidStatusOptions();
                                elseif($type_model->code == StipendRate::STIPEND_RATE_ORPHANAGE)
                                    $options = StipendRate::getOrphanageStatusOptions();*/
                                $options = StipendRate::getClassifierSpecialOptions(StipendRate::STIPEND_RATE_OTHER);
                                return Html::dropDownList('_stipend_rate['.$data->_student.']', $data->_stipend_rate, $options, ['class' => 'form-control']);
                            }
                        ],
                        [
                            'header' => __('Academic Debt'),
                            'headerOptions' => ['style' => 'text-align:center'],
                            'contentOptions' => ['style' => 'text-align:center'],
                            'value' => function ($data) {
                                return $data->calculateAcademicDebt() ? $data->calculateAcademicDebt()['debt_subjects'] : "-";
                            },
                        ],
                        [
                            'header' => __('Contract Debt'),
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'text-align:center'],
                            'contentOptions' => ['style' => 'text-align:center'],
                            'value' => function ($data) {
                                if($data->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT)
                                    return $data->calculateContractDebt() ? $data->calculateContractDebt()['result'] : "-";
                                else
                                    return __('No Contract Debt');
                            },
                        ],
                        [
                            'header' => __('Grade'),
                            'headerOptions' => ['style' => 'text-align:center'],
                            'contentOptions' => ['style' => 'text-align:center'],
                            'value' => function ($data) {
                                return $data->calculateAcademicDebt() ? $data->calculateAcademicDebt()['rating'] : "-";
                            },
                        ],
                        [
                            'header' => __('Satisfactory Amount'),
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'text-align:center'],
                            'contentOptions' => ['style' => 'text-align:center'],
                            'value' => function ($data) {
                                return $data->calculateAcademicDebt() ? $data->calculateAcademicDebt()['satisfactory_amount']: '-';
                            },
                        ],
                        [
                            'attribute' => '_stipend_rate',
                            'header' => __('Stipend Period'),
                            'filterInputOptions' => [
                                'class' => 'form-control',
                            ],
                            'format' => 'raw',
                            'value' => function ($data) {
                                $semestr = Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr);
                                return DatePicker::widget([
                                    'name' => 'start_date['.$data->_student.']',
                                    'value'=> Yii::$app->formatter->asDatetime($semestr->start_date->getTimestamp(), 'php:Y-m-d'),

                                    'type' => DatePicker::TYPE_RANGE,
                                    'name2' => 'end_date['.$data->_student.']',
                                    'value2'=> Yii::$app->formatter->asDatetime($semestr->end_date->getTimestamp(), 'php:Y-m-d'),

                                    'pluginOptions' => [
                                        'autoclose'=>true,
                                        'format' => 'yyyy-mm-dd',
                                        'placeholder' => __('YYYY-MM-DD'),
                                    ]
                                ]);
                            }
                        ],
                    ],
                ]); ?>

            </div>

<br>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Assign'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign']) ?>
            </div>
        </div>


    </div>

<?php
$script = <<< JS
	$("#assign").click(function(){
	    var keys = $('#data-grid').yiiGridView('getSelectedRows');
		var stipend_rate = $('[name*=\'_stipend_rate\']').serialize();
		var start_date = $('[name*=\'start_date\']').serialize();
		var end_date = $('[name*=\'end_date\']').serialize();
		var education_type =  $('#_education_type').val();
		var curriculum =  $('#_curriculum').val();
		var group =  $('#_group').val();
		var semester =  $('#_semester').val();
		var decree =  $('#_decree').val();
		if(keys.length&&stipend_rate.length&&start_date.length&&end_date.length&&education_type&&curriculum&&group&&semester&&decree)
		$.post({
           url:  '/finance/to-set-scholarship',
           data: {selection: keys, stipend_rate: stipend_rate, start_date: start_date, end_date: end_date, education_type: education_type, curriculum: curriculum, group: group, semester: semester, decree: decree},
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