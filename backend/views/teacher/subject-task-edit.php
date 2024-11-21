<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\MarkingSystem;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\TrainingType;
use common\models\curriculum\Semester;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use backend\widgets\DateTimePickerDefault;
use common\models\curriculum\ECurriculumSubjectExamType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use trntv\filekit\widget\Upload;
use backend\widgets\DatePickerDefault;
use common\models\curriculum\ECurriculumWeek;

/**
 * @var $model \common\models\curriculum\ESubjectTask
 */
$training = TrainingType::findOne($training_type)->name;
$this->title = "{$subject->subject->name} ($training | {$subject->semester->name} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => $this->title];
//$this->params['breadcrumbs'][] = $model->name;
$this->params['breadcrumbs'][] = $model->isNewRecord ? __('Add New Task') : $model->name;

$this->registerJs("initTaskForm()");
?>
<?php
@$semestr_start_date = Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester);
@$last_week_date = ECurriculumWeek::getLastWeekByCurriculumSemester($subject->_curriculum, $subject->_semester);

?>
<div class="row">
    <div class="col col-md-12 col-lg-12">

        <?php $form = ActiveForm::begin(['action' => Url::current(), 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <?php echo $form->field($model, '_curriculum')->hiddenInput(['value' => $subject->_curriculum, 'id' => '_curriculum'])->label(false); ?>
        <?php echo $form->field($model, '_subject')->hiddenInput(['value' => $subject->_subject, 'id' => '_subject'])->label(false); ?>
        <?php echo $form->field($model, '_semester')->hiddenInput(['value' => $subject->_semester, 'id' => '_semester'])->label(false); ?>
        <?php echo $form->field($model, '_marking_category')->hiddenInput(['value' => $subject->curriculum->_marking_system, 'id' => '_marking_category'])->label(false); ?>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= __('Task Information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, '_task_type')->widget(Select2Default::classname(), [
                            'data' => $model->getTaskTypeOptions(),
                            'allowClear' => false,
                            'options' => [
                                'id' => '_task_type',
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-6">

                        <?= $form->field($model, '_exam_type')->widget(Select2Default::classname(), [
                            'data' => ArrayHelper::map($examTypes, '_exam_type', 'examType.name'),
                            'allowClear' => false,
                            'options' => [
                                'id' => '_exam_type',
                            ],
                        ]) ?>
                    </div>
                </div>
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'comment')->textArea(['maxlength' => true]) ?>

                <div class="row">


                    <?php if ($training_type != TrainingType::TRAINING_TYPE_LECTURE): ?>
                    <div class="col-md-8">
                        <?= $form->field($model, '_subject_topic')->widget(Select2Default::classname(), [
                            'data' => ArrayHelper::map($subject_topics, 'id', 'name'),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?php else: ?>
                        <div class="col-md-12">
                            <?php endif; ?>

                            <?php if ($model->isNewRecord) : ?>
                                <?= $form->field($model, 'deadline')->widget(DateTimePickerDefault::classname(), [
                                    'options' => [
                                        'placeholder' => __('YYYY-MM-DD H:i'),
                                        'id' => 'deadline',
                                        'readonly' => true,
                                    ],
                                    'pluginOptions' => [
                                        'format' => 'php:Y-m-d H:i',
                                        'startDate' => Yii::$app->formatter->asDatetime(@$semestr_start_date->start_date->getTimestamp(), 'php:Y-m-d H:i'),
                                        'endDate' => Yii::$app->formatter->asDatetime(@$last_week_date->end_date->getTimestamp(), 'php:Y-m-d H:i'),
                                        'todayHighlight' => true,
                                    ]

                                ]); ?>
                            <?php else : ?>
                                <?= $form->field($model, 'deadline')->textInput(['value' => Yii::$app->formatter->asDatetime(@$model->deadline->getTimestamp(), 'php:Y-m-d H:i'), 'maxlength' => true, 'disabled' => true, 'readonly' => true]) ?>
                            <?php endif; ?>


                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'max_ball')->input('number', ['id' => 'max_ball', 'maxlength' => true, /*'max'=>($model->_marking_category == MarkingSystem::MARKING_SYSTEM_FIVE ? 5 : false), 'min'=>($model->_marking_category == MarkingSystem::MARKING_SYSTEM_FIVE ? 0 : false)*/]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'attempt_count')->input('number', ['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, '_language')->widget(Select2Default::classname(), [
                                'data' => \common\models\system\classifier\Language::getClassifierOptions(),
                                'allowClear' => false,
                                'disabled' => true,
                            ]) ?>
                        </div>
                    </div>
                    <div class="row" id="test_attributes"
                         style="display: <?= !$model->isTestTask() ? 'none' : 'block' ?>">
                        <div class="col-md-4">
                            <?= $form->field($model, 'test_duration')->input('number', ['id' => 'test_duration']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'question_count')->input('number', ['id' => 'question_count', 'max' => 120]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'random')->widget(Select2Default::classname(), [
                                'data' => ['1' => __('Yes'), '0' => __('No')],
                                'allowClear' => false,
                                'placeholder' => false,
                                'options' => [
                                    'value' => $model->random || $model->isNewRecord ? 1 : 0,
                                ],
                            ]) ?>
                        </div>
                    </div>
                    <div id="task_attributes" style="display: <?= $model->isTestTask() ? 'none' : 'block' ?>">
                        <?= $form->field($model, 'filename')->widget(
                            Upload::class,
                            [
                                'url' => ['dashboard/file-upload', 'type' => 'attachment'],
                                'acceptFileTypes' => new JsExpression(
                                    '/(\.|\/)(xlsx?|docx?|pdf|txt|pptx?|jpe?g|png)$/i'
                                ),
                                'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 200 MiB
                                'multiple' => true,
                                'sortable' => true,
                                'maxNumberOfFiles' => 4,
                                'clientOptions' => [],
                                'options' => ['class' => 'file'],
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?php if (is_array($model->filename)): ?>
                        <?= Html::a('<i class="fa fa-download"></i> '.__('Download'), Url::current(['download' => 1]), ['data-pjax' => 0, 'class' => 'btn btn-flat btn-default']); ?>
                    <?php endif; ?>

                    <?php if (!$model->isNewRecord): ?>
                        <?= $this->getResourceLink(__('Cancel'), ['teacher/subject-task-list',
                            'curriculum' => $subject->_curriculum,
                            'semester' => $subject->_semester,
                            'subject' => $subject->_subject,
                            'training_type' => $model->_training_type,
                            'education_lang' => $model->_language
                        ], ['class' => 'btn btn-default btn-flat']) ?>
                        <?php
                        $label = "";
                        $label = ($model->active) ? "Unpublish" : "Publish";
                        ?>
                        <? //php if (Yii::$app->formatter->asDate(time(), 'php:Y-m-d') < Yii::$app->formatter->asDate($model->deadline, 'php:Y-m-d')) :?>
                        <? /*= $this->getResourceLink($label, [''], [
                                'class' => 'showModalButton loadMainContent',
                                //'data-pjax' => 0,
                                'modal-class' => 'modal-lg',
                                //'title' => $label,
                                'value' => Url::to(['teacher/subject-task-list',
                                    'curriculum' => $subject->_curriculum,
                                    'semester' => $subject->_semester,
                                    'subject' => $subject->_subject,
                                    'training_type' => $model->_training_type,
                                    'education_lang' => $model->_language,
                                    'code' => $model->id,
                                    'active' => 1
                                ]),
                        ]) */ ?>
                        <?php
                        echo Html::a(__("Publish"), Url::current(['code' => $model->id, 'active' => 1]), [
                            'class' => 'btn btn-success btn-flat',
                            'modal-class' => 'modal-lg',
                            'title' => __("Publish"),
                            'data-pjax' => 0
                        ]);
                        ?>
                        <?php
                        /*echo Html::a(__("Publish"), '#', [
                            'class' => 'showModalButton btn btn-success btn-flat',
                            'modal-class' => 'modal-lg',
                            'title' => __("Publish"),
                            'value' => Url::current(['code' => $model->id, 'active' => 1]),
                            'data-pjax' => 0
                        ]);*/
                        ?>
                        <? //php endif; ?>
                        <?php if ($last_week_date->end_date->getTimestamp() >= time()) : ?>
                            <? //php if (Yii::$app->formatter->asDate($last_week_date->end_date) >= date("d.m.Y", time())) :?>
                            <?= $this->getResourceLink(__('Delete'), ['teacher/subject-task-list',
                                'curriculum' => $subject->_curriculum,
                                'semester' => $subject->_semester,
                                'subject' => $subject->_subject,
                                'training_type' => $model->_training_type,
                                'education_lang' => $model->_language,
                                'code' => $model->id,
                                'delete' => 1
                            ], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 1]) ?>
                        <?php endif; ?>
                    <?php else: ?>
                    <?php endif; ?>
                    <?php
                    /*if (!$model->isNewRecord):
                if (Yii::$app->formatter->asDate(time(), 'php:Y-m-d') < Yii::$app->formatter->asDate($model->deadline, 'php:Y-m-d')) :?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                <?php endif; ?>
                    <?php else: */ ?>
                    <?php if ($last_week_date->end_date->getTimestamp() >= time()) : ?>
                        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                    <?php endif; ?>
                    <? //php endif; ?>
                </div>

            </div>
            <?php ActiveForm::end(); ?>
        </div>


    </div>

    <script>
        function initTaskForm() {
            $('#_task_type').change(function () {
                initTaskType();
            });
            initTaskType();

            $('#_exam_type').change(function () {
                initExamType();
            });
            initExamType();
        }

        function initTaskType() {
            var id = parseInt($('#_task_type').val());
            if (id === <?=\common\models\curriculum\ESubjectTask::TASK_TYPE_TEST?>) {
                $("#test_attributes").show();
                $("#task_attributes").hide();
                $("#test_duration").attr('required', true);
                $("#question_count").attr('required', true);
            } else {
                $("#test_attributes").hide();
                $("#task_attributes").show();
                $("#test_duration").attr('required', false);
                $("#question_count").attr('required', false);
            }
        }

        function initExamType() {

            var id = parseInt($('#_exam_type').val());

            var curriculum = parseInt($('#_curriculum').val());
            var semester = $('#_semester').val();
            var subject = parseInt($('#_subject').val());
            var marking_category = $('#_marking_category').val();

            $.ajax({
                url: '/ajax/get-exam-type-data',
                type: "POST",
                data: {curriculum: curriculum, semester: semester, subject: subject, exam_type: id},
                dataType: "json",
                success: function (data) {
                    $("#max_ball").attr('max', data.max);
                    if (marking_category == <?=MarkingSystem::MARKING_SYSTEM_FIVE?>) {
                        $("#max_ball").attr('min', data.max);
                    } else {
                        $("#max_ball").attr('min', 0);
                    }

                },
            });
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
    <script>
        function changeAttribute(id, att) {
            var data = {};
            data.id = id;
            data.attribute = att;
            $.get('<?= Url::to(['teacher/subject-task-list'])?>', data, function (resp) {

            })
        }
    </script>


