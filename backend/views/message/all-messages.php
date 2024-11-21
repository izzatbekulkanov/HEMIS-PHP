<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\AdminMessage;
use common\models\system\AdminResource;
use common\models\system\SystemClassifier;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-sm-4">
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="col col-sm-8">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Title / Sender')])->label(false) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <?= GridView::widget([
                'id' => 'data-grid',
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => '_sender',
                        'format' => 'raw',
                        'value' => function (AdminMessage $data) {
                            return Html::a($data->sender ? $data->sender->getFullName() : '', ['message/all-messages', 'id' => $data->id], ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => 'title',
                        'format' => 'raw',
                        'value' => function (AdminMessage $data) {
                            return Html::a($data->getShortTitle(), ['message/all-messages', 'id' => $data->id], ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($data) {
                            $class = $data->isNotSent() ? 'default' : 'success';
                            return "<span class='label label-$class'>{$data->getStatusLabel()}</span>";
                        },
                    ],
                    [
                        'attribute' => 'send_on',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return $data->send_on ? Yii::$app->formatter->asDatetime($data->send_on->getTimestamp()) : '';
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
    </div>
</div>
<?php Pjax::end() ?>
