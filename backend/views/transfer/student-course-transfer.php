<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\student\EStudentTransferMeta;
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
/* @var $searchModel \common\models\student\EStudentTransferMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'transfer-form']]); ?>
<div class="row">
    <div class="col col-md-8">
        <div class="box box-default ">
            <?= GridView::widget([
                'id' => 'data-grid',
                'sticky' => '#sidebar',
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function (EStudentTransferMeta $model, $key, $index, $column) use ($searchModel) {
                            return [
                                'disabled' => !$model->canTransferNextLevel($searchModel)
                            ];
                        }
                    ],
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EStudentTransferMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                        },
                    ],
                    [
                        'attribute' => '_education_type',
                        'format' => 'raw',
                        'value' => function (EStudentTransferMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                        },
                    ],
                    [
                        'attribute' => '_level',
                        'format' => 'raw',
                        'value' => function (EStudentTransferMeta $data) {
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
                    ],
                    [
                        'attribute' => 'gpa',
                        'format' => 'raw',
                        'value' => function (EStudentTransferMeta $data) {
                            return $data->markingSystem->isCreditMarkingSystem() && $data->studentGpa ? $data->studentGpa->gpa : '-';
                        },
                    ],
                    [
                        'attribute' => 'debt_subjects',
                        'header' => __('Debt Subjects'),
                        'format' => 'raw',
                        'value' => function (EStudentTransferMeta $data) {
                            return $data->studentGpa ? $data->studentGpa->debt_subjects : '-';
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <div class="box box-default " id="data-grid-filters">

            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getCurriculumItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudenttransfermeta-_education_year").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = $searchModel->_curriculum == null,
                            'options' => [
                                'onchange' => '$("#estudenttransfermeta-_level").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_level')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getLevelItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudenttransfermeta-_semestr").val("")'
                            ],
                            'disabled' => $disabled = ($disabled || $searchModel->_education_year == null),
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSemesterItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudenttransfermeta-_group").val("")'
                            ],
                            'disabled' => $disabled = ($disabled || $searchModel->_level == null),
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
                <?= Html::a('<i class="fa fa-close"></i> ' . __('Clear Filter'), ['student-course-transfer', 'clear-filter' => 1], [
                    'class' => 'btn btn-default btn-flat',
                    'data-pjax' => 0
                ]) ?>
            </div>
            <div class="box-header bg-gray"></div>
            <div class="box-body">
                <?= $form->field($searchModel, '_nextLevel')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getNextLevelOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(); ?>
                <?= $form->field($searchModel, 'selectedStudents')->textInput(['readonly' => true])->label(); ?>
                <?php
                $decrees = EDecree::getOptionsByCurriculum($this->_user(), $searchModel->_curriculum ? $searchModel->curriculum : null, DecreeType::TYPE_TRANSFER_TO_LEVEL);
                ?>
                <?php if ($searchModel->isCourseTransfer()): ?>
                    <?= $form->field($searchModel, '_decree')->widget(Select2Default::class, [
                        'data' => $decrees['options'],
                        'options' => [
                            'id' => '_decree',
                            'required' => true,
                        ],
                        'hideSearch' => false,
                        'allowClear' => false,
                    ]); ?>
                    <?= $form->field($searchModel, 'order_date')->textInput([
                        'id' => 'order_date',
                        'readonly' => true
                    ])->label(); ?>
                <?php endif; ?>
            </div>

            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Transfer to Course'), [
                    'class' => 'btn btn-primary btn-flat',
                    'disabled' => $searchModel->nextLevel == null,
                    'onclick' => 'return confirmTransfer()'
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<script>
    function updateSelectedStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        $('#estudenttransfermeta-selectedstudents').val(keys.length)
    }

    function confirmTransfer() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            if (confirm(<?=json_encode([__('Are you sure to transfer {count} students into {level}?')])?>[0].replace('{count}', keys.length).replace('{level}', '<?=($data = $searchModel->nextLevel) ? ($data->level ? $data->level->name : '') . ' / ' . $data->name : ""?>'))) {
                $('#transfer-form').submit();
            }
        } else {
            alert(<?=json_encode([__('Please, select students')])?>[0])
        }

        return false;
    }
</script>
<?php
$this->registerJs("$('#data-grid input[type=\"checkbox\"]').on('change',function(){updateSelectedStudents()})")
?>
<?php Pjax::end() ?>


