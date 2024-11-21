<?php

use backend\widgets\DatePickerDefault;
use common\models\attendance\ELessonsStat;
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
/* @var $searchModel \common\models\attendance\ELessonsStat */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col-md-12">
                <div class="row">
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => true,
                            'disabled' => $faculty != null,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, 'start_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, 'end_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                            ],
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-3">
                        <?= $form->field($searchModel, 'group_by')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupByOptions(),
                            'allowClear' => true,
                        ])->label(false); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Employee / Group / Subject / Lesson Pair')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php if ($searchModel->group_by == null): ?>
        <?= GridView::widget([
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => '_employee',
                    'format' => 'raw',
                    'value' => function (ELessonsStat $data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->employee->getFullName(), $data->employee->department ? $data->employee->department->getShortTitle(4) : '');
                    },
                ],
                [
                    'attribute' => '_group',
                    'format' => 'raw',
                    'value' => function (ELessonsStat $data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->group->getShortTitle(4), $data->group->department->getShortTitle(4));
                    },
                ],

                [
                    'attribute' => '_subject',
                    'format' => 'raw',
                    'value' => function (ELessonsStat $data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->subject->name, $data->trainingType->name);
                    },
                ],

                [
                    'attribute' => '_lesson_pair',
                    'format' => 'raw',
                    'value' => function (ELessonsStat $data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->lessonPair->getFullName(), Yii::$app->formatter->asDate($data->lesson_date->getTimestamp()));

                    },
                ]
            ],
        ]); ?>
    <?php else: ?>
        <?php
        if ($searchModel->group_by == 'teacher') {
            $col = [
                'attribute' => '_employee',
                'format' => 'raw',
                'footer' => __('Total'),
                'value' => function (ELessonsStat $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->employee->getFullName(), $data->employee->department ? $data->employee->department->name : '');
                },
            ];
        } else if ($searchModel->group_by == 'group') {
            $col = [
                'attribute' => '_group',
                'format' => 'raw',
                'footer' => __('Total'),
                'value' => function (ELessonsStat $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->group->name, $data->group->department->name);
                },
            ];
        } else if ($searchModel->group_by == 'department') {
            $col = [
                'attribute' => '_department',
                'format' => 'raw',
                'footer' => __('Total'),
                'value' => function (ELessonsStat $data) {
                    return $data->department->name;
                },
            ];
        } else if ($searchModel->group_by == 'lesson_date') {
            $col = [
                'attribute' => 'lesson_date',
                'format' => 'raw',
                'footer' => __('Total'),
                'value' => function (ELessonsStat $data) {
                    return Yii::$app->formatter->asDate($data->lesson_date->getTimestamp());
                },
            ];
        } else if ($searchModel->group_by == '_lesson_pair') {
            $col = [
                'attribute' => '_lesson_pair',
                'format' => 'raw',
                'footer' => __('Total'),
                'value' => function (ELessonsStat $data) {
                    return $data->lessonPair->getFullName();
                },
            ];
        }
        ?>
        <?= GridView::widget([
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'showFooter' => true,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                $col,
                [
                    'attribute' => 'count',
                    'options' => [
                        'style' => 'width:70%'
                    ],
                    'format' => 'raw',
                    'value' => function (ELessonsStat $data) {
                        return $data->count;
                    },
                    'footer' => array_sum(\yii\helpers\ArrayHelper::getColumn($dataProvider->getModels(), 'count'))
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
<?php Pjax::end() ?>
