<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\curriculum\EducationYear;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\finance\EStudentContractType;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use common\models\finance\EStudentContract;

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
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i> ' . __('Export to Excel'),
                        [
                            'finance/payment-monitoring-department',
                            'education_year' => $searchModel->_education_year,
                            'download' => 1
                        ],
                        ['class' => 'btn btn-flat btn-success btn-primary btn-block', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-4">
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
                <?= $form->field($searchModel, '_specialty')->widget(DepDrop::classname(), [
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
        </div>
        <div class="row" id="data-grid-filters">
            <div class="col col-md-2">
            </div>


            <div class="col col-md-2">
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
                <?php
                $groups = array();
                if ($searchModel->_department && $searchModel->_specialty && $searchModel->_education_form) {
                    $groups = EGroup::getOptionsByFacultyEduForm($searchModel->_department, $searchModel->_specialty, $searchModel->_education_form);

                    if ($this->_user()->role->isTutorRole()) {
                        $groups = array_intersect_key($groups,$this->_user()->tutorGroups);
                    }
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

            <div class="col col-md-2">
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
                <?= $form->field($searchModel, '_level')->widget(Select2Default::class, [
                    'data' => \common\models\system\classifier\Course::getClassifierOptions(),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_level',
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
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    '__class' => SerialColumn::class,
                ],

                [
                    'attribute' => '_student',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s / %s</p>", $data->student->fullName, $data->student->student_id_number, $data->educationForm->name);
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
                    'attribute' => '_education_year',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->educationYear->name, $data->group ? $data->group->name : '');
                    },
                ],
                [
                    'attribute' => '_contract_type',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s / %s </p>", $data->contractType->name, EStudentContractType::getContractFormOptions()[$data->contract_form_type], $data->contractSummaType->name);
                    },
                ],
                [
                    'attribute' => 'number',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->number, Yii::$app->formatter->asDate($data->date, 'php:d.m.Y'));
                    },
                ],
                [
                    'attribute' => 'summa',
                    'value' => function ($data) {
                        return $data->summa !== null ? Yii::$app->formatter->asCurrency($data->summa) : '-';
                    },
                ],
                [
                    'attribute' => 'id',
                    'header' => __('Paid Contract Fee'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Yii::$app->formatter->asCurrency(EStudentContract::getTotal($data->paidContractFee, 'summa'));
                    },
                ],
                [
                    'attribute' => 'id',
                    'header' => __('Contract Indebtedness'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        $color = "black";
                        if ($data->different >= 0)
                            $color = "red";
                        elseif ($data->different < 0)
                            $color = "blue";
                        if ($data->different != 0) {
                            return sprintf("%s<p class='text-muted'> %s</p>", Yii::$app->formatter->asCurrency(@$data->different), '<span style="color:' . $color . '">' . EStudentContract::getDifferentOptions()[@$data->different_status] . '</span>');
                        } else if ($data->different == 0) {
                            return '-';
                        } else
                            return sprintf("%s<p class='text-muted'> %s</p>", $data->summa !== null ? Yii::$app->formatter->asCurrency($data->summa) : '-', '<span style="color:' . $color . '">' . EStudentContract::getDifferentOptions()[EStudentContract::DIFFERENT_DEBTOR_STATUS] . '</span>');

                        //return Yii::$app->formatter->asCurrency($data->summa - EStudentContract::getTotal($data->paidContractFee, 'summa'));
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
