<?php

use common\components\Config;

?>

<div class="form-group">
    <label class="control-label" for="config_<?= $item['path'] ?>"><?= $item['label'] ?></label>
    <textarea
            class="form-control"
            id="config_<?= $item['path'] ?>"
            name="config[<?= $item['path'] ?>]"
            placeholder="<?= $item['label'] ?>"><?= Config::get($item['path']) ?></textarea>

    <div class="meta-block"><?= (isset($item['help']) ? $item['help'] : '') ?></div>
</div>
