<?php

use backend\widgets\GridView;
use common\models\system\SystemLog;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel SystemLog */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'review-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6 col-md-6">
            </div>
            <div class="col col-md-6 col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'data-grid',
        'columns' => [
            [
                'attribute' => 'admin_name',
                'value' => function ($model) {
                    return $model->admin_name;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'ip',
            ],
            [
                'attribute' => 'action',
                'value' => function (SystemLog $model) {
                    return Html::a($model->action, Url::to([$model->action]) . '?' . http_build_query($model->get), ['data-pjax' => 0]);
                },
                'format' => 'raw',
            ],

            [
                'attribute' => 'message',
                'value' => function ($model) {
                    return Html::a(strip_tags($model->getShortTitle()), Url::to(['system/system-log', 'id' => $model->id]), ['data-pjax' => 0]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
