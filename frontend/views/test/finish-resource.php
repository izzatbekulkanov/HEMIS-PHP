<?php
/**
 * @var $model SubjectTaskStudent
 * @var $question \common\models\curriculum\ESubjectResourceQuestion
 * @var $this \frontend\components\View
 */

use frontend\models\curriculum\SubjectTaskStudent;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->addBodyClass('skin-blue layout-top-nav');
$this->title = $model->getTitle()
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
                                        'attribute' => '_student',
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return $data->student->getFullName();
                                        }
                                    ],
                                    [
                                        'attribute' => '_subject',
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return $data->subject->name;
                                        }
                                    ],
                                    [
                                        'attribute' => '_training_type',
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return $data->trainingType->name;
                                        }
                                    ],
                                    [
                                        'attribute' => '_training_type',
                                        'label' => __('Subject Topic'),
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return $data->subjectResource->subjectTopic->name;
                                        }
                                    ],

                                    [
                                        'attribute' => 'attempts',
                                        'label' => __('Attempts'),
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return $data->attempt_count;
                                        }
                                    ],
                                    [
                                        'attribute' => 'test_question_count',
                                        'label' => __('Questions Count'),
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return $data->getRealQuestionsCount();
                                        }
                                    ],
                                    [
                                        'attribute' => 'correct',
                                        'label' => __('Natija'),
                                        'format' => 'raw',
                                        'value' => function (SubjectTaskStudent $data) {
                                            return sprintf('<b>%s / %s', round($data->correct,1),  round($data->percent,1)) . '%</b>';
                                        }
                                    ],
                                ],
                            ]) ?>
                        </div>
                        <div class="box-footer text-right">
                            <a href="<?= linkTo(['test/index', 'subject' => $model->_subject]) ?>"
                               class="btn btn-default">
                                <i class="fa fa-chevron-left"></i> <?= __('Testlar ro\'yxatiga qaytish') ?>
                            </a>
                            <?php if ($model->canStartTest()): ?>
                                <a href="<?= linkTo(['test/start', 'id' => $model->id]) ?>"
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