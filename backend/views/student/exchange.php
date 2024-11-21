<?php

use common\models\student\EStudentExchange;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel EStudentExchange */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Student Exchange'),
                        ['student/exchange', 'edit' => 1],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, 'exchange_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\student\EStudentExchange::getExchangeTypeOptions(),
                    'allowClear' => true,
                    'hideSearch' => true,
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_country')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\Country::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-5">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Specialty Name / Document')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'full_name',
                'format' => 'raw',
                'value' => function (EStudentExchange $data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->full_name, $data->educationYear->name), linkTo(['student/exchange', 'id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function (EStudentExchange $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->educationType->name, $data->getExchangeTypeLabel());
                },
            ],
            [
                'attribute' => 'university',
                'format' => 'raw',
                'value' => function (EStudentExchange $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->university, $data->country->name);
                },
            ],
            [
                'attribute' => 'exchange_document',
                'format' => 'raw',
                'value' => function (EStudentExchange $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->exchange_document, Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp()));
                },
            ],

        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
