<?php

use frontend\assets\FrontendAsset;
use yii\helpers\Html;

/* @var $this \backend\components\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> | <?= __('{name} axborot tizimi', ['name' => Yii::$app->name]) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="<?= $this->getBodyClass() ?> <?= isset($_COOKIE['sm_menu']) && $_COOKIE['sm_menu'] ? 'sidebar-collapse' : '' ?>">
    <?php $this->beginBody() ?>
    <?= $content ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>