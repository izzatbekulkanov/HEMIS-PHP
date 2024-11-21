<?php
/**
 * @var $model \common\models\system\SystemLog
 */

use backend\widgets\AceEditorWidget;
use yii\widgets\ActiveForm;

$this->title = strip_tags($model->getShortTitle());
$this->params['breadcrumbs'][] = ['url' => ['system/system-log'], 'label' => __('System System Log')];
$this->params['breadcrumbs'][] = $this->title;

$data = $model->getAttributes();

$model->post = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'enableClientValidation' => true, 'validateOnSubmit' => false, 'options' => ['id' => 'post_form']]); ?>

<?= $form->field($model, 'post')
    ->widget(AceEditorWidget::className(), ['options' => ['id' => 'dbg_content'], 'mode' => 'json', 'containerOptions' => ['style' => 'min-height:1000px']]) ?>
<?php ActiveForm::end(); ?>

