<?php

use backend\assets\BackendAsset;
use yii\helpers\Html;

/* @var $this \backend\components\View */
/* @var $content string */
BackendAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> </title>
        <?php $this->head() ?>
    </head>
    <body class="<?= $this->getBodyClass() ?> <?= isset($_COOKIE['sm_menu']) && $_COOKIE['sm_menu'] ? 'sidebar-collapse' : '' ?>">
    <?php $this->beginBody() ?>
    <?= $content ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>