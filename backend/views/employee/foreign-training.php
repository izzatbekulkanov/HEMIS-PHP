<?php

use common\models\employee\EEmployeeTraining;
use common\models\structure\EDepartment;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel EEmployeeTraining */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Training'),
                        ['employee/foreign-training', 'edit' => 1],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_country')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\Country::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_training_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\TrainingType::getClassifierOptions(),
                    'allowClear' => true,
                ])->label(false); ?>
            </div>

            <div class="col col-md-4">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Employee ID')])->label(false) ?>
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
                'attribute' => '_employee',
                'format' => 'raw',
                'value' => function (EEmployeeTraining $data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->employee->getFullName(), $data->employee->employee_id_number), linkTo(['employee/foreign-training', 'id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_country',
                'format' => 'raw',
                'value' => function (EEmployeeTraining $data) {
                    return sprintf("%s<p class='text-muted'> %s </p>", $data->country ? $data->country->name : '', $data->university);
                },
            ],
            [
                'attribute' => '_training_type',
                'format' => 'raw',
                'value' => function (EEmployeeTraining $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->trainingType->name, $data->specialty_name);
                },
            ],
            [
                'attribute' => 'training_contract',
                'format' => 'raw',
                'value' => function (EEmployeeTraining $data) {
                    return sprintf("%s<p class='text-muted'> %s / %s</p>", $data->training_contract, Yii::$app->formatter->asDate($data->training_date_start->getTimestamp()), Yii::$app->formatter->asDate($data->training_date_end->getTimestamp()));
                },
            ]
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
