<?php

use frontend\assets\FrontendAsset;

FrontendAsset::register($this);
?>
<?php $this->beginContent('@frontend/views/layouts/main.php'); ?>
<?= $content ?>
<?php $this->endContent() ?>