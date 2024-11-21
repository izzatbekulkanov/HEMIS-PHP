<?php

use backend\widgets\GridView;
use common\components\hemis\models\SyncLog;
use common\models\system\SystemLog;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel SystemLog */

$this->params['breadcrumbs'][] = $this->title;
$api = \common\components\hemis\HemisApi::getApiClient();
?>

<?php Pjax::begin(['id' => 'review-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6 col-md-6">
            </div>
            <div class="col col-md-6 col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Model / Description / Error')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'data-grid',
        'columns' => [
            [
                'attribute' => 'model',
                'value' => function (SyncLog $model) use ($api) {
                    return $api->getModelTitle($model->model) . "<br>" . Html::tag('span', Yii::$app->formatter->asDatetime($model->created_at), ['class' => 'text-muted']);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'model',
                'value' => function (SyncLog $model) use ($api) {
                    return $model->description . "<br>" . Html::tag('span', $model->model_id, ['class' => 'text-muted']);
                },
                'format' => 'raw',
            ],
            'error',
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
