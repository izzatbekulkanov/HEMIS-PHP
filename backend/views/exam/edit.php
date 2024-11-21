<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\DateTimePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\components\Config;
use common\models\curriculum\EExam;
use common\models\curriculum\EExamGroup;
use common\models\curriculum\MarkingSystem;
use common\models\system\Admin;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use yii2mod\editable\EditableColumn;
use common\models\system\classifier\ExamType;
use common\models\curriculum\ECurriculumSubjectExamType;
use kartik\depdrop\DepDrop;

/* @var $this \backend\components\View */
/* @var $model \common\models\curriculum\EExam */

$this->title = $model->isNewRecord ? __('Create Exam') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['exam/index'], 'label' => __('Exam Index')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>


<div class="row">
    <div class="col col-md-12">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'action' => linkTo(['exam/edit', 'id' => $model->id])]); ?>
        <div class="box box-default ">
            <?php if (!$model->isNewRecord): ?>
                <div class="box-header bg-gray">
                    <div class="row">
                        <div class="col col-md-12">
                            <?= Html::a(__('Exam Questions ({count})', ['count' => $model->getTestQuestions()->count()]), ['exam/edit', 'id' => $model->id, 'questions' => 1], ['class' => 'btn btn-primary btn-flat']) ?>

                            <button class="btn btn-default btn-flat showModalButton"
                                    modal-class="modal-lg"
                                    type="button"
                                <?= $model->canEditExam() ? '' : 'disabled' ?>
                                    value="<?= linkTo(['exam/edit', 'id' => $model->id, 'groups' => 1]) ?>"
                                    title="<?= __('Select Group') ?>">
                                <i class="fa fa-plus"></i> <?= __('Add Group') ?>
                            </button>

                            <?= Html::a(__('Exam Results ({count})', ['count' => $model->getExamStudentResults()->count()]), ['exam/edit', 'id' => $model->id, 'results' => 1], ['class' => 'btn btn-default btn-flat']) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'disabled' => !$model->canEditExam()]) ?>
                        <?= $form->field($model, 'comment')->textArea(['maxlength' => true, 'style' => 'height:108px', 'disabled' => !$model->canEditExam()]) ?>
                        <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                            'data' => \common\models\curriculum\EducationYear::getEducationYears(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'disabled' => !$model->canEditExam(),
                            'options' => [
                                'id' => '_education_year',
                            ],
                        ]) ?>

                        <?php
                        $curriculums = [];
                        if ($model->_curriculum) {
                            $curriculums = $model->getCurriculumOptions($this->_user(), $model->_education_year);
                        }
                        ?>
                        <?= $form->field($model, '_curriculum')->widget(DepDrop::classname(), [
                            'data' => $curriculums,
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => [
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                                'theme' => Select2::THEME_DEFAULT
                            ],
                            'options' => [
                                'id' => '_curriculum',
                                'placeholder' => __('-Choose Curriculum-'),
                                'required' => true,
                                'disabled' => !$model->canEditExam(),
                            ],
                            'pluginOptions' => [
                                'depends' => ['_education_year'],
                                'url' => Url::to(['/ajax/get-exam-curriculums']),
                                'required' => true
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-6">
                        <div class="row">

                            <div class="col-md-6">
                                <?= $form->field($model, '_exam_type')->widget(Select2Default::classname(), [
                                    'data' => EExam::getExamTypeOptions(),
                                    'allowClear' => false,
                                    'disabled' => !$model->canEditExam(),
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'active')->widget(Select2Default::classname(), [
                                    'data' => ['1' => __('Yes'), '0' => __('No')],
                                    'allowClear' => false,
                                    'placeholder' => false,
                                    'options' => [
                                        'value' => $model->isNewRecord ? 0 : $model->active ? 1 : 0,
                                    ],
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'start_at')->widget(DateTimePickerDefault::classname(), [
                                    'options' => [
                                        'placeholder' => __('YYYY-MM-DD H:i'),
                                    ],
                                    'disabled' => !$model->canEditExam(),
                                ]); ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'finish_at')->widget(DateTimePickerDefault::classname(), [
                                    'options' => [
                                        'placeholder' => __('YYYY-MM-DD H:i'),
                                    ],
                                    'disabled' => !$model->canEditExam(),
                                ]); ?>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'duration')->input('number', ['id' => 'test_duration', 'disabled' => !$model->canEditExam()]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'max_ball')->input('number', [
                                    'maxlength' => true,
                                    'disabled' => !$model->canEditExam()
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, 'attempts')->input('number', ['maxlength' => true, 'disabled' => !$model->canEditExam()]) ?>

                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'question_count')->input('number', ['id' => 'question_count', 'max' => 200, 'disabled' => !$model->canEditExam()]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'random')->widget(Select2Default::classname(), [
                                    'data' => ['1' => __('Yes'), '0' => __('No')],
                                    'allowClear' => false,
                                    'placeholder' => false,
                                    'disabled' => !$model->canEditExam(),
                                    'options' => [
                                        'value' => $model->random || $model->isNewRecord ? 1 : 0,

                                    ],
                                ]) ?>
                            </div>
                        </div>
                        <div >
                            <?php
                            $subjects = [];
                            if ($model->_curriculum) {
                                $subjects = $model->getSubjectOptions($this->_user(), $model->_curriculum);
                            }
                            ?>
                            <?= $form->field($model, '_subject')->widget(DepDrop::classname(), [
                                'data' => $subjects,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => [
                                        'allowClear' => false,
                                    ],
                                    'theme' => Select2::THEME_DEFAULT
                                ],
                                'options' => [
                                    'id' => '_subject',
                                    'placeholder' => __('-Choose Subject-'),
                                    'required' => true,
                                    'disabled' => !$model->canEditExam(),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_curriculum'],
                                    'url' => Url::to(['/ajax/get-curriculum-subjects', 'employee' => $model->_employee]),
                                    'required' => true
                                ],
                            ]); ?>
                        </div>

                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?php if ($model->canEditExam()): ?>
                        <?= $this->getResourceLink(__('Delete'), ['exam/delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-flat btn-delete']) ?>
                    <?php endif; ?>
                    <?= $this->getResourceLink(__('Reset Sessions'), ['exam/edit', 'id' => $model->id, 'reset' => 1], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Update'), ['class' => 'btn btn-primary btn-flat']) ?>
                <?php else: ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <?php if (!$model->isNewRecord): ?>
        <div class="col col-md-12">
            <div class="box box-default ">
                <div class="box-header bg-gray">
                    <div class="row">
                        <div class="col col-md-12">
                            <h3 class="box-title"><?= __('Exam Groups') ?></h3>
                        </div>
                    </div>
                </div>
                <?php Pjax::begin(['id' => 'group-list', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $model->searchGroups(),
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'header' => __('Group'),
                            'format' => 'raw',
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                            'value' => function (EExamGroup $data) use ($model) {
                                return Html::a(sprintf('%s',
                                    $data->group->name
                                ), '#',
                                    [
                                        'class' => 'showModalButton',
                                        'modal-class' => 'modal-lg',
                                        'title' => $data->group->name,
                                        'value' => linkTo(['exam/edit', 'id' => $model->id, 'students' => $data->_group])
                                    ]);
                            }
                        ],
                        [
                            'attribute' => '_department',
                            'header' => __('Faculty'),
                            'format' => 'raw',
                            'value' => function (\common\models\curriculum\EExamGroup $data) {
                                return $data->group->department->name;
                            }
                        ],
                        [
                            'attribute' => '_language',
                            'header' => __('Language'),
                            'format' => 'raw',
                            'value' => function (\common\models\curriculum\EExamGroup $data) {
                                return $data->group->educationLang->name;
                            }
                        ],
                        [
                            'attribute' => 'start_at',
                            'header' => __('Start At'),
                            'format' => 'raw',
                            'value' => function (\common\models\curriculum\EExamGroup $data) use ($model) {
                                $label = $data->start_at ? Yii::$app->formatter->asDatetime($data->start_at->getTimestamp(), 'php: d.m.Y H:i') : __('Exam Start At');

                                return Html::a($label, '#', $model->canEditExam() ? [
                                    'modal-class' => 'modal-sm',
                                    'title' => __('Change Start At'),
                                    'class' => 'showModalButton',
                                    'value' => linkTo(['exam/edit', 'id' => $data->_exam, 'group_start_at' => $data->_group]),
                                ] : []);
                            },
                        ],
                        [
                            'attribute' => 'finish_at',
                            'header' => __('Finish At'),
                            'format' => 'raw',
                            'value' => function (\common\models\curriculum\EExamGroup $data) use ($model) {
                                $label = $data->finish_at ? Yii::$app->formatter->asDatetime($data->finish_at->getTimestamp(), 'php: d.m.Y H:i') : __('Exam Finish At');

                                return Html::a($label, '#', $model->canEditExam() ? [
                                    'modal-class' => 'modal-sm',
                                    'title' => __('Change Finish At'),
                                    'class' => 'showModalButton',
                                    'value' => linkTo(['exam/edit', 'id' => $data->_exam, 'group_finish_at' => $data->_group]),
                                ] : []);
                            },
                        ],
                        [
                            'format' => 'raw',
                            'visible' => $model->canEditExam(),
                            'value' => function (\common\models\curriculum\EExamGroup $data) use ($model) {
                                return Html::a(__('Remove'), linkTo(['exam/edit', 'id' => $model->id, 'remove' => 1, 'items' => $data->_group]), ['class' => 'btn-delete']);
                            }
                        ],
                    ],
                ]); ?>
                <?php Pjax::end() ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
$this->registerJs('

')
?>

