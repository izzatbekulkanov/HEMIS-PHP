<?php

use common\components\Config;
use yii\web\JsExpression;

$files = [];
$file =(Config::get($item['path']));
if (is_array($file)) {
    $files[] = $file;
}
?>

<div class="form-group">
    <label class="control-label" for="config_<?= $item['path'] ?>"><?= $item['label'] ?></label>
    <?=\backend\widgets\UploadDefault::widget([
        'name' => 'config[' . $item['path'] . ']',
        'files' => $files,
        'accept' => 'image/*',
        'hiddenInputId' => 'config_' . $item['path'] . $key, // must for not use model
        'url' => ['dashboard/file-upload', 'type' => 'profile'],
        'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
        'showPreviewFilename' => false,
        'maxFileSize' => 10 * 1024 * 1024, // 10 MiB
        'maxNumberOfFiles' => 1,
        'sortable' => false,
        'multiple' => false,
        'clientOptions' => [],
    ]) ?>
    <div class="meta-block"><?= (isset($item['help']) ? $item['help'] : '') ?></div>
</div>
