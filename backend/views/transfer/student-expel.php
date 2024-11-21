<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\student\EStudentExpelMeta;
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
use common\models\curriculum\EStudentSubject;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\Course;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\student\EStudentExpelMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'transfer-form']]); ?>
    <div class="row">
        <div class="col col-md-3 col-sm-push-9" style="padding-left: 0">
            <div class="box box-default " id="data-grid-filters">
                <div class="box-body">
                    <div class="row">
                        <div class="col col-md-12">
                            <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getDepartmentItems(),
                                'allowClear' => $searchModel->_education_type == null,
                                'disabled' => $faculty != null,
                                'hideSearch' => false,
                                'options' => [
                                    'onchange' => '$("#estudentexpelmeta_education_form").val("")'
                                ],
                            ])->label(false); ?>
                            <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getEducationTypeItems(),
                                'allowClear' => $searchModel->_education_form == null,
                                'disabled' => $disabled = $searchModel->_department == null,
                                'hideSearch' => false,
                                'options' => [
                                    'onchange' => '$("#estudentexpelmeta_education_form").val("")'
                                ],
                            ])->label(false); ?>
                            <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getEducationFormItems(),
                                'allowClear' => $searchModel->_curriculum == null,
                                'hideSearch' => false,
                                'disabled' => $disabled = $searchModel->_education_type == null,
                                'options' => [
                                    'onchange' => '$("#estudentexpelmeta_curriculum").val("")'
                                ],
                            ])->label(false); ?>

                            <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getCurriculumItems(),
                                'allowClear' => $searchModel->_semestr == null,
                                'hideSearch' => false,
                                'disabled' => $disabled = $disabled || $searchModel->_education_form == null,
                                'options' => [
                                    'onchange' => '$("#estudentexpelmeta_semestr").val("")'
                                ],
                            ])->label(false); ?>
                            <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getSemesterItems(),
                                'allowClear' => $searchModel->_group == null,
                                'hideSearch' => false,
                                'disabled' => $disabled = $disabled || $searchModel->_curriculum == null,
                                'options' => [
                                    'onchange' => '$("#estudentexpelmeta_group").val("")'
                                ],
                            ])->label(false); ?>
                            <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getGroupItems(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'disabled' => $disabled = ($disabled || $searchModel->_semestr == null),
                            ])->label(false); ?>
                            <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::a('<i class="fa fa-close"></i> ' . __('Clear Filter'), ['student-expel', 'clear-filter' => 1], [
                        'class' => 'btn btn-default btn-flat',
                        'data-pjax' => 0
                    ]) ?>
                </div>
                <div class="box-header bg-gray"></div>
                <div class="box-body">
                    <?= $form->field($searchModel, '_status_change_reason')->widget(Select2Default::class, [
                        'data' => \common\models\system\classifier\ExpelReason::getClassifierOptions(),
                        'hideSearch' => false,
                        'allowClear' => false,
                        'disabled' => $searchModel->_group == null,
                    ]); ?>
                    <?= $form->field($searchModel, '_decree')->widget(Select2Default::class, [
                        'data' => $options = $searchModel->getDecreeOptions(),
                        'hideSearch' => false,
                        'allowClear' => false,
                        'disabled' => $searchModel->_group == null,
                    ]); ?>
                    <?= $form->field($searchModel, 'selectedStudents')->textInput(['readonly' => true])->label(); ?>
                </div>
                <div class="box-footer text-right">
                    <?= Html::button('<i class="fa fa-check"></i> ' . __('Expel'), [
                        'class' => 'btn btn-primary btn-flat',
                        'disabled' => $searchModel->_decree == null,
                        'onclick' => 'return confirmTransfer()'
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col col-md-9 col-sm-pull-3">
            <div class="box box-default">
                <div>
                    <?= GridView::widget([
                        'id' => 'data-grid',
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                                'checkboxOptions' => function (EStudentExpelMeta $model, $key, $index, $column) use ($searchModel) {
                                    return [
                                        'disabled' => !$model->canOperateExpel()
                                    ];
                                }
                            ],
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => function (EStudentExpelMeta $data) {
                                    return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                                },
                            ],
                            [
                                'attribute' => '_education_type',
                                'format' => 'raw',
                                'value' => function (EStudentExpelMeta $data) {
                                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                                },
                            ],
                            [
                                'attribute' => '_level',
                                'format' => 'raw',
                                'value' => function (EStudentExpelMeta $data) {
                                    $semester = "";
                                    if(Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr) != null)
                                        $semester  = Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr)->name;
                                    elseif($data->semester)
                                        $semester =  $data->semester->name;
                                    else
                                        $semester = \common\models\system\classifier\Semester::findOne($data->_semestr)->name;

                                    return sprintf('%s<p class="text-muted">%s</p>', $data->level->name, $semester);
                                },
                            ],
                            [
                                'attribute' => '_group',
                                'value' => 'group.name',
                            ]
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>

    <script>
        function updateSelectedStudents() {
            var keys = $('#data-grid').yiiGridView('getSelectedRows');
            $('#estudentexpelmeta-selectedstudents').val(keys.length)
        }

        function confirmTransfer() {
            var keys = $('#data-grid').yiiGridView('getSelectedRows');
            if (keys.length === 0) {
                alert(<?=json_encode([__('Please, select students')])?>[0])
            } else {
                if (confirm(<?=json_encode([__('Are you sure to expulsion {count} students from the university?')])?>[0].replace('{count}', keys.length))) {
                    $('#transfer-form').submit();
                }
            }

            return false;
        }
    </script>
<?php
$this->registerJs("$('#data-grid input[type=\"checkbox\"]').on('change',function(){updateSelectedStudents()})")
?>
<?php Pjax::end() ?>