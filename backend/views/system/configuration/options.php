<?php
use common\components\Config;
?>
<div class="form-group">
    <label class="control-label" for="config_<?= $item['path'] ?>"><?= $item['label'] ?></label>
    <select class="form-control "
            name="config[<?= $item['path'] ?>]"
            id="config_<?= $item['path'] ?>"
    >
        <?php foreach ($item['options'] as $value => $label): ?>
            <option value="<?= $value ?>"
                <?= ($value == Config::get($item['path'])) ? 'selected="selected"' : '' ?>
            >
                <?= $label ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="meta-block"><?= (isset($item['help']) ?$item['help']: '') ?></div>
</div>