<?php

use common\models\student\EStudentSport;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel EStudentSport */
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Student Sport'),
                        ['student/sport', 'edit' => 1],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_sport_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\SportType::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-7">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Document')])->label(false) ?>
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
                'value' => function (EStudentSport $data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->student->getFullName(), $data->student->student_id_number), linkTo(['student/sport', 'id' => $data->id]), ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_education_year',
                'format' => 'raw',
                'value' => function (EStudentSport $data) {
                    return $data->educationYear->name;
                },
            ],
            [
                'attribute' => '_sport_type',
                'format' => 'raw',
                'value' => function (EStudentSport $data) {
                    return $data->sportType->name;
                },
            ],
            [
                'attribute' => 'sport_date',
                'format' => 'raw',
                'value' => function (EStudentSport $data) {
                    return $data->sport_date ? Yii::$app->formatter->asDate($data->sport_date->getTimestamp()) : '';
                },
            ],

            [
                'attribute' => 'sport_rank',
                'format' => 'raw',
                'value' => function (EStudentSport $data) {
                    return $data->sport_rank;
                },
            ],
            [
                'attribute' => 'sport_rank_document',
                'format' => 'raw',
                'value' => function (EStudentSport $data) {
                    return $data->sport_rank_document;
                },
            ],
        ]
    ]); ?>
</div>
<?php Pjax::end() ?>
