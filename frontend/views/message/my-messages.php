<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\AdminMessage;
use common\models\system\AdminMessageItem;
use common\models\system\AdminResource;
use common\models\system\SystemClassifier;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model AdminMessageItem */
$folderTitle = AdminMessageItem::getFolderTitle($folder);

$this->params['breadcrumbs'][] = ['url' => ['message/my-messages'], 'label' => __('My Messages')];
if (isset($model)) {
    $this->params['breadcrumbs'][] = ['url' => ['message/my-messages', 'folder' => $folder], 'label' => $folderTitle];
    $this->title = $model->message->getShortTitle();
} else {
    $this->title = $folderTitle;
}

$this->params['breadcrumbs'][] = $this->title;
?>
<?php if (isset($model)): ?>
    <div class="box-header with-border hidden-print">
        <h3 class="box-title"><?= __('Read Mail') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= Url::current(['prev' => 1, 'next' => null]) ?>" class="btn btn-box-tool"
               data-toggle="tooltip"
               title="" data-original-title="Previous"><i class="fa fa-chevron-left"></i></a>
            <a href="<?= Url::current(['next' => 1, 'prev' => null]) ?>" class="btn btn-box-tool"
               data-toggle="tooltip"
               title="" data-original-title="Next"><i class="fa fa-chevron-right"></i></a>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body no-padding">
        <div class="mailbox-read-info">
            <h3><?= $model->message->title ?></h3>
            <h5>
                <span class="text-muted"><?= __('From:') ?></span> <?= $model->message->getSenderInformation('name') ?>
                <br>
                <span class="text-muted"><?= __('To:') ?></span> <?= $model->message->getRecipientInformation() ?>
                <span class="mailbox-read-time pull-right"><?= Yii::$app->formatter->asDatetime($model->message->send_on ? $model->message->send_on->getTimestamp() : $model->message->created_at->getTimestamp()) ?></span>
            </h5>
        </div>
        <!-- /.mailbox-read-info -->

        <!-- /.mailbox-controls -->
        <div class="mailbox-read-message">
            <?= $model->message->message ?>
        </div>
        <!-- /.mailbox-read-message -->
    </div>
    <!-- /.box-body -->
    <?php if (!empty($model->message->files)): ?>
        <div class="box-footer hidden-print">
            <ul class="mailbox-attachments clearfix">
                <?php foreach ($model->message->files as $file): ?>
                    <li>
                        <span class="mailbox-attachment-icon"><i class="fa fa-file-pdf-o"></i></span>

                        <div class="mailbox-attachment-info">
                            <a href="#" class="mailbox-attachment-name"><i class="fa fa-paperclip"></i>
                                Sep2014-report.pdf</a>
                            <span class="mailbox-attachment-size">
                          1,245 KB
                          <a href="<?= Url::current(['download' => md5($file['name'])]) ?>>"
                             class="btn btn-default btn-xs pull-right">
                              <i class="fa fa-cloud-download"></i>
                          </a>
                        </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <!-- /.box-footer -->
    <div class="box-footer hidden-print">
        <?php if (!$model->isDeleted()): ?>
            <div class="pull-right">
                <?php if ($model->isInboxMessage()): ?>
                    <a data-pjax=0 href="<?= linkTo(['message/compose', 'reply' => $model->id]) ?>"
                       class="btn btn-primary btn-flat">
                        <i class="fa fa-reply"></i> <?= __('Reply') ?>
                    </a>
                <?php endif; ?>
                <a data-pjax=0 href="<?= linkTo(['message/compose', 'forward' => $model->id]) ?>"
                   class="btn btn-default btn-flat">
                    <i class="fa fa-share"></i> <?= __('Forward') ?>
                </a>
            </div>
        <?php else: ?>
            <div class="pull-right">
                <a href="<?= linkTo(['my-messages', 'id' => $model->id, 'folder' => $folder, 'remove' => 1]) ?>"
                   class="btn btn-danger btn-delete btn-flat"
                   data-pjax="0">
                    <i class="fa fa-trash-o"></i> <?= __('Remove') ?></a>
            </div>
        <?php endif; ?>
        <?php if ($model->isDeleted()): ?>
            <a href="<?= linkTo(['my-messages', 'id' => $model->id, 'folder' => $folder, 'restore' => 1]) ?>"
               class="btn btn-default btn-flat"
               data-pjax="0"
               data-confirm="<?= __('Are you sure to restore?') ?>">
                <i class="fa fa-check"></i> <?= __('Restore') ?></a>
        <?php else: ?>
            <a href="<?= linkTo(['my-messages', 'id' => $model->id, 'folder' => $folder, 'delete' => 1]) ?>"
               class="btn btn-default btn-delete btn-flat"
               data-pjax="0">
                <i class="fa fa-trash-o"></i> <?= __('Trash Message') ?></a>
        <?php endif; ?>
        <button type="button" onclick="window.print();" class="btn btn-default btn-flat">
            <i class="fa fa-print"></i> <?= __('Print') ?></button>
    </div>
    <!-- /.box-footer -->
<?php else: ?>
    <div class="box-header with-border">
        <div id="data-grid-filters">
            <?php $form = ActiveForm::begin(['options' => ['data-pjax' => 1]]); ?>
            <h3 class="box-title"><?= $this->title ?></h3>

            <div class="box-tools pull-right">
                <div class="has-feedback">
                    <?= $form->field($searchModel, 'search', ['template' => '{input}', 'options' => ['tag' => null]])->textInput(['placeholder' => __('Search Message'), 'class' => 'form-control input-sm'])->label(false) ?>
                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php Pjax::begin(['timeout' => false, 'options' => ['data-pjax' => true], 'enablePushState' => false]) ?>
    <?= $this->render("my-messages-{$folder}.php", ['folder' => $folder, 'dataProvider' => $dataProvider]) ?>
    <?php Pjax::end() ?>
<?php endif; ?>
<script>
    var mailItemToggle = false;

    function deleteMailItems() {
        if (confirm(decodeEntities("<?=Html::encode(__('Are you sure delete selected messages?'))?>"))) {

        }
    }

    function toggleMailItems() {
        if (mailItemToggle) {
            $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
            $(".checkbox-toggle .fa").removeClass("fa-check-square-o").addClass('fa-square-o');
        } else {
            $(".mailbox-messages input[type='checkbox']").iCheck("check");
            $(".checkbox-toggle .fa").removeClass("fa-square-o").addClass('fa-check-square-o');
        }
        mailItemToggle = !mailItemToggle;
    }
</script>
