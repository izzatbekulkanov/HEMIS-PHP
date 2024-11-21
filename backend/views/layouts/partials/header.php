<?php

use common\models\system\AdminMessageItem;
use common\models\system\AdminRole;
use yii\helpers\Html;
use yii\bootstrap\Modal;

/* @var $this \backend\components\View */
/* @var $content string */

$user = $this->_user();
?>
<?php
yii\bootstrap\Modal::begin([
    'headerOptions' => ['id' => 'modalHeader', 'style' => 'opacity:1.00;'],
    'header' => __('Group Schedule Information'),

    'id' => 'modal',
    'size' => 'modal-lg',
    'closeButton' => ['label' => '&times;'],
    //keeps from closing modal with esc key or by clicking out of the modal.
    // user must click cancel or X to close
    'clientOptions' => ['keyboard' => TRUE],
]);
echo "<div id='modalContent'></div>";
yii\bootstrap\Modal::end();
?>
<?php
/*yii\bootstrap\Modal::begin([
    'headerOptions' => ['id' => 'modalHeader'],
    'id' => 'modalLg',
    'size' => 'modal-lg',
    'closeButton' => ['label' => '&times;'],
    //keeps from closing modal with esc key or by clicking out of the modal.
    // user must click cancel or X to close
    'clientOptions' => ['backdrop' => 'X', 'keyboard' => TRUE]
]);
echo "<div id='modalContent'></div>";
yii\bootstrap\Modal::end();*/
?>
<header class="main-header">

    <?= Html::a('<span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <?php if ($this->_user()->role->isDeanOrTutorRole()) {
                    @$faculty = Yii::$app->user->identity->employee->deanFaculties->name;
                    ?>
                    <li class="pull-left">
                        <?= Html::a(
                            "<i class='fa fa-building'></i> " . @$faculty,
                            [''],
                            []
                        ) ?>
                    </li>
                <?php } ?>
                <?php $langs = \common\components\Config::getLanguageOptions(); ?>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?= $langs[Yii::$app->language] ?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($langs as $lang => $label): ?>
                            <?php
                            if (!\common\components\Config::isLanguageEnable($lang)) continue;
                            ?>
                            <li>
                                <a href="<?= \yii\helpers\Url::current(['language' => $lang]) ?>">
                                    <?= $label ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li>
                    <a href="<?= linkTo(['system/cache']) ?>">
                        <i class="fa fa-refresh"></i>
                    </a>
                </li>
                <?php if ($count = AdminMessageItem::getUnReadInboxCount($user)): ?>
                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning"><?= $count ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <ul class="menu">
                                    <?php foreach (AdminMessageItem::getInboxMessages($user, 8) as $item): ?>
                                        <li class="<?= $item->opened ? 'open-message' : '' ?>">
                                            <a href="<?= $item->getViewUrl() ?>">
                                                <i class="fa fa-envelope<?= $item->opened ? '-open' : '' ?>-o"></i> <?= $item->message->getShortTitle() ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="<?= linkTo(['message/my-messages']) ?>"><?= __('View all') ?></a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="dropdown  user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $user->getImageUrl() ?>" class="user-image" alt="<?= $user->getFullName() ?>"/>
                        <span class="user-name"><?= $user->getFullName() ?></span>
                        <span class="user-role"><?= $user->role->name ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (count($user->roles) > 1): ?>
                            <li class="dropdown-header"><?= __('User Roles') ?></li>
                            <?php foreach ($user->roles as $role): ?>
                                <li class="<?= $user->_role == $role->id ? 'active' : '' ?>">
                                    <?= Html::a(
                                        $role->name,
                                        ['/dashboard/switch-role', 'id' => $role->id],
                                        []
                                    ) ?>
                                </li>
                            <?php endforeach; ?>
                            <li role="separator" class="divider"></li>
                        <?php endif; ?>
                        <li>
                            <?= Html::a(
                                __('Profile'),
                                ['/dashboard/profile'],
                                []
                            ) ?>
                        </li>
                        <li role="separator" class="divider"></li>
                        <li>
                            <?= Html::a(
                                "<i class='fa fa-sign-out'></i> " . __('Sign out'),
                                ['/dashboard/logout'],
                                []
                            ) ?>
                        </li>
                    </ul>


                </li>
            </ul>
        </div>
    </nav>
</header>
