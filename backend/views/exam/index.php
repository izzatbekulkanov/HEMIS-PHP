<?php

use backend\widgets\GridView;
use common\models\curriculum\EExam;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Exam'),
                        ['exam/edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => \common\models\curriculum\EducationYear::getEducationYears(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return sprintf('%s<br><span class="text-muted">%s / %s / %s / %s</span>',
                        Html::a($data->name, ['exam/edit', 'id' => $data->id], ['data-pjax' => 0]),
                        $data->educationYear ? $data->educationYear->name : '',
                        $data->subject ? $data->subject->name : '',
                        $data->examType ? $data->examType->name : '',
                        $data->employee ? $data->employee->getShortName() : ''
                    );
                },
            ],
            [
                'attribute' => '_groups',
                'header' => __('Groups'),
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return $data->getGroupsLabel();
                },
            ],
            [
                'attribute' => '_questions',
                'header' => __('Questions'),
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return Html::a(sprintf('%s / %s', $data->getTestQuestions()->count(), $data->question_count), ['exam/edit', 'id' => $data->id, 'questions' => 1], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'start_at',
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return $data->start_at ? Yii::$app->formatter->asDatetime($data->start_at->getTimestamp(), 'php: d.m.Y H:i') : '';
                },
            ],
            [
                'attribute' => 'finish_at',
                'format' => 'raw',
                'value' => function (EExam $data) {
                    return $data->finish_at ? Yii::$app->formatter->asDatetime($data->finish_at->getTimestamp(), 'php: d.m.Y H:i') : '';
                },
            ],
            [
                'attribute' => 'duration',
                'format' => 'raw',
                'value' => function ($data) {
                    return __('{min} daqiqa', ['min' => $data->duration]);
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
