<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\academic\EDecree;
use common\models\structure\EDepartment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $searchModel EDecree */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = __('Student Decree');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">

            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_decree_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\DecreeType::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Number')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::className()
            ],
            'number',

            [
                'attribute' => 'date',
                'format' => 'raw',
                'value' => function (EDecree $data) {
                    return $data->date ? Yii::$app->formatter->asDate($data->date->getTimestamp()) : '';
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function (EDecree $data) {
                    return $data->name;
                },
            ],
            [
                'attribute' => '_decree_type',
                'format' => 'raw',
                'value' => function (EDecree $data) {
                    return $data->decreeType->name;
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
                    return Html::a("<i class='fa fa-download'></i> " . __('Download'), ['student/decree', 'file' => $data->id], ['data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
