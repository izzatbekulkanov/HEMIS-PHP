<?php

use backend\components\View;
use common\components\Config;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this View */

$this->title = __('System Configuration');
$this->params['breadcrumbs'][] = $this->title;
$i = -1;
?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
]); ?>
<div class="row">
    <div class="col col-md-6">
        <?php foreach (Config::getAllConfiguration() as $groupName => $items): $i++ ?>
            <div class="box box-default <?= $i != 0 ? 'collapsed-box' : '' ?>">
                <div class="box-header">
                    <h3 class="box-title"><?= $groupName ?></h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-<?= $i != 0 ? 'plus' : 'minus' ?>"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <?php foreach ($items as $key => $item): ?>
                        <?php echo $this->render('configuration/' . $item['type'], ['key' => $key, 'item' => $item, 'form' => $form]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="box-footer text-right">
            <?= Html::submitButton(__('Update'), ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
$this->registerJs('

')

?>

