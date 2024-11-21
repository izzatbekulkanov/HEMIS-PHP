<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;
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
use common\models\curriculum\ECurriculum;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use backend\widgets\GridView;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title =  __('View Scholarship Information') ;
$this->params['breadcrumbs'][] = [
    'url' => ['finance/scholarship'],
    'label' => __('Finance Scholarship'),
];
$this->params['breadcrumbs'][] = $this->title;

$user = $this->context->_user();
\kartik\date\DatePickerAsset::registerBundle($this, '3.x');
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
<?php echo $form->errorSummary($meta)?>
    <div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($meta, '_student')->textInput(['maxlength' => true, 'id' => 'number', 'disabled' => true,'readonly' => true, 'value'=>$meta->student->fullName]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($meta, '_payment_form')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\PaymentForm::getClassifierOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => true,
                            'readonly' => true,
                            'options' => [
                                'id' => '_payment_form',
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">

                        <?= $form->field($meta, '_group')->widget(Select2Default::classname(), [
                            'data' => \common\models\student\EGroup::getOptions($meta->_curriculum),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => true,
                            'readonly' => true,
                            'options' => [
                                'id' => '_group',
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($meta, '_department')->widget(Select2Default::classname(), [
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
                    <div class="col-md-3">
                        <?= $form->field($meta, '_education_year')->widget(Select2Default::classname(), [
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
                        <?php
                        $semesters = array();
                        if ($meta->_curriculum) {
                            $semesters = \common\models\curriculum\Semester::getSemesterByCurriculum($meta->_curriculum);
                        }
                        ?>
                        <?= $form->field($meta, '_semester')->widget(Select2Default::classname(), [
                            'data' => \yii\helpers\ArrayHelper::map($semesters,'code', 'name'),
                            'hideSearch' => false,
                            'disabled' => true,
                            'readonly' => true,
                            'options' => [
                                'id' => '_semester'
                            ],
                        ]); ?>
                    </div>
                </div>


                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $specialties = array();
                            if ($meta->_department) {
                                $specialties = ESpecialty::getHigherSpecialty($meta->_department);
                            }

                            ?>
                            <?= $form->field($meta, '_specialty')->widget(DepDrop::classname(), [
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
                        <div class="col-md-3">
                            <?= $form->field($meta, '_education_type')->widget(
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
                            <?= $form->field($meta, '_education_form')->widget(
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
                    <div class="col col-md-6">
                        <?php
                        $decrees = \common\models\academic\EDecree::getOptionsByCurriculum($this->_user(), $meta->_curriculum ? $meta->curriculum : null, \common\models\system\classifier\DecreeType::TYPE_SCHOLARSHIP);
                        ?>
                        <?= $form->field($meta, '_decree')->widget(Select2Default::class, [
                            'data' => $decrees['options'],
                            'options' => [
                                'id' => '_decree',
                                'required' => true,
                                //'onchange' => 'decreeChanged(this.value)'
                            ],
                            //'disabled' => $searchModel->_group == null,
                            'disabled' => true,
                            'readonly' => true,
                            'hideSearch' => false,
                            'allowClear' => false,
                        ]); ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($meta, '_stipend_rate')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\StipendRate::getClassifierOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => true,
                            'readonly' => true,

                        ]) ?>


                    </div>
                    <div class="col-md-3">
                        <?= $form->field($meta, 'summa')->textInput(['maxlength' => true, 'id' => 'summa', 'disabled' => true,'readonly' => true,]) ?>
                    </div>

                </div>



                <? /*<div class="col-md-3">
                    <?= $form->field($selected, 'start_date')->widget(DatePickerDefault::classname(), [
                        'options' => [
                            'placeholder' => __('YYYY-MM-DD'),
                            'id' => 'start_date',
                            'disabled' => true,
                            'readonly' => true,
                        ],
                    ]); ?>
                </div> */?>



            </div>

        </div>
    </div>


        <div class="col col-md-12">
            <div class="box box-default ">
                <div class="box-header bg-gray ">


                    <div class="row" id="data-grid-filters">
                        <div class="col col-md-6">
                            <h4><?= __('Month names in periods') ?>
                                <span class="text-muted  italic fs-16">
                            <?=sprintf("(%s - %s)<p class='text-muted'></p>",
                                Yii::$app->formatter->asDate($meta->start_date, 'php:d.m.Y'),
                                Yii::$app->formatter->asDate($meta->end_date, 'php:d.m.Y'));?>
                        </span>
                            </h4>
                        </div>

                        <div class="col col-md-6">
                            <div class="pull-right">
                                <?=
                                Html::a(__('Add Scholarship Month'), '#', [
                                    'class' => 'showModalButton btn btn-flat btn-success',
                                    'modal-class' => 'modal-md',
                                    'title' => __('Add Scholarship Month').' / '.$meta->summa,
                                    'value' => Url::to(['finance/scholarship',
                                        'scholarship' => $meta->id,
                                        'edit' => 1,
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
                                    <?php $_curriculum = $meta->_curriculum;?>
                                    <?= GridView::widget([
                                        'id' => 'data-grid',
                                   //     'toggleAttribute' => 'active',

                                        'dataProvider' => $dataProvider,
                                        'columns' => [
                                            [
                                                'attribute' => '_semester',
                                                'format' => 'raw',
                                                'value' => function ($data) use ($_curriculum) {
                                                    return sprintf("%s<p class='text-muted'> </p>", Semester::getByCurriculumSemester($_curriculum, $data->_semester)->name);
                                                },
                                            ],
                                            [
                                                'attribute' => '_education_year',
                                                'format' => 'raw',
                                                'value' => function ($data)  {
                                                    return sprintf("%s<p class='text-muted'> </p>", $data->educationYear->name);
                                                },
                                            ],
                                            [
                                                'attribute' => 'month_name',
                                                'format' => 'raw',
                                                'value' => function ($data) use ($model) {
                                                    return Html::a(Yii::$app->formatter->asDate($data->month_name, 'php:Y - F'), '#', [
                                                        'class' => 'showModalButton ',
                                                        'modal-class' => 'modal-md',
                                                        'title' => Yii::$app->formatter->asDate($data->month_name, 'php:Y - F').' / '.$data->studentScholarship->summa,
                                                        'value' => Url::to(['finance/scholarship',
                                                            'month' => $data->id,
                                                            'edit' => 1,
                                                        ]),
                                                        'data-pjax' => 0
                                                    ]);
                                                },

                                            ],
                                            [
                                                'attribute' => 'summa',
                                                'value' => function ($data) {
                                                    return $data->summa !==null ? Yii::$app->formatter->asCurrency($data->summa) : '-';
                                                },
                                            ],
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>

                <div class="box-footer text-right">
                    <?= $this->getResourceLink(__('Cancel'), ['finance/scholarship'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?php if(!$meta->isNewRecord): ?>
                        <?= $this->getResourceLink(__('Delete'), ['finance/scholarship', 'scholarship' => $meta->id,  'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif;?>
                    <?/*= Html::submitButton(
                        '<i class="fa fa-check"></i> ' . __('Save'),
                        ['class' => 'btn btn-primary btn-flat']
                    ) */?>
                </div>




            </div>
        </div>


<?php ActiveForm::end(); ?>