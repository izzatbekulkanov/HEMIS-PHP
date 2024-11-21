<?php

use backend\widgets\GridView;
use common\models\curriculum\EExam;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div style="margin: -15px">
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function (EExam $data) use ($model) {
                    return Html::a(sprintf('%s<br><span class="text-muted"> %s / %s / %s</span>',
                        $data->name,
                        $data->subject ? $data->subject->name : '',
                        $data->examType ? $data->examType->name : '',
                        $data->employee ? $data->employee->getShortName() : ''
                    ), ['check-overall-rating', 'id' => $model->id, 'fill_exam' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_groups',
                'header' => __('Groups'),
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return $data->getGroupsLabel();
                },
            ],
            [
                'attribute' => 'start_at',
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return $data->start_at ? Yii::$app->formatter->asDatetime($data->start_at->getTimestamp(), 'php: d.m.Y H:i') : '';
                },
            ],
            [
                'attribute' => 'finish_at',
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return $data->finish_at ? Yii::$app->formatter->asDatetime($data->finish_at->getTimestamp(), 'php: d.m.Y H:i') : '';
                },
            ],
        ],
    ]); ?>
</div>
