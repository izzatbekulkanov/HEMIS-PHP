<?php

use backend\widgets\Select2Default;
use common\models\system\Admin;
use dosamigos\tinymce\TinyMce;
use dosamigos\tinymce\TinyMceAsset;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\system\AdminMessage
 */
$this->title = $model->getShortTitle();
$this->params['breadcrumbs'][] = ['url' => ['message/all-messages'], 'label' => __('My Messages')];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-9">
        <div class="box box-primary">

            <div class="box-body no-padding">
                <div class="mailbox-read-info">
                    <h3><?= Html::encode($model->title) ?></h3>
                    <h5>
                        <?= __('From: {sender}', ['sender' => $model->getSenderInformation('name')]) ?><br>
                        <?= __('To: {recipient}', ['recipient' => $model->getRecipientInformation()]) ?>
                        <span class="mailbox-read-time pull-right"><?= Yii::$app->formatter->asDatetime($model->send_on ? $model->send_on->getTimestamp() : $model->updated_at->getTimestamp()) ?></span>
                    </h5>
                </div>
                <!-- /.mailbox-read-info -->

                <!-- /.mailbox-controls -->
                <div class="mailbox-read-message">
                    <?= $model->message ?>
                </div>
                <!-- /.mailbox-read-message -->
            </div>
            <!-- /.box-body -->
            <?php if (!empty($model->files)): ?>
                <div class="box-footer hidden-print">
                    <ul class="mailbox-attachments clearfix">
                        <?php foreach ($model->files as $file): ?>
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
            <!-- /.box-footer -->
        </div>
        <!-- /. box -->
    </div>
    <!-- /.col -->
</div>