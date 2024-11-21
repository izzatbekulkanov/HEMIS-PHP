<?php

use backend\widgets\GridView;
use common\components\Config;
use common\components\hemis\HemisApiSyncModel;
use common\models\system\_BaseModel;
use common\models\system\SystemLog;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\queue\Queue;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel SystemLog */

$this->params['breadcrumbs'][] = $this->title;
$dataProvider = new \yii\data\ArrayDataProvider();

/**
 * @var $queue Queue
 */
$queue = \Yii::$app->queue;
$prefix = $queue->channel;
$total = $queue->redis->get("$prefix.message_id");
$queueData = [
    'Waiting' => $waiting = $queue->redis->llen("$prefix.waiting"),
    'Delayed' => $delayed = $queue->redis->zcount("$prefix.delayed", '-inf', '+inf'),
    'Reserved' => $reserved = $queue->redis->zcount("$prefix.reserved", '-inf', '+inf'),
    'Done' => $total - $waiting - $delayed - $reserved,
    'Total' => $total,
];

?>
<div class="row">
    <div class="col col-md-6 col-lg-4">
        <div class="box box-default">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?= __('Queue Status') ?></th>
                    <th><?= __('Count') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($queueData as $label => $count): ?>
                    <tr>
                        <td><?= __($label) ?></td>
                        <td><?= $count ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col col-md-6 col-lg-4">
        <div class="box box-default">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?= __('Cron Job') ?></th>
                    <th><?= __('Started At') ?></th>
                    <th><?= __('Finished At') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (['min1', 'min5', 'hour1', 'hour6', 'day1'] as $job): ?>
                    <tr>
                        <td><?= $job ?></td>
                        <td><?= ($time = Config::getCronLog($job . '_start')) ? Yii::$app->formatter->asDatetime($time) : '' ?></td>
                        <td><?= ($time = Config::getCronLog($job . '_end')) ? Yii::$app->formatter->asDatetime($time) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Pjax::begin(['id' => 'sync-status-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default">
    <?= GridView::widget([
        'dataProvider' => \common\components\hemis\HemisApi::getApiClient()->getSyncModelsStatus(),
        'id' => 'data-grid',
        'toggleAll' => true,
        'togglePos' => 'begin',
        'toggleAttribute' => 'enabled',
        'toggleLink' => currentTo(['toggle' => 1]),
        'columns' => [
            [
                'attribute' => 'name',
                'header' => __('Name'),
                'value' => function ($model) {
                    return sprintf('%s <p class="text-muted">%s</span>', __($model->name), __($model->info));
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'success',
                'header' => __('Success'),
                'value' => function ($model) {
                    return $model->success;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'fail',
                'header' => __('Fail'),
                'value' => function ($model) {
                    return $model->fail;
                },
                'format' => 'raw',
            ],
            [
                'value' => function (\common\components\hemis\SyncModel $model) {
                    return $model->enabled ? Html::a(__('Sync'), Url::current(['sync' => $model->class]), ['data-pjax' => 0]) : '';
                },
                'format' => 'raw'
            ],
            [
                'value' => function ($model) {
                    return $model->enabled ? (is_subclass_of($model->class, \common\models\report\BaseReport::class) ? Html::a(__('Generate'), Url::current(['run' => $model->class]), ['data-pjax' => 0]) : '') : '';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'actual',
                'header' => __('Sync Actual'),
                'value' => function ($model) {
                    if ($model->syncCheck)
                        return $model->actual;
                    else
                        return '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'different',
                'header' => __('Sync Diff'),
                'value' => function ($model) {
                    if ($model->syncCheck)
                        return $model->different;
                    return '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'error',
                'header' => __('Sync Error'),
                'value' => function ($model) {
                    if ($model->syncCheck)
                        return $model->error;
                    return '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'error',
                'header' => __('Sync Not Found'),
                'value' => function ($model) {
                    if ($model->syncCheck)
                        return $model->not_found;
                    return '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'not_checked',
                'header' => __('Sync Not Checked'),
                'value' => function ($model) {
                    if ($model->syncCheck)
                        return $model->not_checked;
                    return '';
                },
                'format' => 'raw',
            ],
            [
                'value' => function ($model) {
                    if ($model->syncCheck && $model->enabled)
                        return Html::a(__('Details'), Url::current(['detail' => $model->class]), ['data-pjax' => 0]);
                    return '';
                },
                'format' => 'raw'
            ],

        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
