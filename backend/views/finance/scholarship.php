<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\curriculum\Semester;
use common\models\student\EGroup;
use common\models\system\classifier\StipendRate;
use common\models\curriculum\ECurriculum;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __(StipendRate::findOne(StipendRate::STIPEND_RATE_BASE)->name),
                        ['finance/set-scholarship'/*, 'type'=>StipendRate::STIPEND_RATE_BASE*/],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?/*= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __(StipendRate::findOne(StipendRate::STIPEND_RATE_FAMOUS)->name),
                        ['finance/set-scholarship', 'type'=>StipendRate::STIPEND_RATE_FAMOUS],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) */?>
                </div>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?/* $label_in = StipendRate::findOne(StipendRate::STIPEND_RATE_INVALID)->name;?>
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __(substr($label_in,0,strlen($label_in)-4).')'),
                        ['finance/set-scholarship', 'type'=>StipendRate::STIPEND_RATE_INVALID],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) */?>
                </div>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?/*= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __(StipendRate::findOne(StipendRate::STIPEND_RATE_ORPHANAGE)->name),
                        ['finance/set-scholarship', 'type'=>StipendRate::STIPEND_RATE_ORPHANAGE],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) */?>
                </div>
            </div>
        </div>

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
                ])->label(false);; ?>
                <?php echo $form->field($searchModel, '_education_form')->hiddenInput(['value'=>EducationForm::EDUCATION_FORM_DAYLY, 'id'=>'_education_form'])->label(false);?>
            </div>
            <div class="col col-md-4">
                <?php
                $curriculums = array();
                if ($searchModel->_education_type) {
                    $curriculums = ECurriculum::getOptionsByEduTypeForm($searchModel->_education_type, $searchModel->_education_form, $department);
                }

                ?>
                <?= $form->field($searchModel, '_curriculum')->widget(DepDrop::classname(), [
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
                ])->label(false); ?>
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
                <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
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
                <?= $form->field($searchModel, '_stipend_rate')->widget(Select2Default::class, [
                    'data' => StipendRate::getClassifierOptions(),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_stipend_rate',
                        'required' => true,
                    ]
                ])->label(false);; ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div>

    </div>
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'toggleAttribute' => 'active',
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    '__class' => SerialColumn::class,
                ],

                [
                    'attribute' => '_student',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a($data->student->fullName,
                            [
                                'finance/scholarship',
                                'scholarship' => $data->id,
                                'view' => 1
                            ], ['data-pjax' => 0]);
                    },
                ],
                [
                    'attribute' => '_specialty',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->educationType->name, $data->specialty->code);
                    },
                ],
                [
                    'attribute' => '_group',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->group->name, $data->paymentForm->name);
                    },
                ],
                [
                    'attribute' => '_semester',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s / %s<p class='text-muted'> %s</p>", Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name, $data->educationYear->name, $data->stipendRate->name);
                    },
                ],
                [
                    'attribute' => '_decree',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->decree->number, Yii::$app->formatter->asDate($data->decree->date, 'php:d.m.Y'));
                    },
                ],
                [
                    'attribute' => 'summa',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s </p>", $data->summa !==null ? Yii::$app->formatter->asCurrency($data->summa) : '-', count($data->scholarshipMonth).' '.__('month'));
                    },
                ],
                [
                    'attribute' => 'start_date',
                    'header' => __('Stipend Period'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s / <p class='text-muted'>%s</p>",  Yii::$app->formatter->asDate($data->start_date, 'php:d.m.Y'), Yii::$app->formatter->asDate($data->end_date, 'php:d.m.Y'));
                    },
                ],
            ],
        ]
    ); ?>
</div>
<?php
$this->registerJs(
    '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
)
?>

<?php Pjax::end() ?>
