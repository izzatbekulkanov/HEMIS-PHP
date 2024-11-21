<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\UploadDefault;
use common\models\academic\EDecree;
use common\models\attendance\EAttendance;
use common\models\student\EStudentTransferGroupMeta;
use common\models\system\classifier\DecreeType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
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
/* @var $searchModel \common\models\attendance\EAttendance */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = $this->title;
$disabled = false
?>

<div class="row">
    <div class="col col-md-8" style="padding-right: 0">
        <?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>

        <div class="box box-default">
            <div class="box-header bg-gray">
                <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => []]); ?>
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'disabled' => $faculty != null,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Group / Student / Teacher / Subject')])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'start_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'end_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                            ],
                        ])->label(false); ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            <?= GridView::widget([
                'id' => 'data-grid',
                'sticky' => '#sidebar',
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function (EAttendance $model, $key, $index, $column) use ($searchModel) {
                            return [
                                'disabled' => !$model->canChangeAttendance(),
                            ];
                        }
                    ],
                    [
                        'class' => \yii\grid\SerialColumn::class,
                    ],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EAttendance $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->student->getShortName(), $data->group->name);
                        },
                    ],
                    [
                        'attribute' => '_subject',
                        'format' => 'raw',
                        'value' => function (EAttendance $data) {
                            return sprintf(' %s<p class="text-muted">%s / %s</p>', $data->subject->getShortTitle(4), $data->subjectSchedule->trainingType->name, $data->employee->getShortName());
                        },
                    ],
                    [
                        'attribute' => 'lesson_date',
                        'format' => 'raw',
                        'value' => function (EAttendance $data) {
                            return sprintf('%s / %s<p class="text-muted">%s</p>', $data->semester->name, Yii::$app->formatter->asDate($data->lesson_date->getTimestamp()), $data->lessonPair->getFullName());
                        },
                    ],
                    [
                        'format' => 'raw',
                        'value' => function (EAttendance $data) {
                            return $data->attendanceActivity && $data->attendanceActivity->file ? Html::a('<i class="fa fa-download"></i> ' . __($data->absent_on ? 'Absent On' : 'Absent Off'), currentTo(['download' => $data->attendanceActivity->id]), ['data-pjax' => 0]) : (__($data->absent_on ? 'Absent On' : 'Absent Off'));
                        }
                    ]
                ],
            ]); ?>
        </div>

        <?php
        $this->registerJs("$('#data-grid input[type=\"checkbox\"]').on('change',function(){updateSelectedStudents()})")
        ?>
        <?php Pjax::end() ?>
    </div>

    <div class="col col-md-4" id="sidebar">
        <div class="box box-default ">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => true, 'options' => ['id' => 'transfer-form', 'data-pjax' => 0]]); ?>
            <div class="box-body">
                <?= $form->field($activity, 'status')->widget(Select2Default::classname(), [
                    'data' => EAttendance::getValueOptions(),
                    'allowClear' => false,
                    'options' => [
                        'value' => EAttendance::ATTENDANCE_ABSENT_ON,
                    ]
                ])->label(__('Status')); ?>
                <?= $form->field($activity, 'reason')->textarea(['rows' => 4])->label(); ?>
                <?= $form->field($activity, 'file')
                    ->widget(UploadDefault::className(), [
                        'url' => ['dashboard/file-upload', 'type' => 'ad'],
                        'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png|pdf)$/i'),
                        'sortable' => true,
                        'accept' => 'application/pdf,image/*',
                        'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                        'maxNumberOfFiles' => 1,
                        'multiple' => false,
                        'useCaption' => false,
                        'clientOptions' => [],
                    ])->label(__('Asoslovchi fayl')); ?>
                <?= $form->field($activity, 'selectedStudents')->hiddenInput(['readonly' => true])->label(false); ?>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Apply'), [
                    'class' => 'btn btn-primary btn-flat',
                    'onclick' => 'return confirmTransfer()'
                ]) ?>
            </div>
        </div>
    </div>
</div>

<script>
    function updateSelectedStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        $('#eattendanceactivity-selectedstudents').val(keys.join(','))
    }

    function confirmTransfer() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert(<?=json_encode([__('Please, select students')])?>[0])
        } else {
            if (confirm(<?=json_encode([__('Are you sure to apply the document to {count} attendance?')])?>[0].replace('{count}', keys.length))) {
                $('#transfer-form').submit();
            }
        }

        return false;
    }
</script>




