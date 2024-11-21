<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;
use kartik\select2\Select2Asset;
use yii\grid\SerialColumn;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\MarkingSystem;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */
$this->registerAssetBundle(Select2Asset::class);
$this->title =  __('View Academic Information') ;
$this->params['breadcrumbs'][] = [
    'url' => ['archive/academic-information'],
    'label' => __('Academic Information'),
];
$this->params['breadcrumbs'][] = $this->title;

$user = $this->context->_user();

?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
<?php echo $form->errorSummary($model)?>
    <div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body no-padding">
                <?= DetailView::widget(
                    [
                        'model' => $student,
                        'attributes' => [
                            [
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => static function ($data) {
                                    return $data->student->fullName;
                                },
                            ],
                            [
                                'attribute' => 'student.birth_date',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Yii::$app->formatter->asDate($data->student->birth_date, 'dd.MM.Y');
                                },
                            ],
                            [
                                'attribute' => '_education_type',
                                'value' => static function ($data) {
                                    return $data->educationType->name;
                                },
                            ],
                            [
                                'attribute' => '_department',
                                'value' => static function ($data) {
                                    return $data->department ? $data->department->name : '';
                                },
                            ],
                            [
                                'attribute' => '_specialty_id',
                                'value' => static function ($data) {
                                    return $data->specialty->mainSpecialty ? $data->specialty->mainSpecialty->name : $data->specialty->name;
                                },
                            ],
                            [
                                'attribute' => 'year_of_enter',
                                'value' => static function ($data) {
                                    return $data->student->year_of_enter;
                                },
                            ],
                            [
                                'attribute' => '_student_status',
                                'value' => static function ($data) {
                                    return $data->studentStatus->name;
                                },
                            ],
                            [
                                'attribute' => '_education_form',
                                'value' => static function ($data) {
                                    return $data->educationForm->name;
                                },
                            ],

                            [
                                'attribute' => '_marking_system',
                                'label' => __('Marking System'),
                                'value' => static function ($data) {
                                    return $data->curriculum->markingSystem->name;
                                },
                            ],
                        ],
                    ]
                ); ?>
            </div>
    <div class="box-body">

            <?php if($model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT):?>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'academic_register_date')->widget(DatePickerDefault::classname(), [
                        'options' => [
                            'placeholder' => __('YYYY-MM-DD'),
                            'id' => 'academic_register_date',

                        ],
                    ]); ?>
                </div>
            </div>
            <?php else:?>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'academic_number')->textInput(['maxlength' => true, 'id' => 'academic_number']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'academic_register_number')->textInput(['maxlength' => true, 'id' => 'academic_number']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'academic_register_date')->widget(DatePickerDefault::classname(), [
                        'options' => [
                            'placeholder' => __('YYYY-MM-DD'),
                            'id' => 'academic_register_date',

                        ],
                    ]); ?>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'rector')->textInput(['maxlength' => true, 'id' => 'rector']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'dean')->textInput(['maxlength' => true, 'id' => 'dean']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'secretary')->textInput(['maxlength' => true, 'id' => 'dean']) ?>
                    </div>
                </div>
            <?php endif;?>


<?php /*
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($selected, 'number')->textInput(['maxlength' => true, 'id' => 'number', 'disabled' => true,'readonly' => true,]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($selected, 'date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'id' => 'date',
                                'disabled' => true,
                                'readonly' => true,
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($selected, 'summa')->textInput(['maxlength' => true, 'id' => 'summa', 'disabled' => true,'readonly' => true,]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($selected, '_education_year')->widget(Select2Default::classname(), [
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
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($selected, '_contract_summa_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\ContractSummaType::getClassifierOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            // 'disabled' => true,
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
                        <?= $form->field($selected, '_contract_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\ContractType::getClassifierOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            // 'disabled' => true,
                            //'readonly' => true,

                        ]) ?>


                    </div>
                    <div class="col-md-3">
                        <?= $form->field($selected, 'contract_status')->widget(Select2Default::classname(), [
                            'data' => \common\models\finance\EStudentContractType::getContractStatusOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => true,
                            'readonly' => true,
                        ]) ?>


                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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

                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($selected, 'mailing_address')->textarea(['maxlength' => true, 'rows' => 4,]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($selected, 'bank_details')->textarea(['maxlength' => true, 'rows' => 4,]) ?>
                    </div>

                </div>
            </div>
             */ ?>
            <div class="box-footer text-right">
                <?= $this->getResourceLink(__('Cancel'), ['archive/academic-information'], ['class' => 'btn btn-default btn-flat']) ?>
                <?= Html::submitButton(
                    '<i class="fa fa-check"></i> ' . __('Create'),
                    ['class' => 'btn btn-primary btn-flat']
                ) ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>