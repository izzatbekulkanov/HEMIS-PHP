<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use common\models\system\Admin;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_department')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'placeholder' => __('-Choose Faculty-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Full Name / Login / Email')])->label(false) ?>
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
                'value' => function (Admin $data) {
                    return Html::a(sprintf("%s <p class='text-muted'>%s</p>", $data->full_name, $data->employee ? $data->employee->getStaffPositionsLabel() : ''), currentTo(['id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_department',
                'format' => 'raw',
                'value' => function (Admin $data) {
                    return $data->employee ? implode(', ', ArrayHelper::getColumn($data->employee->departments, 'name')) : '';
                },
            ],
            [
                'header' => __('Tutor Groups'),
                'format' => 'raw',
                'value' => function (Admin $data) {
                    return implode(', ', ArrayHelper::getColumn($data->tutorGroups, 'name'));
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
<?php Pjax::end() ?>
