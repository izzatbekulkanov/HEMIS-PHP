<?php

use dosamigos\tinymce\TinyMce;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


/**
 * @var $model \common\models\curriculum\EExam
 * @var $question \common\models\curriculum\EExamQuestion
 */
$this->title = $model->isNewRecord ? __('Create Question') : $question->getShortTitle();

$this->params['breadcrumbs'][] = ['url' => ['exam/index'], 'label' => __('Exam Index')];
$this->params['breadcrumbs'][] = ['url' => ['exam/edit', 'id' => $model->id,], 'label' => $model->name];
$this->params['breadcrumbs'][] = ['url' => ['exam/edit', 'id' => $model->id, 'questions' => 1], 'label' => __('Exam Questions')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(
    ['id' => 'question-form', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
);
?>
    <div class="user-create">
        <div class="user-form">
            <?php $form = ActiveForm::begin(['options' => ['class' => 'question-form', 'data-pjax' => false]]); ?>
            <div class="row">
                <div class="col col-md-7">
                    <div class="box box-primary ">
                        <div class="box-header bg-gray with-border">
                            <h3 class="box-title"><?= __('Content') ?></h3>
                        </div>
                        <div class="box-body">
                            <?= $form->field($question, 'content')->widget(TinyMce::className(), [
                                'options' => [
                                    'style' => 'height:630px',
                                    'value' => $question->content_r
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
                                <?= $question->name ?>
                            </p>
                            <?php foreach ($question->answers as $v => $answer): ?>
                                <div class="p <?= in_array($v, $question->_answer) ? 'bg-correct' : '' ?>">
                                    <?= $v ?>) <?= $answer ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="panel-footer">
                            <div class="text-right">
                                <?php if (!$question->isNewRecord): ?>
                                    <?= Html::a(
                                        __('Delete'),
                                        currentTo(['delete' => 1]),
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