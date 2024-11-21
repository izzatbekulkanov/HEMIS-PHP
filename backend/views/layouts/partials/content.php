<?php

use lavrentiev\widgets\toastr\Notification;
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;

?>
<div class="content-wrapper">
    <section class="content-header">
        <?= \backend\widgets\BreadCrumbsDefault::widget(
            [
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]
        ) ?>
    </section>

    <section class="content">
        <?= $content ?>
    </section>
</div>

<footer class="main-footer">
    <div class="hidden-xs">
        <b><?= __('App Version') ?></b> <a href="<?= linkTo(['dashboard/version']) ?>"><?= Yii::$app->version ?></a> /
        <b><?= __('Core') ?></b> <?= Yii::getVersion() ?> /
        <b><?= __('Date') ?></b> <?= Yii::$app->formatter->asDatetime('now') ?>
    </div>
</footer>