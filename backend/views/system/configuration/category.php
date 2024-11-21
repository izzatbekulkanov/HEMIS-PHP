<?php
use common\components\Config;
use yii\helpers\Json;
use yii2mod\chosen\ChosenSelectAsset;

ChosenSelectAsset::register($this);
$js = '$("#config_' . $item['path'] . '").chosen(' . Json::encode(['allow_single_deselect' => true, 'width' => '100%']) . ');';

$this->registerJs($js);

?>
<div class="form-group">
    <label class="control-label" for="config_<?= $item['path'] ?>"><?= $item['label'] ?></label>
    <select class="form-control selectpicker show-tick"
            name="config[<?= $item['path'] ?>]"
            id="config_<?= $item['path'] ?>">
        <?php foreach ($item['options'] as $value => $label): ?>
            <option value="<?= $value ?>"
                <?= ($value == Config::get($item['path'])) ? 'selected="selected"' : '' ?>
            >
                <?= $label ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="meta-block"><?= (isset($item['help']) ? $item['help'] : '') ?></div>
</div>