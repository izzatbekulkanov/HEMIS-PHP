<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectResourceQuestion;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
use common\models\system\classifier\Language;
use common\models\curriculum\Semester;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use trntv\filekit\widget\Upload;

$training = $taskModel->trainingType->name;
$label = "{$taskModel->subject->name} ($training | {$taskModel->semester->name} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => $label];
$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-task-status', 'subject_task' => $taskModel->id], 'label' => __('Teacher Subject Task Status')];
$this->params['breadcrumbs'][] = $this->title;


$q = 0;
$isTest = count($dataProvider->getModels()) == 1 && $dataProvider->getModels()[0]->_task_type == \common\models\curriculum\ESubjectTask::TASK_TYPE_TEST;
?>

<?php if ($isTest): ?>
    <?php
    /**
     * @var $model EStudentTaskActivity
     */
    $model = $dataProvider->getModels()[0]; ?>
    <div class="row">
        <div class="col-md-7 ">
            <div class="box box-default question">
                <?php
                $questions = $model->subjectTaskStudent->getUserQuestions(false);
                ?>
                <?php foreach ($questions as $i => $data): ?>
                    <?php
                    $q++;
                    $question = $data['q'];
                    if (!($question instanceof \common\models\curriculum\ESubjectResourceQuestion)) continue;

                    $selected = isset($data['s']) ? $data['s'] : [];
                    ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= $q ?>. <?= $question->name ?></h3>
                    </div>
                    <div class="box-body checkbo">
                        <?php foreach ($data['a'] as $v => $variant): ?>
                            <?php
                            $variants = $question->answers;
                            $type = $question->isMultiple() ? 'checkbox' : 'radio';
                            $checked = isset($selected[$variant]) ? 'selected' : '';
                            $correct = array_key_exists($variant, $selected) && in_array($variant, $question->_answer) ? 'correct' : '';
                            ?>
                            <p>
                                <label class="<?= $checked ?> <?= $correct ?>"
                                       for="test_question_<?= $q ?>_<?= $v ?>">
                                            <span class="qv">
                                                <?php if ($correct): ?>
                                                    <i class="fa fa-check marker" style=""></i>
                                                <?php elseif ($checked): ?>
                                                    <i class="fa fa-close marker" style=""></i>
                                                <?php endif; ?>
                                                <?= @$variants[$variant] ?>
                                            </span>
                                </label>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-5 ">
            <div class="box box-default">
                <div class="box-header with-border hidden-print">
                    <h3 class="box-title"><?= __('Natijalar') ?></h3>
                </div>
                <div class="box-body">
                    <table class="table-striped table">
                        <tbody>
                        <tr>
                            <th><?= __('Task') ?></th>
                            <td><?= $model->subjectTask->name ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Student') ?></th>
                            <td><?= $model->student->getFullName() ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Started At') ?></th>
                            <td><?= $model->started_at ? Yii::$app->formatter->asDatetime($model->started_at->getTimestamp(), 'php: d.m.Y H:i') : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Finished At') ?></th>
                            <td><?= $model->finished_at ? Yii::$app->formatter->asDatetime($model->finished_at->getTimestamp(), 'php: d.m.Y H:i') : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Correct') ?></th>
                            <td><?= round($model->correct) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Percent') ?></th>
                            <td><?= $model->percent_c ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <button class="btn btn-primary btn-flat hidden-print"
                            onclick="window.print();"
                    ><?= __('Chop etish') ?></button>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row hidden-print">
        <div class="col col-md-8 col-lg-8">
            <div class="box box-default ">
                <?php if (count($dataProvider->getModels())): ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= __('List Answers') ?></h3>
                    </div>

                    <?= GridView::widget([
                        'id' => 'data-grid',
                        'layout' => "<div class='box-body no-padding'>{items}</div>",
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'headerOptions' => [
                                    'style' => 'width:3%',
                                ],
                            ],
                            [
                                'attribute' => 'comment',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(htmlentities($data->comment), ['teacher/answer-list',
                                        'subject_task' => $data->_subject_task,
                                        'student' => $data->_student,
                                        'code' => $data->id
                                    ], ['data-pjax' => 0]);
                                },
                            ],
                            /*[
                                'attribute' => 'filename',
                                'format' => 'raw',
                                'headerOptions' => [
                                    'style' => 'width:15%',
                                ],
                                'value' => function ($data) {
                                    if ($data->filename) {
                                        return Html::a($data->filename['name'], $data->filename['base_url'] . '/' . $data->filename['path']);
                                    }
                                },
                            ],*/
                            [
                                'attribute' => 'send_date',
                                'format' => 'raw',
                                'headerOptions' => [
                                    'style' => 'width:15%',
                                ],
                                'value' => function ($data) {
                                    return $data->send_date != "" ? @Yii::$app->formatter->asDateTime($data->send_date->getTimestamp()) : '';
                                },
                            ],
                            [
                                'attribute' => 'mark',
                                'headerOptions' => [
                                    'style' => 'width:10%',
                                ],
                                'value' => function ($data) {
                                    if ($data->mark >= 0)
                                        return $data->mark;
                                    else
                                        return '-';
                                }
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
                            ]
                            /*[
                                'attribute' => 'active',
                                'headerOptions' => [
                                    'style' => 'width:5%',
                                ],
                                'label' => __('Status'),

                                'value' => function ($data) {
                                    if($data->mark >0 )
                                        return $data->statusOptions[$data->active];
                                    else
                                        return '-';
                                }
                            ],*/
                        ],
                    ]); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($task): ?>
            <div class="col col-md-4 col-lg-4" id="sidebar">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h4 class="box-title"><?= __('Information') ?></h4>
                    </div>

                    <div class="box-body no-padding">
                        <?= DetailView::widget([
                            'model' => $task,
                            'attributes' => [
                                [
                                    'attribute' => 'filename',
                                    'format' => 'raw',
                                    /*'value' => function ($data) {
                                        if ($data->filename) {
                                            return Html::a(@$data->filename['name'], @$data->filename['base_url'] . '/' . @$data->filename['path'], ['target'=>'_blank']);
                                        }
                                    },*/
                                    'value' => function ($data) {
                                        @$link = "";
                                        if (!empty($data->filename)) {
                                            if (is_array($data->filename)) {
                                                if (isset($data->filename['name'])) {
                                                    return Html::a(htmlentities($data->filename['name']), htmlentities($data->filename['base_url']) . '/' . htmlentities($data->filename['path']));
                                                } else {
                                                    foreach (@$data->filename as $file) {
                                                        @$link .= Html::a(htmlentities(@$file['name']), htmlentities(@$file['base_url']) . '/' . htmlentities(@$file['path']), ['data-pjax' => 0]) . '; ';
                                                    }
                                                    return @$link;
                                                }
                                            }
                                        }
                                    },
                                ],

                                [
                                    'attribute' => 'mark',
                                    'format' => 'raw',
                                    'visible' => $task->active,
                                    'value' => function ($data) {
                                        return $data->mark;
                                    },
                                ],
                                [
                                    'attribute' => 'marked_comment',
                                    'visible' => $task->active,
                                    'value' => function ($data) {
                                        return $data->marked_comment;
                                    }
                                ],
                                [
                                    'attribute' => 'marked_date',
                                    'format' => 'raw',
                                    'visible' => $task->active,
                                    'value' => function ($data) {
                                        return Yii::$app->formatter->asDateTime($data->marked_date->getTimestamp());
                                    },
                                ],

                            ],
                        ]) ?>
                    </div>
                    <br/>

                    <?php if ($task_meta->_task_status != \common\models\curriculum\ESubjectTaskStudent::TASK_STATUS_RATED && !isset($task->mark)): ?>
                        <div class="box box-default ">
                            <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= __('Marking') ?></h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col col-md-12">
                                        <?= $form->field($task, 'mark')->textInput([
                                            'type' => 'number',
                                            'maxlength' => true,
                                            'min' => 0,
                                            'max' => $task->subjectTask->max_ball,
                                            'step' => 0.1,
                                        ])->label(__('Ball') . ' (' . __('Max Ball') . ': ' . $task->subjectTask->max_ball . ')') ?>
                                        <?= $form->field($task, 'marked_comment')->textArea(['maxlength' => true, 'rows' => 3]) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer text-right">
                                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['teacher/answer-list'])?>', data, function (resp) {

        })
    }
</script>