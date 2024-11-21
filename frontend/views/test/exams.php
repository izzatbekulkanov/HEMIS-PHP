<?php

use backend\widgets\Select2Default;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\StudentExam;
use frontend\models\curriculum\StudentFinalExam;
use frontend\models\curriculum\SubjectTaskStudent;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $this \frontend\components\View
 * @var $searchModel SubjectTaskStudent
 */
$this->title = __('Exam Index');
$user = $this->_user();
?>
<?php
Pjax::begin(['id' => 'test-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
<div class="row">
    <div class="col col-md-12 ">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-3">

                    </div>

                    <div class="col col-md-3">
                        <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                            'data' => StudentCurriculum::getSemesterSubjects($user, $this->getSelectedSemester()),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name')])->label(false); ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <?php
            $hours = 0;
            ?>
            <?= \backend\widgets\GridView::widget([
                'id' => 'data-grid',
                'mobile' => true,
                'dataProvider' => $dataProvider,
                'emptyText' => __('Ma\'lumotlar mavjud emas'),
                'columns' => [

                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function (StudentFinalExam $data, $index, $i) {
                            return sprintf("%s. %s <p class='text-muted'>%s / %s / %s</p>", $i + 1, $data->name, $data->subject ? $data->subject->name : '', $data->examType->name, $data->employee ? $data->employee->getShortName() : '');
                        },
                    ],
                    [
                        'attribute' => '_subject',
                        'header' => __('Your Result'),
                        'format' => 'raw',
                        'value' => function (StudentFinalExam $data, $index, $i) use ($user) {
                            if ($exam = $data->getStudentExam($user)) {
                                return sprintf('<a href="%s" data-pjax="0" target="_blank">%s / %s <br><span class="text-muted">%s / %s</span></a>',
                                    $exam->finished_at ? Url::to(['test/exam-result', 'id' => $exam->id]) : '',
                                    __('{mark} ball', ['mark' => round($exam->mark, 1)]),
                                    round($exam->percent, 1) . '%',
                                    $exam->started_at ? Yii::$app->formatter->asDatetime($exam->started_at->getTimestamp(), 'php: d.m.Y H:i') : '',
                                    __('{count} ta urinish', ['count' => $exam->attempts])
                                );
                            }
                            return '- / -';
                        },
                    ],
                    [
                        'format' => 'raw',
                        'header' => __('Max Ball'),
                        'value' => function (StudentFinalExam $data) {
                            return sprintf('%s<br><span class="text-muted">%s / %s</span>',
                                __('{mark} ball', ['mark' => round($data->max_ball, 1)]),
                                __('{count} ta savol', ['count' => $data->question_count]),
                                __('{min} daqiqa', ['min' => $data->duration]));
                        }
                    ],
                    [
                        'attribute' => 'start_at',
                        'header' => __('Start / Finish'),
                        'format' => 'raw',
                        'value' => function (StudentFinalExam $data) use ($user) {
                            $group = $data->getStudentExamGroup($user);
                            return sprintf(
                                '%s<br><span class="text-muted">%s</span>',
                                Yii::$app->formatter->asDatetime($group->getStartAtTime()->getTimestamp(), 'php: d.m.Y H:i'),
                                Yii::$app->formatter->asDatetime($group->getFinishAtTime()->getTimestamp(), 'php: d.m.Y H:i'));
                        },
                    ],

                    [
                        'format' => 'raw',
                        'contentOptions' => [
                            'class' => 'text-right'
                        ],
                        'value' => function (StudentFinalExam $data) use ($user) {
                            $exam = $data->getStudentExam($user);
                            $canJoin = $data->canJoinExam($user);
                            $options = [
                                'class' => 'btn btn-primary ' . ($canJoin ? '' : 'disabled'),
                                'data-pjax' => 0,
                                'disabled' => !$canJoin
                            ];
                            $results = "";
                            if ($exam) {
                                if ($exam->finished_at) {
                                    $results .= \yii\helpers\Html::a(__('Natijalar'), ['exam-result', 'id' => $exam->id], ['data-pjax' => 0, 'class' => 'btn btn-default', 'target' => '_blank']);
                                    $options['data-confirm'] = __('Testni qaytadan ishlaysizmi?');
                                    $results .= \yii\helpers\Html::a('<i class="fa fa-check"></i> ' . __('Start Test'), ['start-exam', 'id' => $data->id], $options);
                                } else {
                                    $results .= \yii\helpers\Html::a('<i class="fa fa-close"></i> ' . __('Yakunlash'), ['finish-exam', 'id' => $exam->exam->id], ['data-pjax' => 0, 'class' => 'btn btn-default', 'data-confirm' => __('Testni yakunlaysizmi?')]);
                                    $results .= \yii\helpers\Html::a('<i class="fa fa-check"></i> ' . __('Davom etish'), ['start-exam', 'id' => $data->id], $options);
                                }
                            } else {
                                $options['data-confirm'] = __('Testni topshirishni boshlaysizmi?');
                                $results .= \yii\helpers\Html::a('<i class="fa fa-check"></i> ' . __('Start Test'), ['start-exam', 'id' => $data->id], $options);
                            }

                            return $results;
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
<?php Pjax::end() ?>

