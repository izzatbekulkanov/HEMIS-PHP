<?php

use common\models\student\EStudentOlympiad;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel EStudentOlympiad */
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Student Olympiad'),
                        ['student/olympiad', 'edit' => 1],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, 'olympiad_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\student\EStudentOlympiad::getTypeOptions(),
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
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Olympiad Name')])->label(false) ?>
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
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function (EStudentOlympiad $data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->student->getFullName(), $data->educationYear->name), linkTo(['student/olympiad', 'id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'olympiad_name',
                'format' => 'raw',
                'value' => function (EStudentOlympiad $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->olympiad_name, $data->getTypeLabel());
                },
            ],
            [
                'attribute' => '_country',
                'format' => 'raw',
                'value' => function (EStudentOlympiad $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->country->name, $data->olympiad_place);
                },
            ],
            [
                'attribute' => 'diploma_number',
                'format' => 'raw',
                'value' => function (EStudentOlympiad $data) {
                    return sprintf("%s / %s <p class='text-muted'> %s</p>", $data->student_place, $data->diploma_serial . $data->diploma_number, Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp()));
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
