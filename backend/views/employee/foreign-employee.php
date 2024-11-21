<?php

use common\models\employee\EEmployeeForeign;
use common\models\structure\EDepartment;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel EEmployeeForeign */
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Foreign Employee'),
                        ['employee/foreign-employee', 'edit' => 1],
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
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Specialty Name / Work Place')])->label(false) ?>
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
                'value' => function (EEmployeeForeign $data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->full_name, $data->country->name), linkTo(['employee/foreign-employee', 'id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'specialty_name',
                'format' => 'raw',
                'value' => function (EEmployeeForeign $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->specialty_name, $data->work_place);
                },
            ],
            [
                'attribute' => 'subject',
                'format' => 'raw',
                'value' => function (EEmployeeForeign $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->subject, $data->educationYear->name);
                },
            ],
            [
                'attribute' => 'contract_date',
                'format' => 'raw',
                'value' => function (EEmployeeForeign $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->contract_data, Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp()));
                },
            ]
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
