<?php

use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EExamStudent;
use common\models\system\classifier\Language;
use backend\widgets\Select2Default;
use common\models\system\classifier\TrainingType;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/**
 * @var $model \common\models\curriculum\EExam
 */
$this->title = __('Exam Results');

$this->params['breadcrumbs'][] = ['url' => ['exam/index'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => ['exam/edit', 'id' => $model->id,], 'label' => $model->name];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'test-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-primary ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-5">
                    </div>

                    <div class="col col-md-5">
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => \yii\helpers\ArrayHelper::map($model->groups, 'id', 'name'),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <div class="form-group">
                            <?= Html::a('<i class="fa fa-download"></i> '.__('Download'), currentTo(['download' => 1]), ['class' => 'btn btn-flat btn-success btn-block w-100', 'data-pjax' => 0]) ?>
                        </div>
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
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data, $index, $i) {
                            return Html::a($data->student->getFullName(), currentTo(['item' => $data->id]), ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => '_group',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data, $index, $i) {
                            return $data->group->name;
                        },
                    ],
                    [
                        'attribute' => 'ip',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data, $index, $i) {
                            return $data->ip . "";
                        },
                    ],
                    'attempts:integer',
                    'correct:integer',
                    [
                        'attribute' => 'mark',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data, $index, $i) {
                            return __('{mark} ball', ['mark' => round($data->mark, 1)]);
                        },
                    ],
                    [
                        'attribute' => 'percent',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data, $index, $i) {
                            return round($data->percent, 1) . '%';
                        },
                    ],
                    [
                        'attribute' => 'started_at',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data) {
                            return $data->started_at ? Yii::$app->formatter->asDatetime($data->started_at->getTimestamp(), 'php: d.m.Y H:i') : '';
                        },
                    ],
                    [
                        'attribute' => 'time',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data, $index, $i) {
                            return __('{min} daqiqa', ['min' => ceil($data->time / 60)]);
                        },
                    ],
                    [
                        'attribute' => 'finished_at',
                        'format' => 'raw',
                        'value' => function (EExamStudent $data) {
                            return $data->finished_at ? Yii::$app->formatter->asDatetime($data->finished_at->getTimestamp(), 'php: d.m.Y H:i') : '';
                        },
                    ],

                ],
            ]); ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>


