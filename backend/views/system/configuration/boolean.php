<?php

use backend\widgets\checkbo\CheckBo;
use common\components\Config;

?>
<div class="form-group">
    <div class="togglebutton">
        <?php echo CheckBo::widget([
            'name' => "config[" . $item['path'] . "]",
            'attribute' => $item['label'],
            'value' => Config::get($item['path']) || isset($item['checked']) && $item['checked'],
            'options' => [
                'checked' => Config::get($item['path']) ? 'checked' : (isset($item['checked']) ? $item['checked'] : ''),
                'disabled' => isset($item['disabled']) ? $item['disabled'] : false,
            ],

        ]) ?>
    </div>
</div>