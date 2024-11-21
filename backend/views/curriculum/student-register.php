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
    <div class="col col-md-6 col-lg-6">
        <div class="box box-primary ">
            <div class="box-header bg-gray with-border">
                <h3 class="box-title"><?= __('Students in Group') ?></h3>
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
                        <?php
                        $education_years = [];
                        if ($searchModel->_curriculum) {
                            $education_years = ArrayHelper::map(Semester::getSemesterByCurriculum($searchModel->_curriculum), '_education_year', 'educationYear.name');
                        }
                        ?>
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => $education_years,
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
            </div>


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
                            return Html::a($data->student->fullName, ['student/student-edit', 'id' => $data->_student], ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => '_specialty_id',
                        'value' => 'specialty.code',
                    ],


                ],
            ]); ?>
        </div>
    </div>
    <div class="col col-md-6" id="sidebar">

        <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"><?= __('Subjects in Curriculum') ?></h3>
            </div>


            <?php $colors = ['bg-aqua', 'bg-green', 'bg-info', 'bg-olive', 'bg-teal', 'bg-orange', 'bg-purple', 'bg-lime', 'bg-fuchsia', 'bg-maroon', 'bg-yellow'];?>
            <?php $selectedColors = [] ?>
            <?= GridView::widget([
                'id' => 'data-grid-subject',
                'dataProvider' => $subjectProvider,
                'rowOptions' => function ($data, $key, $index, $obj) use ($subjectProvider, $colors, &$selectedColors) {
                    if ($data->_subject_type == SubjectType::SUBJECT_TYPE_SELECTION) {
                        $result = [];
                        /*$models = $subjectProvider->models;
                        $prev = ($index > 0) ? $models[$index - 1] : null;
                        $next = ($index < ((count($models) - 1))) ? $models[$index + 1] : null;
                        if (@$data->in_group == @$prev->in_group || @$data->in_group == @$next->in_group) {
                        }*/

                        if (!isset($selectedColors[$data->in_group])) {
                            $selectedColors[$data->in_group] = @$colors[count($selectedColors)];
                        }
                        $result['class'] = @$selectedColors[$data->in_group];

                        return $result;
                    }
                },
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function ($data) {
                            return [
                                'class' => 'subject-items',
                                'data-group' => $data->in_group,
                                'onchange' => 'checkGroupSelection(this)'
                            ];
                        }
                    ],
                    [
                        'attribute' => '_subject',
                        'value' => 'subject.name',
                    ],
                    [
                        'attribute' => '_subject_type',
                        'value' => 'subjectType.name',
                    ],
                    [
                        'attribute' => 'in_group',
                        'value' => function ($data) {
                            if ($data->in_group == null) {
                                return " ";
                            } else
                                return "T-" . $data->in_group;
                        },
                    ],


                ],
            ]); ?>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign']) ?>
            </div>
        </div>

    </div>
</div>
<br>
<?php if ($searchModel->_group): ?>
    <div class="row">
        <div class="col col-md-12 col-lg-12">
            <div class="box box-default ">
                <div class="box-header bg-gray">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= __('Students registered in the Subject') ?></h3>

                    </div>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>

                        <div class="col col-md-6">
                            <?php
                            $subjects = [];
                            $filter_group = $searchModel->_group;
                            if ($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semestr) {
                                $registered_students = EStudentSubject::find()
                                    ->where([
                                        '_curriculum' => $searchModel->_curriculum,
                                        '_education_year' => $searchModel->_education_year,
                                        '_semester' => $searchModel->_semestr,
                                        //'_group' => $searchModel->_group,
                                    ])
                                    ->andWhere(['in', '_student', $students])
                                    ->all();
                                $subjects = ArrayHelper::map($registered_students, '_subject', 'subject.name');
                            }
                            ?>
                            <?= $form->field($studentSubject, '_subject')->widget(Select2Default::classname(), [
                                'data' => $subjects,
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_subject',
                                ]
                            ])->label(false); ?>

                        </div>
                        <div class="col col-md-6">
                            <?php

                            if ($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semestr) {
                                $students = ArrayHelper::map($registered_students, '_student', 'student.fullName');
                            }
                            ?>
                            <?= $form->field($studentSubject, '_student')->widget(Select2Default::classname(), [
                                'data' => $students,
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_student',
                                ]
                            ])->label(false); ?>

                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <?= GridView::widget([
                    'id' => 'data-grid-subject-subject',
                    'dataProvider' => $studentSubjectProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => '_student',
                            'value' => 'student.fullName',
                        ],
                        [
                            'attribute' => '_subject',
                            'value' => 'subject.name',
                        ],
                        [
                            'attribute' => '_education_year',
                            'value' => 'educationYear.name',
                        ],
                        [
                            'attribute' => '_semester',
                            'value' => function ($data) {
                                return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                            },

                        ],
                        [
                            'attribute' => '_group',
                            'format' => 'raw',
                            'value' => function ($data) use ($filter_group) {
                                $result = "";
                                if($data->_group != $filter_group) {
                                    $result =  Html::a($data->group->name, '#', [
                                        'class' => 'showModalButton label label-danger',
                                        'modal-class' => 'modal-small',
                                        'title' => $data->group->name . ' => ' . __('Edit Group Information'),
                                        'value' => Url::current(
                                            [
                                                //    'curriculum/student-register',
                                                'id' => $data->id,
                                                'filter' => $filter_group,
                                                'edit' => 1
                                            ]
                                        ),
                                        'data-pjax' => 0
                                    ]);
                                }
                                else{
                                    $result = $data->group->name;
                                }
                                return $result;
                            },
                        ],
                        [
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
                                    $url = Url::to(['curriculum/student-register', 'id' => $model->id, 'delete' => 1]);
                                    return $url;
                                }
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<script>
    var base_url = '<?= \Yii::$app->request->hostInfo; ?>';

    function checkGroupSelection(element) {
        if ($(element).prop('checked')) {
            var g = $(element).data('group');
            if (g !== undefined) {
                $('input.subject-items[data-group="' + g + '"]').prop('checked', false);
                $(element).prop('checked', true);
            }
        }
    }
</script>
<?php
$script = <<< JS
	$("#assign").click(function(){
		var keys = $('#data-grid').yiiGridView('getSelectedRows');
		var subjects = $('#data-grid-subject').yiiGridView('getSelectedRows');
		var _curriculum =  $('#_curriculum_search').val();
		var _education_year =  $('#_education_year_search').val();
		var _semester =  $('#_semester_search').val();
		var _group =  $('#_group_search').val();
		$.post({
           url:  '/curriculum/to-register',
           data: {selection: keys, subjects: subjects, _curriculum: _curriculum, _education_year: _education_year, _semester: _semester, _group: _group },
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
