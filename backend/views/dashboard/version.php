<?php
$Parsedown = new Parsedown();
$content = file_get_contents(Yii::getAlias('@root/version.md'));

$pattern = '/#Version (([0-9\.]+)+)/';
$replacement = '#Version ${2} <a name="${2}"></a>';
$links = [];
$content = preg_replace_callback($pattern, function ($replacement) use (&$links) {
    $links[] = $replacement[1];
    return "#Version $replacement[1] <a name='version_$replacement[1]'></a>";
}, $content);
$text = $Parsedown->text($content);
?>
<div class="row">
    <div class="col-lg-8 col-md-12">
        <h1><?= __("O'zgarishlar tarixi") ?></h1>
        <ol>
            <?php foreach ($links as $link): ?>
                <li><a href="#version_<?= $link ?>">Version <?= $link ?></a></li>
            <?php endforeach; ?>
        </ol>
        <hr>
        <?= $text ?>
    </div>
</div>
