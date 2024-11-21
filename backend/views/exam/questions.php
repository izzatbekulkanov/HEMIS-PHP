<?php

use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\system\classifier\Language;
use backend\widgets\Select2Default;
use common\models\system\classifier\TrainingType;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/**
 * @var $model \common\models\curriculum\EExam
 */
$this->title = __('Exam Questions');

$this->params['breadcrumbs'][] = ['url' => ['exam/index'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => ['exam/edit', 'id' => $model->id,], 'label' => $model->name];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-primary ">
            <div class="box-header bg-gray with-border">
                <div class="row">
                    <div class="col-md-12">
                        <?= Html::a(
                            '<i class="fa fa-chevron-left"></i> &nbsp;' . __('Exam Data'),
                            currentTo(['questions' => null]),
                            ['class' => 'btn btn-default btn-flat']
                        ) ?>
                        <?= Html::a(
                            '<i class="fa fa-plus"></i> &nbsp;' . __('Import Questions'),
                            $model->canEditExam() ? currentTo(['import' => 1]) : '#',
                            ['class' => 'btn btn-success btn-flat', 'disabled' => !$model->canEditExam()]
                        ) ?>
                        <?= Html::a(
                            '<i class="fa fa-upload"></i> &nbsp;' . __('Export Questions'),
                            currentTo(['export' => 1]),
                            ['class' => 'btn btn-info btn-flat']
                        ) ?>
                    </div>
                </div>
            </div>
            <?= GridView::widget(
                [
                    'dataProvider' => new ActiveDataProvider(
                        [
                            'query' => $model->getTestQuestions(),
                            'sort' => [
                                'defaultOrder' => ['position' => SORT_ASC],
                                'attributes' => [
                                    'id',
                                    'position',
                                ]
                            ],
                            'pagination' => [
                                'pageSize' => 400,
                            ],
                        ]
                    ),
                    'id' => 'data-grid',
                    'layout' => "<div class='box-body no-padding'>{items}</div><div class='box-footer'>{pager}</div>",
                    'sortable' => true,
                    'toggleAttribute' => 'active',
                    'tableOptions' => ['class' => 'table table-striped table-hover '],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function (\common\models\curriculum\EExamQuestion $data) use ($model) {
                                return Html::a($data->getTitle() ?: $data->name, ['exam/edit', 'id' => $model->id, 'q' => $data->id], ['data-pjax' => 0]);
                            },
                        ]
                    ],
                ]
            ); ?>
        </div>
    </div>
</div>



