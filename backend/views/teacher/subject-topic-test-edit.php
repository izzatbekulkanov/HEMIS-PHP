<?php

use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\Question;
use common\models\Subject;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use common\models\Topic;
use dosamigos\tinymce\TinyMce;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\Pjax;


/**
 * @var $model \common\models\curriculum\ESubjectResourceQuestion
 */
$this->title = $model->isNewRecord ? __('Create Question') : $model->getShortTitle();

$user = $this->context->_user();
if ($model->subjectResource) {
    $training = $model->subjectTopic->trainingType->name;
    $this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => __('Subject Resources')];
    $this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => "{$subject->subject->name} ($training | {$subject->semester->name} | {$group_labels})"];
    $this->params['breadcrumbs'][] = ['url' => ['teacher/subject-topic-test', 'education_lang' => $model->_language, 'code' => $model->_subject_topic], 'label' => $model->subjectResource->comment];
    $this->params['breadcrumbs'][] = $this->title;
} elseif ($model->subjectTask) {
    $task = $model->subjectTask;

    $label = "{$task->subject->name} ({$task->trainingType->name})";
    $url = $url1 = ['teacher/subject-task-list',
        'curriculum' => $task->_curriculum,
        'semester' => $task->_semester,
        'subject' => $task->_subject,
        'training_type' => $task->_training_type,
        'education_lang' => $task->_language
    ];
    $url1['questions'] = 1;
    $url1['code'] = $task->id;

    $this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
    $this->params['breadcrumbs'][] = ['url' => $url, 'label' => $label];
    $this->params['breadcrumbs'][] = ['url' => $url1, 'label' => $task->name];
    $this->params['breadcrumbs'][] = $this->title;
}


?>
<?php Pjax::begin(
    ['id' => 'question-form', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
);
?>
    <div class="user-create">
        <div class="user-form">
            <?php $form = ActiveForm::begin(['options' => ['class' => 'question-form', 'data-pjax' => false]]); ?>
            <? //php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'options' => ['data-pjax' => 0]]); ?>
            <? //php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 0]]); ?>
            <div class="row">
                <div class="col col-md-7">
                    <div class="box box-primary ">
                        <div class="box-header bg-gray with-border">
                            <h3 class="box-title"><?= __('Content') ?></h3>
                        </div>
                        <div class="box-body">
                            <?= $form->field($model, 'content')->widget(TinyMce::className(), [
                                'options' => [
                                    'style' => 'height:630px',
                                    'value' => $model->content_r
                                ],
                                'clientOptions' => [
                                    'plugins' => [
                                        "autolink link imagetools image charmap print hr anchor pagebreak",
                                        "searchreplace wordcount visualblocks visualchars code fullscreen",
                                        "nonbreaking contextmenu codesample",
                                        "paste",
                                    ],
                                    'init_instance_callback' => new \yii\web\JsExpression("
                                        function(editor){
                                            editor.on('Change', function (e) {
                                                editorChanged(1000);
                                            });
                                        }
                                    "),
                                    'formats' => [
                                        'underline' => [
                                            'exact' => true,
                                            'inline' => 'u',
                                        ]
                                    ],
                                    'inline_styles' => false,
                                    'entity_encoding' => 'raw',
                                    'menubar' => false,
                                    'paste_convert_word_fake_lists' => false,
                                    'image_title' => false,
                                    'relative_urls' => false,
                                    'remove_script_host' => false,
                                    'convert_urls' => false,
                                    'image_class_list' => 'img-responsive',
                                    'image_dimensions' => false,
                                    'automatic_uploads' => true,
                                    'visualblocks_default_state' => false,
                                    'image_caption' => false,
                                    'image_advtab' => false,
                                    'content_style' => 'body {max-width: 768px; margin: 0 auto;padding:10px;}',
                                    'images_upload_url' => linkTo(['dashboard/file-upload', 'type' => 'content-image', 'fileparam' => 'file']),
                                    'toolbar1' => "undo redo | bold italic underline  superscript subscript | link image charmap codesample | code visualblocks fullscreen",
                                ],
                                'language' => 'ru',
                            ])->label(false) ?>
                        </div>
                    </div>
                </div>
                <div class="col col-md-5 " id="sidebar">
                    <div class="box box-default ">
                        <div class="box-body test-variants">
                            <p class="bold">
                                <?= $model->name ?>
                            </p>
                            <?php foreach ($model->answers as $v => $answer): ?>
                                <div class="p <?= in_array($v, $model->_answer) ? 'bg-correct' : '' ?>">
                                    <?= $v ?>) <?= $answer ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="panel-footer">
                            <div class="text-right">
                                <?php if (!$model->isNewRecord): ?>
                                    <?= Html::a(
                                        __('Delete'),
                                        ['teacher/subject-topic-test-edit', 'id' => $model->id, 'delete' => 1],
                                        [
                                            'class' => 'btn btn-danger btn-flat btn-delete',
                                            'data-pjax' => 0,
                                        ]
                                    ) ?>
                                <?php endif; ?>
                                <?= Html::submitInput(__('Save'), ['class' => 'btn btn-primary btn-flat', 'data-pjax' => 0, 'name' => 'save']) ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <script type="text/javascript">
        var timeout;

        function editorChanged(time) {
            if (timeout != undefined) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(function () {
                tinymce.triggerSave();
            }, time);
        }
    </script>

<?php Pjax::end() ?>