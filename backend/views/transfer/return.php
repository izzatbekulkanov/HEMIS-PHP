<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\system\classifier\DecreeType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\curriculum\Semester;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\Course;
use common\models\system\classifier\StudentStatus;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::class, [
                            'data' => ECurriculum::getOptions($faculty),
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_curriculum_search',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),
                            'disabled' => $searchModel->_curriculum == null,
                            'options' => [
                                'id' => '_education_year_search',
                                'required' => true,
                            ]
                        ])->label(false);; ?>
                    </div>
                    <div class="col col-md-6">
                        <?php
                        $semesters = array();
                        if ($searchModel->_curriculum && $searchModel->_education_year) {
                            $semesters = Semester::getByCurriculumYear($searchModel->_curriculum, $searchModel->_education_year);
                        }
                        ?>
                        <?= $form->field($searchModel, '_semestr')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($semesters, 'code', 'name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_semester_search',
                                'placeholder' => __('-Choose-'),
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search'],
                                'url' => Url::to(['/ajax/get-semester-years']),
                                'required' => true
                            ],
                        ])->label(false);; ?>
                    </div>
                    <div class="col col-md-6">
                        <?php
                        $groups = array();
                        if ($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semestr) {
                            $groups = EStudentMeta::getContingentByCurriculumSemester($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semestr);
                        }
                        ?>
                        <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($groups, '_group', 'group.name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_group_search',
                                'placeholder' => __('-Choose-'),
                                'required' => true
                            ],

                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search', '_semester_search'],
                                'url' => Url::to(['/ajax/get-group-semesters']),
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
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function (EStudentMeta $model) {
                            return [
                                'disabled' => !$model->canOperateReturn()
                            ];
                        }
                    ],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return $data->student->fullName;
                        },
                    ],
                    [
                        'attribute' => '_specialty_id',
                        'value' => 'specialty.code',
                    ],
                    [
                        'attribute' => '_education_year',
                        'value' => 'educationYear.name',
                    ],
                    [
                        'attribute' => '_payment_form',
                        'value' => 'paymentForm.name',
                    ],

                ],
            ]); ?>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <div class="box box-default ">
            <div class="box-body">

                <?php $form2 = ActiveForm::begin(['action' => ['/transfer/to-return'], 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1, 'method' => 'post']]); ?>
                <?= $form->field($searchModelFix, '_student_status')->widget(Select2Default::classname(), [
                    'data' => StudentStatus::getReturnStatusOptions(),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'placeholder' => false,
                    'options' => [
                        'id' => '_student_status'
                    ],
                ]); ?>

            </div>
            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign', 'onclick' => 'assignStudents()']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<script>
    var base_url = '<?= \Yii::$app->request->hostInfo; ?>';
    var operand = '<?= StudentStatus::STUDENT_TYPE_STUDIED; ?>';
    function assignStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert(<?=json_encode([__('Talabalarni tanlang')])?>[0]);
        } else if (keys.length > 0) {
            if (confirm(<?=json_encode([__('If you really want to return selected students to study, confirm the action if you agree.')])?>[0])) {
                $.post({
                    url: '/transfer/to-return',
                    data: {
                        selection: keys,
                        operand: operand
                    },
                    dataType: "json",
                });
            }
        }
    }
</script>

<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
