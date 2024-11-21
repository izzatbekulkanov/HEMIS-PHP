<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\attendance\EAttendanceActivity;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

//$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => $this->title];
//$this->params['breadcrumbs'][] = $subject->curriculum->name;
//$this->params['breadcrumbs'][] = $subject->semester->name;
//$this->params['breadcrumbs'][] = $subject->subject->name;

?>
<?php Pjax::begin(['id' => 'resources-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">


    <div class="box-body">
        <?= GridView::widget([
            'id' => 'data-grid',
           // 'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
            //'layout' => '{items}',
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => '_subject_task',
                    /*'contentOptions' => [
                        'class' => 'nowrap'
                    ],*/
                    'value' => function ($data) {
                        return $data->subjectTask ? $data->subjectTask->name : '';
                    },
                ],
                [
                    'attribute' => '_subject_task',
                    'header' => __('Task Type'),
                    'contentOptions' => [
                        'class' => 'nowrap'
                    ],
                    'value' => function ($data) {
                        return $data->subjectTask ? $data->subjectTask->getTaskTypeLabel() : '';
                    },
                ],
                [
                    'attribute' => '_final_exam_type',
                    'value' => function ($data) {
                        return $data->finalExamType ? $data->finalExamType->name : '';
                    }
                ],
                [
                    'attribute' => 'mark',
                    'value' => function ($data) {
                        return $data->mark;
                    },
                ],

                [
                    'attribute' => 'created_at',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                    },
                ],

            ],
        ]); ?>
    </div>
    <div class='box-footer text-right'>
        <button type="button" class="btn btn-flat btn-default"
                data-dismiss="modal"><?= __('Close') ?></button>
    </div>

</div>
<?php Pjax::end() ?>
