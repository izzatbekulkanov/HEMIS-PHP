<?php
/**
 * @var $this \frontend\components\View
 */

use lavrentiev\widgets\toastr\Notification;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;
use yii\widgets\Pjax;


$selected = $this->getSelectedSemester();
?>
<div class="content-wrapper">
    <section class="content">
        <?= $content ?>
    </section>
</div>

<footer class="main-footer">
    <div class="hidden-xs">
        <b><?= __('App Version') ?></b> <?= Yii::$app->version ?> /
        <b><?= __('Core') ?></b> <?= Yii::getVersion() ?> /
        <b><?= __('Date') ?></b> <?= Yii::$app->formatter->asDatetime('now') ?>
    </div>
</footer>