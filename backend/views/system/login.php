<?php

use backend\components\View;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\SystemLogin;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $searchModel SystemLogin */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'log-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6 col-md-2"></div>
            <div class="col col-md-6 col-md-2">
                <?= $form->field($searchModel, 'user')->widget(Select2Default::classname(), [
                    'data' => SystemLogin::getUserOptions(),
                    'placeholder' => __('Choose User'),
                ])->label(false) ?>
            </div>
            <div class="col col-md-6 col-md-2">

                <?= $form->field($searchModel, 'status')->widget(Select2Default::classname(), [
                    'data' => SystemLogin::getStatusOptions(),
                    'placeholder' => __('Choose Status'),
                ])->label(false) ?>
            </div>
            <div class="col col-md-6 col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['autofocus' => true, 'placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'data-grid',
        'columns' => [
            [
                'attribute' => 'ip',
            ],
            [
                'attribute' => 'login',
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->getStatusLabel();
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                },
            ],
            [
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(__('Delete'), ['system/login', 'id' => $data->id, 'delete' => 1], ['class' => 'btn-delete']);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
