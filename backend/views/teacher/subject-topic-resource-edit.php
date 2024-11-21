<?php

use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\system\classifier\SubjectGroup;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use backend\widgets\Select2Default;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use trntv\filekit\widget\Upload;
use common\models\curriculum\Semester;

/**
 * @var $model ECurriculumSubjectTopic
 */
$semester = Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester)->name;
$training = TrainingType::findOne($topic_model->_training_type)->name;
$this->title = "{$subject->subject->name} ($training | {$semester} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => __('Subject Resources')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => $this->title];
$this->params['breadcrumbs'][] = $model->isNewRecord ? __('Yangi resurs qo\'shish') : $model->name;

?>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-primary ">
            <div class="box-header bg-gray with-border">
                <h3 class="box-title"><?= __('Resource information') ?></h3>
            </div>
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'options' => ['data-pjax' => 0]]); ?>

            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'comment')->textArea(['maxlength' => true]) ?>
                        <?= $form->field($model, 'path')->textInput(['maxlength' => true])->label(__('Link')) ?>
                        <?= $form->field($model, '_language')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\Language::getClassifierOptions(),
                            'allowClear' => false,
                            'disabled' => true,
                        ]) ?>
                        <?= $form->field($model, 'filename')->widget(
                            Upload::class,
                            [
                                'url' => ['dashboard/file-upload', 'type' => 'attachment'],
                                'acceptFileTypes' => new JsExpression(
                                    '/(\.|\/)(xlsx?|docx?|pdf|pptx?|jpe?g|png)$/i'
                                ),
                                'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                                'multiple' => true,
                                'sortable' => true,
                                'maxNumberOfFiles' => 4,
                                'clientOptions' => [],
                                'options' => ['class' => 'file'],
                            ]
                        ) ?>
                    </div>
                </div>
            </div>


            <div class="box-footer text-right">
                <?php if (is_array($model->filename)): ?>
                    <?= Html::a('<i class="fa fa-download"></i> '.__('Download'), currentTo(['download' => 1]), ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['teacher/subject-topic-resource-edit', 'education_lang' => $model->_language, 'code' => $model->_subject_topic, 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="col col-md-4 col-lg-4" id="sidebar">
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



