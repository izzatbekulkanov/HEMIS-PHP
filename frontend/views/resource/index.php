<?php

use backend\widgets\Select2Default;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\SubjectResource;
use frontend\models\curriculum\SubjectTaskStudent;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $this \frontend\components\View
 * @var $searchModel SubjectTaskStudent
 */
$this->title = $searchModel->subject ? __('{subject} fanidan resurslar', ['subject' => $searchModel->subject->name]) : null;
$user = $this->_user();
?>
<?php
Pjax::begin(['id' => 'test-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
<div class="row">
    <div class="col col-md-12 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-3">
                        <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                            'data' => StudentCurriculum::getSemesterSubjects($user, $this->getSelectedSemester()),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-3">
                        <?= $form->field($searchModel, '_training_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\TrainingType::getClassifierOptions(),
                            'allowClear' => true,
                            'hideSearch' => true,
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
                'mobile' => true,
                'tableOptions' => ['class' => 'table table-big table-responsive table-striped table-hover '],
                'emptyText' => __('Ma\'lumotlar mavjud emas'),
                'columns' => [
                    [
                        'header' => __('Subject Topic'),
                        'format' => 'raw',
                        'value' => function (SubjectResource $data, $index, $i) {

                            return sprintf(" %s <p class='text-muted'>%s / %s</p>", $data->subjectTopic->name, $data->trainingType->name, $data->employee->getShortName());
                        },
                    ],

                    [
                        'header' => __('Resources'),
                        'format' => 'raw',
                        'value' => function (SubjectResource $data) {
                            if ($data->path) {
                                $link = \yii\helpers\Html::a($data->name . ' <i class="fa fa-external-link"></i>', $data->path, ['class' => '', 'data-pjax' => 0, 'target' => '_blank']);
                                return sprintf("%s <p class='text-muted'>%s</p>", $link, $data->comment);
                            } else {
                                return sprintf("%s <p class='text-muted'>%s</p>", $data->name, $data->comment);
                            }


                        },
                    ],

                    [
                        'format' => 'raw',
                        'contentOptions' => [
                            //'class' => 'text-right'
                        ],
                        'value' => function (SubjectResource $data) use ($user) {
                            if ($data->canStartTest()) {
                                return \yii\helpers\Html::a(__('Test topshirish'), ['test/start', 'id' => $data->id, 'resource' => 1], ['class' => 'btn btn-primary', 'data-pjax' => 0]);
                            } else {
                                $link = "";

                                if (is_array($data->filename)) {
                                    foreach (@$data->filename as $i => $file) {
                                        $link .= \yii\helpers\Html::a(sprintf('<i class="fa fa-download text-muted"></i>&nbsp;&nbsp;&nbsp;%s <span class="pull-right">%s</span>', $file['name'], Yii::$app->formatter->asShortSize($file['size'], 0)), ['resource/download', 'id' => $data->id, 'f' => $i], ['class' => 'download-item', 'data-pjax' => 0]);
                                    }
                                }
                                return $link;
                            }
                        },
                    ],
                ],
            ]); ?>
        </div>

    </div>
</div>
<?php Pjax::end() ?>

