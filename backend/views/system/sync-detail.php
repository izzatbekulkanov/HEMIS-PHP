<?php

use backend\widgets\GridView;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\models\SyncLog;
use common\models\system\SystemLog;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel SystemLog */
$client = \common\components\hemis\HemisApi::getApiClient();
$this->title = $client->getModelTitle($class);

$this->params['breadcrumbs'][] = ['url' => ['system/sync-status'], 'label' => __('System Sync Status')];
$this->params['breadcrumbs'][] = $this->title;
$api = \common\components\hemis\HemisApi::getApiClient();
?>

<?php Pjax::begin(['id' => 'review-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-5">
                <div class="form-group">
                    <div class="btn-group ">
                        <?= $this->getResourceLink(
                             __('Check All Not Checked'),
                            ['system/sync-status', 'detail' => $class, 'check' => HemisApiSyncModel::STATUS_NOT_CHECKED],
                            ['class' => 'btn btn-flat  btn-success ', 'data-pjax' => 0]
                        ) ?>
                        <button type="button" class="btn btn-success btn-flat dropdown-toggle"
                                data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a data-pjax="0"
                                   href="<?= Url::to(['system/sync-status', 'detail' => $class, 'check' => HemisApiSyncModel::STATUS_DIFFERENT]) ?>">
                                    <?= __('Check All Different') ?>
                                </a>
                            </li>
                            <li>
                                <a data-pjax="0"
                                   href="<?= Url::to(['system/sync-status', 'detail' => $class, 'check' => HemisApiSyncModel::STATUS_ERROR]) ?>">
                                    <?= __('Check All Error') ?>
                                </a>
                            </li>
                            <li>
                                <a data-pjax="0"
                                   href="<?= Url::to(['system/sync-status', 'detail' => $class, 'check' => HemisApiSyncModel::STATUS_NOT_FOUND]) ?>">
                                    <?= __('Check All Not Found') ?>
                                </a>
                            </li>
                            <li>
                                <a data-pjax="0"
                                   href="<?= Url::to(['system/sync-status', 'detail' => $class, 'check' => HemisApiSyncModel::STATUS_ACTUAL]) ?>">
                                    <?= __('Check All Actual') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col  col-md-3">
                <?= $form->field($searchModel, '_sync_status')->widget(\backend\widgets\Select2Default::classname(), [
                    'data' => HemisApiSyncModel::getSyncStatusOptions(),
                    'hideSearch' => true,

                ])->label(false) ?>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'data-grid',
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function (HemisApiSyncModel $model) use ($api) {
                    return $model->getIdForSync();
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'model',
                'value' => function (HemisApiSyncModel $model) use ($api) {
                    return $model->getDescriptionForSync();
                },
                'format' => 'raw',
            ],
            [
                'attribute' => '_sync_status',
                'value' => function (HemisApiSyncModel $model) use ($api) {
                    return $model->getSyncStatusLabel();
                },
                'format' => 'raw',
            ],
            [
                'attribute' => '_sync_diff',
                'value' => function (HemisApiSyncModel $model) use ($api) {
                    return "<span class='no-wrap'>" . $model->getSyncDiffAsLabel() . "</span>";
                },
                'format' => 'raw',
            ],
            [
                'attribute' => '_sync_date',
                'value' => function (HemisApiSyncModel $model) use ($api) {
                    return $model->_sync_date instanceof DateTime ? Yii::$app->formatter->asDatetime($model->_sync_date) : '';
                },
                'format' => 'raw',
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
