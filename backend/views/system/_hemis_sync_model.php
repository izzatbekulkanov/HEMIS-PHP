<?php
/**
 * @var $model \common\components\hemis\HemisApiSyncModel
 */

use yii\helpers\Html;

?>
<?php if (!$model->isNewRecord && $model->isSyncEnabled()): ?>
    <div class="box box-default">
        <table class="table table-striped no-margin">
            <tbody>
            <tr>
                <td><?= __('Record Title') ?></td>
                <td><?= $model->getDescriptionForSync() ?></td>
            </tr>
            <tr>
                <td><?= __('Sync Status') ?></td>
                <td><?= $model->getSyncStatusLabel() ?></td>
            </tr>
            <tr>
                <td><?= __('Sync Date') ?></td>
                <td><?= $model->_sync_date instanceof DateTime ? Yii::$app->formatter->asDatetime($model->_sync_date) : '' ?></td>
            </tr>
            <?php
            __('Sync Differences');
            if ($model->_sync_status == \common\components\hemis\HemisApiSyncModel::STATUS_DIFFERENT): ?>
                <tr>
                    <td><?= __('Sync Differences') ?></td>
                    <td><?= $model->getSyncDiffAsLabel() ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="box-footer text-right">
            <?= Html::a(__('Check Sync Data'), currentTo(['check' => 1]), ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
        </div>
    </div>
<?php endif; ?>