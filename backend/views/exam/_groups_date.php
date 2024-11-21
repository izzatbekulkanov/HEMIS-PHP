<?php
/**
 * @var $model \common\models\curriculum\EExamGroup
 * @var $this \backend\components\View
 */

use backend\widgets\DateTimePickerDefault;
use common\models\student\EGroup;
use kartik\form\ActiveForm;

$type = Yii::$app->request->get('type');
if ($model->$attribute == null) {
    $model->$attribute = $model->exam->$attribute;
}
?>
<div style="margin: -15px -15px -35px">
    <div class="box no-border ">
        <div class="box-body">
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, $attribute)->widget(DateTimePickerDefault::classname(), [
                'type' => 4,
                'options' => [
                    'id' => 'group_date',
                    'readonly' => false,
                    'placeholder' => __('YYYY-MM-DD H:i'),
                ],
            ])->label(false); ?>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="box-footer ">
            <div class="text-right">
                <button class="btn btn-primary btn-flat btn-block" onclick="setGroupDate()">
                    <i class="fa fa-check"></i> <?= __('Guruhga sanani belgilash') ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function setGroupDate() {
        $.get('<?=currentTo([])?>&date=' + $('#group_date').val(), function (data) {
            $.pjax.reload('#group-list');
            $('#modal').modal('hide');
        });
    }
</script>
