<?php
/**
 * @var $this \backend\components\View
 * @var $form \yii\widgets\ActiveForm
 * @var $itemModel \common\models\system\classifier\_BaseClassifier
 * @var $model \common\models\system\SystemClassifier
 */
?>
<?= $form->field($itemModel, 'type')->widget(\backend\widgets\Select2Default::classname(), [
    'data' => \common\models\system\classifier\BachelorSpeciality::getTypeOptions(),
    'hideSearch' => true,
    'allowClear' => true,
    'disabled' => HEMIS_INTEGRATION,
]) ?>
<?= $form->field($itemModel, 'year')->textInput(['maxlength' => true, 'disabled' => HEMIS_INTEGRATION]) ?>