<?php

use common\models\system\AdminMessageItem;
use yii\widgets\Pjax;

$this->beginContent('@frontend/views/layouts/dashboard.php');
$folder = Yii::$app->request->get('folder', AdminMessageItem::TYPE_INBOX);
$stat = AdminMessageItem::getFolderCounters($this->_user());
?>
    <div class="row">
        <div class="col-md-3 hidden-print">
            <?php if (Yii::$app->controller->action->id == 'compose'): ?>
                <a href="<?= linkTo(['message/my-messages', 'folder' => 'inbox']) ?>" data-pjax="0"
                   class="btn btn-primary btn-block btn-flat margin-bottom"><?= __('Inbox') ?></a>
            <?php else: ?>
                <?php if ($folder == 'trash'): ?>
                    <div class="btn-group btn-group-justified margin-bottom" role="group">
                        <a href="<?= linkTo(['message/compose']) ?>" data-pjax="0"
                           class="btn btn-primary btn-flat margin-bottom"><?= __('Compose') ?></a>
                        <?php if ($stat['trash']): ?>
                            <a href="<?= linkTo(['message/my-messages', 'folder' => 'trash', 'clean' => 1]) ?>"
                               data-pjax="0"

                               data-confirm="<?= __('Are you sure to clean items?') ?>"
                               class="btn btn-default btn-block btn-flat "><?= __('Clean Trash') ?></a>
                        <?php endif ?>
                    </div>
                <?php else: ?>
                    <a href="<?= linkTo(['message/compose']) ?>" data-pjax="0"
                       class="btn btn-primary btn-block btn-flat margin-bottom"><?= __('Compose') ?></a>
                <?php endif ?>

            <?php endif ?>


            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Folders') ?></h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <?php foreach (AdminMessageItem::getFolderOptions() as $option => $label): ?>
                            <li class="<?= $folder == $option ? 'active' : '' ?>">
                                <a href="<?= linkTo(['message/my-messages', 'folder' => $option]) ?>">
                                    <i class="fa fa-folder-<?= $option ?>"></i> <?= $label ?>
                                    <?php if ($stat[$option]): ?>
                                        <span class="label label-default pull-right"><?= $stat[$option] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col col-md-9">
            <div class="box box-primary ">
                <?= $content ?>
            </div>
        </div>
    </div>
<?php $this->endContent() ?>