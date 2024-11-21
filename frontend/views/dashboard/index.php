<?php

use common\models\system\AdminMessageItem;
use frontend\models\curriculum\StudentCurriculum;
use yii\widgets\Pjax;

/* @var $this \frontend\components\View */
$this->title = '';
$menu = $this->getHomeLinks();
$user = $this->_user();

$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
$counts = [
   // 'message/my-messages' => AdminMessageItem::getUnReadInboxCount($user),
    //'education/curriculum' => StudentCurriculum::getCurrentSemesterSubjectCount($user, $this->getSelectedSemester()),
];
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
    <div class="row ">
        <div class="col col-md-12">
            <div class="row">
                <div class="col-md-5">
                    <div class="box box-widget widget-user text-left">
                        <div class="widget-user-header bg-aqua-active">
                            <h3 class="widget-user-username"><?= $user->getFullName() ?></h3>
                            <h5 class="widget-user-desc">
                                <?= $user->student_id_number ?>
                            </h5>
                        </div>
                        <div class="widget-user-image">
                            <img class="img-circle" src="<?= $user->getImageUrl(220, 220) ?>"
                                 alt="<?= $user->getFullName() ?>">
                        </div>
                        <div class="box-footer">
                            <? /*
                        <div class="row">
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">3,200</h5>
                                    <span class="description-text">SALES</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">13,000</h5>
                                    <span class="description-text">FOLLOWERS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4">
                                <div class="description-block">
                                    <h5 class="description-header">35</h5>
                                    <span class="description-text">PRODUCTS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                        </div>
 */ ?>
                            <!-- /.row -->
                        </div>

                    </div>
                    <div class="box box-widget">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= __('Messages') ?></h3>
                        </div>
                        <div class="box-footer box-comments">
                            <?php if (AdminMessageItem::getInboxMessages($user, 3, ['created_at' => SORT_DESC])): ?>
                                <?php foreach (AdminMessageItem::getInboxMessages($user, 3, ['created_at' => SORT_DESC]) as $message): ?>
                                    <div class="box-comment">
                                        <!-- User image -->
                                        <img class="img-circle img-sm"
                                             src="<?= $message->sender->admin->getImageUrl(60, 60) ?>"
                                             alt="<?= $message->sender->admin->getFullName() ?>">

                                        <div class="comment-text">
                                      <span class="username">
                                        <?= $message->sender->admin->getFullName() ?>
                                        <span class="text-muted pull-right"><?= $message->getTimeFormatted() ?></span>
                                      </span>
                                            <p class="message-title">
                                                <?= $message->message->title ?>
                                            </p>
                                            <div class="message-content">
                                                <?= $message->message->message ?>
                                            </div>
                                            <div class="text-right">
                                                <a href="<?= $message->getViewUrl() ?>" data-pjax="0"
                                                   class="btn btn-default btn-xs">
                                                    <i class="fa fa-envelope"></i> <?= __('O\'qish') ?></a>

                                                <a href="<?= $message->getReplyUrl() ?>" data-pjax="0"
                                                   class="btn btn-default btn-xs">
                                                    <i class="fa fa-share"></i> <?= __('Javob berish') ?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="box-footer">
                            <form action="<?= linkTo(['message/compose']) ?>" method="get">
                                <div class="img-push">
                                    <input type="text" class="form-control input-sm"
                                           name="title"
                                           placeholder="<?= __('Yangi xabar yozish') ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col col-md-7 homelinks">
                    <div class="row">
                        <?php foreach ($menu as $item) : ?>
                            <div class="col-lg-4 col-xs-6 col-sm-4">
                                <a href="<?= $item['url'] ?>" data-pjax="0">
                                    <div class="small-box bg-white">
                                        <div class="inner">
                                            <div class="icon">
                                                <img class="svg"
                                                     src="<?= $this->getImageUrl("image/icons/{$item['icon']}.png") ?>">
                                            </div>

                                            <span class="badge"><?= isset($counts[$item['id']]) ? $counts[$item['id']] : '' ?></span>
                                            <p><?= $item['label'] ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php Pjax::end(); ?>