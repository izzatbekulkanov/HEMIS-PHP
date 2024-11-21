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

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-4">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Decree'),
                        ['decree/edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                    <?= $this->getResourceLink(
                        '<i class="fa fa-check"></i> ' . __('Apply Decree'),
                        ['decree/apply'],
                        ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-8">
                <div class="row">
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_decree_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\DecreeType::getClassifierOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getFaculties(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Number')])->label(false) ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'toggleLink' => currentTo(['status' => 1]),
        'toggleAttribute' => 'status',
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
                    return Html::a($data->name, ['decree/edit', 'id' => $data->id], ['data-pjax' => 0]);
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
                'attribute' => '_department',
                'format' => 'raw',
                'value' => function (EDecree $data) {
                    return $data->department->name;
                },
            ],

            [
                'header' => __('Students'),
                'format' => 'raw',
                'value' => function (EDecree $data) {
                    if ($c = $data->getDecreeStudents()->count()) {
                        return Html::a(__('{count} talaba', ['count' => $c]), '#', [
                            'class' => 'showModalButton',
                            'modal-class' => 'modal-lg',
                            'title' => $data->name,
                            'value' => currentTo(['students' => $data->id])
                        ]);
                    }
                    return '-';
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
                    return Html::a("<i class='fa fa-download'></i> " . __('Download'), ['decree/file', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
