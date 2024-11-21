<?php

use backend\widgets\Select2Default;
use backend\widgets\SelectizeDefault;
use common\models\system\Admin;
use common\models\system\AdminMessageItem;
use common\models\system\Contact;
use dosamigos\selectize\SelectizeTextInput;
use dosamigos\tinymce\TinyMce;
use dosamigos\tinymce\TinyMceAsset;
use kartik\select2\Select2Asset;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\system\AdminMessage
 */
$this->params['breadcrumbs'][] = ['url' => ['message/my-messages'], 'label' => __('My Messages')];
$this->params['breadcrumbs'][] = $this->title;
TinyMceAsset::register($this);
Select2Asset::register($this);
$this->registerJs('initForm();');
$folder = Yii::$app->request->get('folder', 'inbox');

$options = [];
if (is_string($model->_recipients)) {
    $options = Contact::getSelected(explode(',', $model->_recipients));
}
if (is_array($model->_recipients)) {
    $options = Contact::getSelected($model->_recipients);
    $model->_recipients = implode(',', $model->_recipients);
}

?>
<?php $form = ActiveForm::begin(['id' => 'message_form', 'enableClientValidation' => false, 'enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>

<div class="box-header with-border">
    <h3 class="box-title"><?= __('Compose New Message') ?></h3>
</div>
<!-- /.box-header -->
<div class="box-body">
    <div class="row">
        <div class="col col-md-9">
            <?= $form->field($model, '_recipients', ['options' => ['class' => '']])->widget(SelectizeDefault::className(), [
                'loadUrl' => Url::current(['contacts' => 'json']),
                'clientOptions' => [
                    'maxItems' => 100,
                    'maxOptions' => 10,
                    'hideSelected' => true,
                    'preload' => true,
                    'valueField' => 'id',
                    'labelField' => 'name',
                    'searchField' => ['name', 'label'],
                    'placeholder' => __('To:'),
                    /*'optgroupLabelField' => 'name',
                    'optgroupValueField' => 'id',
                    'optgroupField' => 'group',
                    'options' => $options['options'],
                    'optgroups' => $options['optgroups'],*/
                    'options' => $options,
                    'plugins' => ['remove_button'],
                    'render' => [
                        'option' => new JsExpression("
                            function(item, escape) {
                                var label = item.name;
                                var caption = item.label ? item.label : null;
                                return '<div>' +
                                    '<span class=\"item-option\">' + escape(label) + '</span>' +
                                    (caption ? '<span class=\"item-label\">' + escape(caption) + '</span>' : '') +
                                '</div>';
                            }
                        "),
                    ]
                ],
            ])->label(false) ?>
        </div>
        <div class="col col-md-3">
            <div class="btn-group btn-group-justified" role="group">
                <div class="btn-group" role="group">
                    <button class="btn btn-default btn-flat showModalButton"
                            modal-class="modal-lg"
                            type="button"
                            value="<?= Url::current(['contacts' => 'html', 'type' => Contact::TYPE_ADMIN]) ?>"
                            title="<?= __('Select Employee') ?>">
                        <i class="fa fa-plus"></i> <?= __('Employee') ?>
                    </button>
                </div>
                <div class="btn-group" role="group">
                    <button class="btn btn-default btn-flat showModalButton"
                            modal-class="modal-lg"
                            type="button"
                            value="<?= Url::current(['contacts' => 'html', 'type' => Contact::TYPE_STUDENT]) ?>"
                            title="<?= __('Select Student') ?>">
                        <i class="fa fa-plus"></i> <?= __('Student') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <?php // $form->field($model, 'recipient_data', ['template' => '{input}'])->textInput(['placeholder' => __('To:')])->label(false) ?>
    <?= $form->field($model, 'title', ['template' => '{input}'])->textInput(['placeholder' => __('Subject:'), 'maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'message')->widget(TinyMce::className(), [
        'clientOptions' => [
            'plugins' => [
                "advlist autosave autolink lists link imagetools image anchor pagebreak",
                "searchreplace wordcount visualblocks fullscreen",
                "media save table",
                "paste textcolor ",
            ],
            'image_title' => false,
            'image_class_list' => 'img-responsive',
            'image_dimensions' => false,
            'automatic_uploads' => true,
            'placeholder' => __('Write message here ...'),
            'image_caption' => false,
            'branding' => false,
            'elementpath' => false,
            'menubar' => false,
            'toolbar_sticky' => true,
            'content_style' => 'body {max-width: 768px; margin: 5px auto;}.mce-content-body img{width:98%; height:98%}figure.image{margin:0px;width:100%}',
            'images_upload_url' => Url::to(['dashboard/file-upload', 'type' => 'content-image', 'fileparam' => 'file']),
            'toolbar1' => "undo redo copy cut paste | styleselect | bold italic underline | color | alignleft aligncenter alignright alignjustify | bullist numlist image | fullscreen",
            'init_instance_callback' => new JsExpression("
                                    function(editor){
                                        editor.on('Change', function (e) {
                                          initAutoSave();
                                        });
                                    }
                                "),
        ],
        'options' => ['rows' => 20],
    ]) ?>
</div>
<!-- /.box-body -->
<div class="box-footer">
    <div class="pull-right">
        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Send'), ['name' => 'action', 'value' => 'send', 'class' => 'btn btn-primary btn-flat', 'style' => 'min-width:160px']) ?>
    </div>
    <?= Html::a('<i class="fa fa-trash"></i> ' . __('Delete'), ['my-messages', 'id' => $model->messageItem->id, 'delete' => 1, 'folder' => $folder], ['class' => 'btn btn-default btn-flat  btn-delete', 'data-pjax' => 0]) ?>
    <?= Html::submitButton('<i class="fa fa-save"></i> ' . __('Save Draft'), ['name' => 'action', 'value' => 'draft', 'class' => 'btn btn-default btn-flat']) ?>
</div>
<div class="hidden">
    <?= $form->field($model, 'search')->widget(Select2Default::classname(), [
        'data' => [],
        'allowClear' => false,
    ])->label(false) ?>
</div>
<!-- /.box-footer -->
<?php ActiveForm::end() ?>

<script type="text/javascript">
    var saveTimeout;

    function initAutoSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function () {
            tinymce.triggerSave();
            $.post(
                "<?=linkTo(['message/compose', 'id' => $model->id, 'autosave' => 1])?>",
                $('#message_form').serialize(),
                function (data, status) {

                }
            );
        }, 3000);
    }

    function initForm() {
        $('#message_form input, #message_form textarea').on("keydown", function (e) {
            initAutoSave();
        });

        $('#message_form input, #post_form select, #message_form textarea').on('change', function (e) {
            initAutoSave();
        });
    }

    function initSelectGroup(data, element) {
        console.log(data);
        console.log(element);
    }

    function chooseContacts() {

    }
</script>