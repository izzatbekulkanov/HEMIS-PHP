<?php
/**
 * @var $model EExamStudent
 * @var $question \common\models\curriculum\ESubjectResourceQuestion
 * @var $this \frontend\components\View
 */

use common\models\curriculum\EExamStudent;
use frontend\models\curriculum\SubjectTaskStudent;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->addBodyClass('skin-blue layout-top-nav');
$this->title = $model->exam->name
?>
<div class="wrapper" style="height: auto; min-height: 100%;">
    <div class="content-wrapper" style="min-height: 498px;">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mt30"></div>
                <div class="col-md-6 mt30">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= __('Test natijasi') ?></h3>
                        </div>
                        <div class="box-body no-padding">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'attribute' => '_exam',
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return $data->exam->name;
                                        }
                                    ],
                                    [
                                        'attribute' => '_student',
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return $data->student->getFullName();
                                        }
                                    ],
                                    [
                                        'attribute' => '_subject',
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return $data->exam->subject ? $data->exam->subject->name : '';
                                        }
                                    ],
                                    [
                                        'attribute' => 'started_at',
                                        'label' => __('Started At'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return Yii::$app->formatter->asDatetime($data->started_at->getTimestamp(), 'php: d.m.Y H:i');
                                        }
                                    ],
                                    [
                                        'attribute' => 'finished_at',
                                        'label' => __('Finished At'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return Yii::$app->formatter->asDatetime($data->finished_at->getTimestamp(), 'php: d.m.Y H:i');
                                        }
                                    ],
                                    [
                                        'attribute' => 'time',
                                        'label' => __('Duration'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return __('{min} daqiqa', ['min' => ceil($data->time / 60)]);
                                        }
                                    ],
                                    [
                                        'attribute' => 'attempts',
                                        'label' => __('Attempts'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return $data->attempts;
                                        }
                                    ],
                                    [
                                        'attribute' => 'questions_count',
                                        'label' => __('Questions Count'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return $data->getRealQuestionsCount();
                                        }
                                    ],
                                    [
                                        'attribute' => 'mark',
                                        'label' => __('Mark'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return $data->mark ? round($data->mark, 1) : '';
                                        }
                                    ],
                                    [
                                        'attribute' => 'correct',
                                        'label' => __('Natija'),
                                        'format' => 'raw',
                                        'value' => function (EExamStudent $data) {
                                            return sprintf('<b>%s / %s', round($data->correct, 1), round($data->percent, 1)) . '%</b>';
                                        }
                                    ],
                                ],
                            ]) ?>
                        </div>
                        <div class="box-footer text-right">
                            <a href="<?= linkTo(['test/exams']) ?>"
                               class="btn btn-default">
                                <i class="fa fa-chevron-left"></i> <?= __('Imtihonlar ro\'yxatiga qaytish') ?>
                            </a>
                            <?php if ($model->canJoinTest()): ?>
                                <a href="<?= linkTo(['test/start-exam', 'id' => $model->_exam]) ?>"
                                   data-confirm="<?= \yii\helpers\Html::encode(__('Testni qaytadan ishlaysizmi?')) ?>"
                                   class="btn btn-primary">
                                    <?= __('Testni qayta ishlash') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>