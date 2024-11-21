<?php

use backend\components\View;
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
 * @var $topic_model \common\models\curriculum\ECurriculumSubjectTopic
 * @var $model \common\models\curriculum\ESubjectTask
 * @var $subject \common\models\curriculum\ECurriculumSubject
 * @var $this View
 */
$this->title = $model->name;
$label = "{$model->subject->name} ({$model->trainingType->name} | {$subject->semester->name} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-task-list',
    'curriculum' => $model->_curriculum,
    'semester' => $model->_semester,
    'subject' => $model->_subject,
    'training_type' => $model->_training_type,
    'education_lang' => $model->_language
], 'label' => $label];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-primary ">
            <div class="box-header bg-gray with-border">
                <div class="row">
                    <div class="col-md-4">
                        <?= Html::a(
                            '<i class="fa fa-plus"></i> &nbsp;' . __('Import Questions'),
                            Url::current(['import' => 1]),
                            ['class' => 'btn btn-success btn-flat']
                        ) ?>
                        <?= Html::a(
                            '<i class="fa fa-upload"></i> &nbsp;' . __('Export Questions'),
                            Url::current(['export' => 1]),
                            ['class' => 'btn btn-info btn-flat']
                        ) ?>
                    </div>
                    <div class="col-md-8">
                    </div>
                </div>
            </div>
            <?= GridView::widget(
                [
                    'dataProvider' => new ActiveDataProvider(
                        [
                            'query' => $model->getTestQuestions(),
                            'sort' => [
                                'attributes' => ['created_at']
                            ],
                            'pagination' => [
                                'pageSize' => 100,
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
                            'value' => function ($data) {
                                return Html::a($data->getTitle(), ['teacher/subject-topic-test-edit', 'id' => $data->id], ['data-pjax' => 0]);
                            },
                        ],
                    ],
                ]
            ); ?>
        </div>
    </div>
</div>



