<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;
use common\models\finance\EStudentContractType;
use common\models\finance\EPaidContractFee;
use common\models\system\classifier\ContractType;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title =  __('View Contract Information') ;
$this->params['breadcrumbs'][] = [
    'url' => ['finance/student-contract-manual'],
    'label' => __('Student Contract Manual'),
];

$this->params['breadcrumbs'][] = $selected->student->fullName;
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
//$this->registerJs("initContractForm()");
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
<?php echo $form->errorSummary($selected)?>
<?php echo $form->field($selected, '_curriculum')->hiddenInput(['id'=>'_curriculum'])->label(false);?>


<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('Contract Information') ?></h3>
    </div>
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
                       // 'disabled' => true,
                      //  'readonly' => true,
                 //       'value' => ($selected->summa > 0) ? Yii::$app->formatter->asCurrency($selected->summa) : "",
                    ]) ?>
            </div>
            <div class="col-md-3" id="discount_attributes">
                <?= $form->field($selected, 'discount')->textInput(['maxlength' => true, 'id' => 'discount', 'disabled'=>true]) ?>
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
                    //'disabled' => ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME),
                    // 'readonly' => true,
                    'options' => [
                        'id' => '_contract_summa_type',
                    ],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($selected, 'contract_form_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\finance\EStudentContractType::getContractFormOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,

                ]) ?>


            </div>
            <div class="col-md-3">
                <?= $form->field($selected, 'month_count')->widget(
                    Select2Default::classname(),
                    [
                        'data' => \common\models\finance\EStudentContract::getMonthCountOptions(),
                        'allowClear' => false,
                        'placeholder' => false,
                        'disabled' => true

                    ]
                ); ?>
            </div>

            <? /*<div class="col-md-3" id="discount_attributes" style="display: <?= !$selected->isBaseContractType() ? 'none' : 'block' ?>"> */?>


        </div>

    </div>
</div>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('Education information') ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($selected, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => true,
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
                $levels = [];
                if ($selected->_education_year) {
                    $levels = Semester::getCourseOptions($selected->_curriculum, $selected->_education_year);
                }
                ?>
                <?= $form->field($selected, '_level')->widget(DepDrop::classname(), [
                    'data' => \yii\helpers\ArrayHelper::map ($levels,'_level', 'level.name'),
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
                ]); ?>


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
                    ]
                ); ?>
            </div>
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



        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($selected, '_department')->widget(Select2Default::classname(), [
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
                if ($selected->_department) {
                    $specialties = ESpecialty::getHigherSpecialty($selected->_department);
                }

                ?>
                <?= $form->field($selected, '_specialty')->widget(DepDrop::classname(), [
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
    </div>
</div>
<?php if(!$selected->isNewRecord):?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <div class="col col-md-6">
                <h3 class="box-title"><?= __('Payment Information') ?></h3>
            </div>

            <div class="col col-md-6">
                <div class="pull-right">
                    <?=
                    Html::a(__('Add Payment'), '#', [
                        'class' => 'showModalButton btn btn-flat btn-success',
                        'modal-class' => 'modal-md',
                        'title' => __('Add Payment').' / '.$selected->number,
                        'value' => Url::to(['finance/student-contract-manual-edit',
                            'contract' => $selected->id,
                            'payment' => 1,
                        ]),
                        'data-pjax' => 0
                    ]);
                    ?>
                </div>
            </div>


        </div>

    </div>
    <div class="box-body">

        <div class="row">
            <div class="col col-md-12 col-lg-12">
                <div class="box box-default ">
                    <div class="box-body no-padding">
                        <?= GridView::widget([
                            'id' => 'data-grid',
                            'toggleAttribute' => 'active',
                            'showFooter' => true,
                            'footerRowOptions'=>['style'=>'font-weight:bold;',],
                            'dataProvider' => $contractFeeDataProvider,
                            'columns' => [
                                [
                                    'attribute' => 'payment_number',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Html::a($data->payment_number, '#', [
                                            'class' => 'showModalButton ',
                                            'modal-class' => 'modal-md',
                                            'title' => $data->payment_number.' / '.$data->studentContract->number,
                                            'value' => Url::to(['finance/student-contract-manual-edit',
                                                'contract' => $data->_student_contract,
                                                'code' => $data->id,
                                                'payment' => 1,
                                            ]),
                                            'data-pjax' => 0
                                        ]);
                                    },

                                    'footer' => __('Summary'),
                                ],
                                [
                                    'attribute' => 'payment_date',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Yii::$app->formatter->asDate($data->payment_date->getTimestamp());
                                    },
                                ],
                                [
                                    'attribute' => 'summa',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Html::a(Yii::$app->formatter->asCurrency($data->summa), '#', [
                                            'class' => 'showModalButton ',
                                            'modal-class' => 'modal-md',
                                            'title' => $data->payment_number.' / '.$data->studentContract->number,
                                            'value' => Url::to(['finance/student-contract-manual-edit',
                                                'contract' => $data->_student_contract,
                                                'code' => $data->id,
                                                'payment' => 1,
                                            ]),
                                            'data-pjax' => 0
                                        ]);
                                    },
                                    'footer' => Yii::$app->formatter->asCurrency(EPaidContractFee::getTotal($contractFeeDataProvider->models, 'summa')),
                                ],


                                [
                                    'attribute' => 'updated_at',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                                    },
                                ]
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
<?php endif;?>

<div class="box-footer text-right">
    <?= $this->getResourceLink(__('Cancel'), ['finance/student-contract-manual'], ['class' => 'btn btn-default btn-flat']) ?>
    <?= $this->getResourceLink(__('Delete'), ['finance/student-contract-manual', 'code' => $selected->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
    <?= Html::submitButton(
        '<i class="fa fa-check"></i> ' . __('Save'),
        ['class' => 'btn btn-primary btn-flat']
    ) ?>
</div>

<?php ActiveForm::end(); ?>