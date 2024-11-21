<?php

use backend\widgets\GridView;
use common\models\system\classifier\Language;
use backend\widgets\Select2Default;
use common\models\system\classifier\TrainingType;
use common\models\curriculum\Semester;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/**
 * @var $topic_model \common\models\curriculum\ECurriculumSubjectTopic
 * @var $model \common\models\curriculum\ESubjectResource
 */
$training = TrainingType::findOne($topic_model->_training_type)->name;
$this->title = $model->comment;
$semester = Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester)->name;
$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => __('Subject Resources')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => "{$subject->subject->name} ($training | {$semester} | {$group_labels})"];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col col-md-9 col-lg-9">

        <?php if (!$model->isNewRecord): ?>
            <div class="box box-primary ">
                <div class="box-header bg-gray with-border">
                    <div class="row">
                        <div class="col-md-4 text-right">
                            <?= Html::a(
                                '<i class="fa fa-plus"></i> &nbsp;' . __('Import Questions'),
                                [
                                    'teacher/test-import',
                                    'education_lang' => (string)$education_lang,
                                    'code' => $topic_model->id
                                ],
                                ['class' => 'btn btn-success btn-flat']
                            ) ?>
                            <?= Html::a(
                                '<i class="fa fa-upload"></i> &nbsp;' . __('Export Questions'),
                                [
                                    'teacher/test-export',
                                    'education_lang' => (string)$education_lang,
                                    'code' => $topic_model->id
                                ],
                                ['class' => 'btn btn-info btn-flat']
                            ) ?>
                        </div>
                        <div class="col-md-8">
                        </div>
                    </div>
                </div>
                <?= GridView::widget(
                    [
                        'dataProvider' => $topic_model->getQuestionsDataProvider(),
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
        <?php else: ?>
            <div class="box box-default ">
                <?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'options' => ['data-pjax' => 0]]); ?>

                <div class="box-body text-center padding-50">
                    <p>
                        <?= __('Test qo\'shish uchun quyidagi knopkani bosing:') ?>
                    </p>
                    <?= Html::submitButton('<i class="fa fa-plus"></i> ' . __('Create Test'), ['class' => 'btn btn-primary btn-flat']) ?>

                    <div class="row hidden">
                        <div class="col-md-3">
                            <?= $form->field($model, 'test_duration')->input('number', ['max' => 120]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'test_questions')->input('number', []); ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($model, 'test_attempt_count')->input('number', []); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'test_random')->widget(Select2Default::classname(), [
                                'data' => [1 => __('Yes'), 0 => __('No')],
                                'allowClear' => false,
                                'placeholder' => false,
                                'options' => [
                                    'value' => $model->test_random ? 1 : 0,
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col col-md-3 col-lg-3" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $topic_model,
                    'attributes' => [
                        [
                            'attribute' => '_curriculum',
                            'value' => function ($data) {
                                return $data->curriculum ? $data->curriculum->name : '';
                            }
                        ],
                        [
                            'attribute' => 'id',
                            'label' => __('Group'),
                            'value' => function ($data) use ($group_labels) {
                                return $group_labels;
                            }
                        ],
                        [
                            'attribute' => '_semester',
                            'value' => function ($data) {
                                return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                            },
                        ],
                        [
                            'attribute' => '_subject',
                            'value' => function ($data) {
                                return $data->subject ? $data->subject->name : '';
                            }
                        ],
                        [
                            'attribute' => 'id',
                            'label' => __('Name of Topic'),
                            'value' => function ($data) {
                                return $data->name;
                            }
                        ],

                        [
                            'attribute' => 'id',
                            'label' => __('Education Lang'),
                            'value' => function ($data) use ($education_lang) {
                                return Language::findOne($education_lang)->name;
                            }
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>



