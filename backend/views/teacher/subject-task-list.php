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
$semester = Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester)->name;
$this->title = "{$subject->subject->name} ($training | {$semester} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => $this->title];

//$this->params['breadcrumbs'][] = $this->title;
//$this->registerJs("initTaskForm()");
?>
<?php
@$semestr_start_date = Semester::getByCurriculumSemester($model->_curriculum, $model->_semester);
@$last_week_date = ECurriculumWeek::getLastWeekByCurriculumSemester($subject->_curriculum, $subject->_semester);

?>
<div class="row">

    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header with-border">


                <div class="row" id="data-grid-filters">
                    <div class="col col-md-6">
                        <h3 class="form-group"><?= __('List Tasks') . '(' . __('Maks. ball') . ': ' . $ball_summary . ')'; ?></h3>
                    </div>

                    <div class="col col-md-6">
                        <div class="form-group pull-right">
                            <?php if ($last_week_date->end_date->getTimestamp() >= time()) { ?>
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-plus-circle"></i> ' . __('Create Task'),
                                    ['teacher/subject-task-list', 'curriculum' => $subject->_curriculum,
                                        'semester' => $subject->_semester,
                                        'subject' => $subject->_subject,
                                        'training_type' => $training_type,
                                        'education_lang' => $education_lang,
                                        'edit' => 1
                                    ],
                                    ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                                ) ?>
                            <?php } ?>
                        </div>
                    </div>


                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    // 'sortable' => true,
                    //'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn',
                            'headerOptions' => [
                                'style' => 'width:3%',
                            ],
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $link = Html::a($data->name, ['teacher/subject-task-list',
                                    'curriculum' => $data->_curriculum,
                                    'semester' => $data->_semester,
                                    'subject' => $data->_subject,
                                    'training_type' => $data->_training_type,
                                    'education_lang' => $data->_language,
                                    'code' => $data->id,
                                    'edit' => 1,

                                ], ['data-pjax' => 0]);

                                return sprintf("%s<p class='text-muted'>%s / %s / %s / %s</p>", $link, $data->taskTypeOptions[$data->_task_type], $data->statusOptions[$data->active], __('{max} ball', ['max' => $data->max_ball]), $data->examType->name);
                            },
                        ],
                        [
                            'header' => __('Savol / Fayl'),
                            'format' => 'raw',
                            'value' => function (\common\models\curriculum\ESubjectTask $data) {
                                if ($data->isTestTask()) {
                                    return Html::a(__('{count} questions', ['count' => $data->getTestQuestions()->count()]), Url::current(['code' => $data->id, 'questions' => 1]), ['data-pjax' => 0]);
                                } elseif (is_array($data->filename)) {
                                    return Html::a(__('{count} ta fayl', ['count' => count($data->filename)]), Url::current(['code' => $data->id, 'download' => 1]), ['data-pjax' => 0]);
                                }
                                return '-';
                            },
                        ],
                        [
                            'attribute' => 'deadline',
                            'format' => 'raw',

                            'value' => function ($data) {
                                return Yii::$app->formatter->asDatetime($data->deadline->getTimestamp(), 'php:d.m.Y H:i');
                                //return Yii::$app->formatter->asDatetime($data->deadline->getTimestamp());
                            },
                        ],
                        [
                            'attribute' => 'active',
                            'header' => __('New Students'),
                            'format' => 'raw',

                            'value' => function ($data) use ($groups) {
                                $real_students = 0;
                                foreach ($groups as $group) {
                                    $real_students += count(EStudentSubject::getStudentsByYearSemesterGroup($data->_curriculum, $data->_education_year, $data->_semester, $data->_subject, $group->_group));
                                }
                                $label = $real_students - count($data->subjectTaskStudents);
                                if ($label > 0 && count($data->subjectTaskStudents) > 0) {
                                    /*if (Yii::$app->formatter->asDate(time(), 'php:Y-m-d') < Yii::$app->formatter->asDate($data->deadline, 'php:Y-m-d')) {
                                        return '<span class="badge bg-red">' . $this->getResourceLink($label, ['teacher/subject-task-list',
                                                'curriculum' => $data->_curriculum,
                                                'semester' => $data->_semester,
                                                'subject' => $data->_subject,
                                                'training_type' => $data->_training_type,
                                                'education_lang' => $data->_language,
                                                'code' => $data->id,
                                                'newactive' => 1,
                                            ], ['class' => '', 'data-pjax' => 0, 'style' => 'color:white;']) . '</span>';
                                    }
                                    else{*/
                                    return '<span class="badge bg-red">' . $label . '</span>';
                                    //}
                                } else {
                                    return '-';
                                }
                            },
                        ],
                        [
                            'attribute' => 'active',
                            'header' => __('Students'),
                            'format' => 'raw',

                            'value' => function ($data) {
                                $label = count($data->subjectTaskGivenStudents) . '<span class="separator"> / </span>'
                                    . count($data->subjectTaskPassedStudents) . '<span class="separator"> / </span>'
                                    . count($data->subjectTaskRatedStudents);
                                return '<span class="badge bg-primary">' . Html::a($label, ['teacher/subject-task-status',
                                        'subject_task' => $data->id,
                                    ], ['data-pjax' => 0, 'style' => 'color:white;']) . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'active',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return CheckBo::widget([
                                    'type' => 'switch',
                                    'options' => [
                                        'onclick' => "changeAttribute('$data->id', 'active')",
                                    ],
                                    'name' => $data->id,
                                    'value' => $data->active
                                ]);
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

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
        $.get('<?= Url::current(['teacher/subject-task-list'])?>', data, function (resp) {

        })
    }
</script>