<?php

use frontend\assets\FrontendAsset;

FrontendAsset::register($this);
$this->addBodyClass('sidebar-mini skin-blue');
?>
<?php $this->beginContent('@frontend/views/layouts/main.php'); ?>
    <div class="wrapper">

        <?= $this->render('partials/header.php') ?>

        <?= $this->render('partials/left.php') ?>

        <?= $this->render('partials/content.php', ['content' => $content]) ?>

    </div>
<?php $this->endContent() ?>