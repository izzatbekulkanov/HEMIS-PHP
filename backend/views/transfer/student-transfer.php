<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
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
use common\models\curriculum\EStudentSubject;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\Course;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'transfer-data', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-5 col-lg-5">
        <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"><?= __('Students in Group') ?></h3>
            </div>

            <div class="box-body">
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

                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),
                            'hideSearch' => false,
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
                                'placeholder' => __('-Choose Semester-'),
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search'],
                                'url' => Url::to(['/ajax/get-semester-years']),
                                'required' => true
                            ],
                        ])->label(false);; ?>

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
                                'placeholder' => __('-Choose Group-'),
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

                <?php if (isset($dataProvider)): ?>

                    <?= GridView::widget([
                        'id' => 'data-grid',
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                            ],
                            [
                                'attribute' => '_student',
                                'value' => 'student.fullName',
                                // 'enableSorting' => true,
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->student->fullName;
                                },
                            ],
                            [
                                'attribute' => '_payment_form',
                                'value' => 'paymentForm.name',
                            ],


                        ],
                    ]); ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <div class="col col-md-6" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 0]]); ?>
        <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"><?= __('Transfer') ?></h3>
            </div>
            <div class="box-body">

                <div class="col col-md-6">
                    <?php if ($searchModel->_curriculum)
                        $searchModelFix->_curriculum = $searchModel->_curriculum;
                    ?>
                    <?= $form->field($searchModelFix, '_curriculum')->widget(Select2Default::class, [
                        'data' => ECurriculum::getOptions($faculty),
                        'hideSearch' => false,
                        'disabled' => true,
                        'options' => [
                            'id' => '_curriculum_search_fix',
                            'required' => true,
                            // 'readonly' => true
                        ]
                    ])->label(false); ?>
                    <?php if ($searchModel->_education_year)
                        $searchModelFix->_education_year = $searchModel->_education_year;
                    ?>
                    <?= $form->field($searchModelFix, '_education_year')->widget(Select2Default::class, [
                        'data' => EducationYear::getEducationYears(),
                        'hideSearch' => false,
                        'disabled' => true,
                        // 'readonly' => true,
                        'options' => [
                            'id' => '_education_year_search_fix',
                            'required' => true,

                        ]
                    ])->label(false);; ?>
                </div>
                <div class="col col-md-6">
                    <?php
                    $semesters = array();
                    if ($searchModelFix->_curriculum && $searchModelFix->_education_year) {
                        $semesters = Semester::getByCurriculumYear($searchModelFix->_curriculum, $searchModelFix->_education_year);
                    }
                    if ($searchModel->_curriculum && $searchModel->_education_year)
                        $searchModelFix->_semestr = (int)$searchModel->_semestr + 1;
                    ?>
                    <?= $form->field($searchModelFix, '_semestr')->widget(DepDrop::classname(), [
                        'data' => ArrayHelper::map($semesters, 'code', 'name'),
                        'type' => DepDrop::TYPE_SELECT2,
                        'pluginLoading' => false,
                        'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                        'options' => [
                            'id' => '_semester_search_fix',
                            'placeholder' => __('-Choose Semester-'),
                            'required' => true
                        ],
                        'pluginOptions' => [
                            'depends' => ['_curriculum_search_fix', '_education_year_search_fix'],
                            'url' => Url::to(['/ajax/get-semester-years']),
                            'required' => true
                        ],
                    ])->label(false);; ?>

                    <?php
                    /*$groups = array();
                    if($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semestr)
                        $searchModelFix->_group = $searchModel->_group;
                    if($searchModel->_curriculum)
                        $groups = EGroup::getOptions($searchModel->_curriculum)
                    ?>
                    <?= $form->field($searchModelFix, '_group')->widget(DepDrop::classname(), [
                        'data' => $groups,
                        'type' => DepDrop::TYPE_SELECT2,
                        'pluginLoading' => false,
                        'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                        'options' => [
                            'id' => '_group_search_fix',
                            'placeholder' => __('-Choose Group-'),
                            'required' => true
                        ],

                        'pluginOptions' => [
                            'depends' => ['_curriculum_search_fix'],
                            'url' => Url::to(['/ajax/get-group-by-curruculum']),
                            'required' => true
                        ],
                    ])->label(false); */ ?>

                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Transfer'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign', 'data-pjax' => '0',]) ?>

                </div>
            </div>


            <?php ActiveForm::end(); ?>


            <?php if ($searchModel->_group): ?>
                <?php if (isset($studentRegisterProvider)): ?>

                    <?= GridView::widget([
                        'id' => 'data-grid-subject-subject',
                        'dataProvider' => $studentRegisterProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => '_student',
                                'value' => 'student.fullName',
                            ],

                            [
                                'attribute' => '_education_year',
                                'value' => 'educationYear.name',
                            ],
                            [
                                'attribute' => '_semestr',
                                'value' => function ($data) {
                                    if(Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr) != null)
                                        return Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr)->name;
                                    elseif($data->semester)
                                        return $data->semester->name;
                                    else
                                        return \common\models\system\classifier\Semester::findOne($data->_semestr)->name;
                                },
                            ],
                            [
                                'attribute' => '_level',
                                'value' => 'level.name',
                            ],
                            [
                                'attribute' => '_group',
                                'value' => 'group.name',
                            ],
                            /*[
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{delete}',
                                'buttons' => [
                                    'delete' => function ($url, $model) {
                                        return Html::a('<span class="fa fa-trash"></span>', $url, [
                                            'title' => __('Delete'),
                                            'data-confirm' => __('	Are you sure to delete?'),
                                            'data-pjax' => '0',
                                        ]);
                                    }
                                ],
                                'urlCreator' => function ($action, $model, $key, $index) {
                                    if ($action === 'delete') {
                                        $url = Url::to(['transfer/student-transfer', 'id' => $model->id, 'delete' => 1]);
                                        return $url;
                                    }
                                }
                            ],*/
                        ],
                    ]); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    <?php
    $message = json_encode([__('You are transferring students for a semester. Are you really continuing?')]);
    $script = <<< JS
	$("#assign").click(function(){
	    var r = confirm({$message}[0]);
	    if(r){
            var keys = $('#data-grid').yiiGridView('getSelectedRows');
            var _curriculum =  $('#_curriculum_search_fix').val();
            var _education_year =  $('#_education_year_search_fix').val();
            var _semester =  $('#_semester_search_fix').val();
            $.post({
               url:  '/transfer/to-transfer',
               data: {selection: keys, _curriculum: _curriculum, _education_year: _education_year, _semester: _semester},
               dataType:"json",
            }).done(function(data){
                document.location.reload();
            });
		}
		
		return false;    
	});
JS;
    $this->registerJs($script);
    ?>
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
