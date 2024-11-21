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
use common\models\curriculum\Semester;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\Course;
use common\models\structure\EDepartment;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use common\models\student\EStudentMeta;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Student Fixed');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'transfer-form']]); ?>

<div class="row">
    <div class="col col-md-8 col-sm-12">

        <div class="box box-default ">
            <div class="box-header bg-gray">
                    <div class="row" id="data-grid-filters">
                        <div class="col-md-12">
                            <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>

                        </div>
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
                                'disabled' => !$model->canOperateApplied()
                            ];
                        }
                    ],
                    [
                        'attribute' => '_student',
                        'value' => 'student.fullName',
                        // 'enableSorting' => true,
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::a($data->student ? $data->student->fullName : '-', ['student/student-edit', 'id' => $data->_student], ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => '_specialty_id',
                        'value' => 'specialty.code',
                    ],
                    [
                        'attribute' => 'student.year_of_enter',
                        //'value' => 'educationYear.name',
                    ],
                    [
                        'attribute' => '_education_type',
                        'value' => 'educationType.name',
                    ],
                    [
                        'attribute' => '_payment_form',
                        'value' => 'paymentForm.name',
                    ],

                ],
            ]); ?>
        </div>
    </div>
    <div class="col col-md-4 col-sm-12" id="sidebar">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">


                    <?php if (!$faculty): ?>
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getFaculties(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_department_search',
                            ],

                            //'disabled' => true,
                        ])->label(); ?>
                    <?php endif; ?>
                    <?php
                    $specialties = [];
                    if ($searchModel->_department) {
                        $specialties = ESpecialty::getHigherSpecialty($searchModel->_department);
                    }
                    if ($faculty) {
                        $specialties = ESpecialty::getHigherSpecialty($faculty);
                    }
                    ?>
                    <?= $form->field($searchModel, '_specialty_id')->widget(Select2Default::classname(), [
                        'data' => $specialties,
                        'allowClear' => true,
                        'hideSearch' => false,
                        'options' => [
                            'id' => '_specialty_id',
                            'required' => true,

                        ],

                        //'disabled' => true,
                    ])->label(); ?>
                    <?php
                    $curriculums = [];
                    if ($searchModel->_department && $searchModel->_specialty_id) {
                        $curriculums = ECurriculum::getOptionsByDepartmentSpecialty($searchModel->_department, $searchModel->_specialty_id);
                    }
                    if ($faculty && $searchModel->_specialty_id ) {
                        $curriculums = ECurriculum::getOptionsByDepartmentSpecialty($faculty, $searchModel->_specialty_id);
                    }
                    ?>
                    <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                        'data' => $curriculums,
                        'allowClear' => true,
                        'hideSearch' => false,
                        'options' => [
                            'id' => '_curriculum',
                            'required' => true,
                        ],
                    ]) ?>
                    <?php
                    $groups = [];
                    if ($searchModel->_curriculum) {
                        $groups = EGroup::getOptions($searchModel->_curriculum);
                    }

                    ?>
                    <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                        'data' => $groups,
                        'allowClear' => true,
                        'hideSearch' => false,
                        'options' => [
                            'id' => '_group',
                            'required' => true,
                        ],
                    ]) ?>

                <?php
                    $semesters = [];
                    if ($searchModel->_curriculum) {
                        $semesters = Semester::getSemesterByCurriculum($searchModel->_curriculum);
                    }
                ?>
                <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map($semesters, 'code', 'name'),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_semestr',
                        'required' => true,
                    ],
                ]) ?>

                <?php
                $levels = [];
                if ($searchModel->_curriculum && $searchModel->_semestr) {
                    $levels = Semester::getOptionsByCurriculumSemester($searchModel->_curriculum, $searchModel->_semestr);
                }
                ?>
                <?= $form->field($searchModel, '_level')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map($levels, '_level', 'level.name'),
                    'allowClear' => true,
                    'hideSearch' => false,

                    'options' => [
                        'id' => '_level',
                        'required' => true,
                    ],
                ]) ?>
                    <?php
                    $years = [];
                    if ($searchModel->_curriculum && $searchModel->_semestr) {
                        $years = Semester::getOptionsByCurriculumSemester($searchModel->_curriculum, $searchModel->_semestr);
                    }
                    ?>
                    <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                        'data' => ArrayHelper::map($years, '_education_year', 'educationYear.name'),
                        'allowClear' => true,
                        'hideSearch' => false,

                        'options' => [
                            'id' => '_education_year',
                            'required' => true,
                        ],
                    ]) ?>


            </div>
            </div>
            </div>
            <div class="box-footer text-right">
                <?//= Html::button('<i class="fa fa-check"></i> ' . __('Assignment'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign', 'type' => 'button']) ?>
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Assignment'), [
                    'class' => 'btn btn-primary btn-flat',
                    //'disabled' => $searchModel->nextLevel == null,
                    'onclick' => 'return confirmTransfer()'
                ]) ?>
            </div>
        </div>


    </div>
</div>
<?php ActiveForm::end(); ?>
<script>
    //var base_url = '<?= \Yii::$app->request->hostInfo; ?>';
</script>


<script>

    function updateSelectedStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        $('#estudenttransfermeta-selectedstudents').val(keys.length)
    }

    function confirmTransfer() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        var _curriculum =  $('#_curriculum').val();
        var _group =  $('#_group').val();
        var _semester =  $('#_semestr').val();
        var _level =  $('#_level').val();
        var _education_year =  $('#_education_year').val();
        if(_curriculum==""){
            alert(<?=json_encode([__('Please, select curriculum')])?>[0])
            return false;
        }
        if(_group==""){
            alert(<?=json_encode([__('Please, select group')])?>[0])
            return false;
        }
        if(_semester==""){
            alert(<?=json_encode([__('Please, select semester')])?>[0]);
            return false;
        }
        if(_level==""){
            alert(<?=json_encode([__('Please, select level')])?>[0])
            return false;
        }
        if(_education_year==""){
            alert(<?=json_encode([__('Please, select education year')])?>[0])
            return false;
        }
        if (keys.length > 0) {
            if (confirm(<?=json_encode([__('Are you sure to transfer {count} students into {group}?')])?>[0].replace('{count}', keys.length).replace('{group}', '<?=($data = $searchModel->_group) ? EGroup::findOne($data)->name : ""?>'))) {

                if(keys.length&&_curriculum&&_group&&_semester&&_level&&_education_year)
                    $.post({
                        url: '/student/to-fixed-groups',
                        data: {selection: keys, curriculum: _curriculum, group: _group, semester: _semester, level: _level, education_year: _education_year,'_csrf-backend':$('input[name=""]').val() },
                        dataType:"json",
                    });
            }
        } else {
            alert(<?=json_encode([__('Please, select students')])?>[0])
        }

        return false;
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
