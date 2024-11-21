<?php

use common\models\employee\EEmployeeAcademicDegree;
use common\models\structure\EDepartment;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel EEmployeeAcademicDegree */
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Academic Rank'),
                        ['employee/academic-degree', 'edit' => 1],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $form->field($searchModel, 'diploma_type')->widget(Select2Default::classname(), [
                        'data' => EEmployeeAcademicDegree::getTypeOptions(),
                        'allowClear' => true,

                    ])->label(false); ?>
                </div>
            </div>
            <div class="col col-md-2">
                <?php if ($searchModel->diplomaTypeIsRank()): ?>
                    <?= $form->field($searchModel, '_academic_rank')->widget(Select2Default::classname(), [
                        'data' => \common\models\system\classifier\AcademicRank::getClassifierOptions(),
                        'allowClear' => true,
                        'disabled' => $searchModel->diploma_type == null
                    ])->label(false); ?>
                <?php else: ?>
                    <?= $form->field($searchModel, '_academic_degree')->widget(Select2Default::classname(), [
                        'data' => \common\models\system\classifier\AcademicDegree::getClassifierOptions(),
                        'allowClear' => true,
                        'disabled' => $searchModel->diploma_type == null
                    ])->label(false); ?>
                <?php endif; ?>

            </div>
            <div class="col col-md-6">
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
                'value' => function (EEmployeeAcademicDegree $data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->employee->getFullName(), $data->employee->employee_id_number), linkTo(['employee/academic-degree', 'id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'diploma_type',
                'format' => 'raw',
                'value' => function (EEmployeeAcademicDegree $data) {
                    return sprintf("%s<p class='text-muted'> %s / %s</p>", $data->getTypeLabel(), $data->educationYear->name, $data->diplomaTypeIsRank() ? $data->academicRank->name : $data->academicDegree->name);
                },
            ],
            [
                'attribute' => '_country',
                'format' => 'raw',
                'value' => function (EEmployeeAcademicDegree $data) {
                    return sprintf("%s<p class='text-muted'> %s </p>", $data->country ? $data->country->name : '', $data->university);
                },
            ],
            [
                'attribute' => 'diploma_number',
                'format' => 'raw',
                'value' => function (EEmployeeAcademicDegree $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->diploma_number, Yii::$app->formatter->asDate($data->diploma_date->getTimestamp()));
                },
            ],
            [
                'attribute' => 'council_number',
                'format' => 'raw',
                'value' => function (EEmployeeAcademicDegree $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->council_number, Yii::$app->formatter->asDate($data->council_date->getTimestamp()));
                },
            ]
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
