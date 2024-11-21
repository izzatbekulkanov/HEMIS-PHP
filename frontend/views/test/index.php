<?php

use backend\widgets\Select2Default;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\SubjectTaskStudent;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $this \frontend\components\View
 * @var $searchModel SubjectTaskStudent
 */
$this->title = $searchModel->subject ? __('{subject} fanidan mavzuli testlar', ['subject' => $searchModel->subject->name]) : null;
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
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Topic / Employee')])->label(false); ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <?php
            $hours = 0;
            ?>
            <?= \backend\widgets\GridView::widget([
                'id' => 'data-grid',
                'dataProvider' => $dataProvider,
                'emptyText' => __('Ma\'lumotlar mavjud emas'),
                'columns' => [
                    [
                        'attribute' => '_subject',
                        'format' => 'raw',
                        'options' => ['width' => '30%'],
                        'value' => function (SubjectTaskStudent $data, $index, $i) {
                            return sprintf("%s. %s <p class='text-muted'>%s / %s</p>", $i + 1, $data->subject->name, $data->trainingType->name, $data->employee->getShortName());
                        },
                    ],

                    [
                        'header' => __('Subject Topic'),
                        'format' => 'raw',
                        'options' => ['width' => '50%'],
                        'value' => function (SubjectTaskStudent $data) {
                            if ($data->subjectResource) {
                                return sprintf("%s <p class='text-muted'>%s</p>", $data->subjectResource->subjectTopic->name, __('{count} ta savol', ['count' => intval($data->subjectResource->test_question_count)]));
                            }
                        },
                    ],
                    [
                        'attribute' => 'correct',
                        'label' => __('Natija'),
                        'options' => ['width' => '20%'],
                        'format' => 'raw',
                        'value' => function (SubjectTaskStudent $data) {
                            return sprintf('%s / %s <p class="text-muted">%s </p>', round($data->correct, 1), round($data->percent, 1) . '%', __('{attempt} ta urinish', ['attempt' => intval($data->attempt_count)]));
                        }
                    ],
                    [
                        'format' => 'raw',
                        'contentOptions' => [
                            'class' => 'text-right'
                        ],
                        'value' => function (SubjectTaskStudent $data) use ($user) {
                            return \yii\helpers\Html::a('<i class="fa fa-check"></i> ' . __('Start Test'), ['test/start', 'id' => $data->id], ['class' => 'btn btn-primary', 'data-pjax' => 0, 'disabled' => !$data->canStartTest()]);
                        },
                    ],
                ],
            ]); ?>
        </div>

    </div>
</div>
<?php Pjax::end() ?>

