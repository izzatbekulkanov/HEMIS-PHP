<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\EducationYear;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

\kartik\date\DatePickerAsset::registerBundle($this, '3.x');
?>
<?//php Pjax::begin([]) ?>
<div class="row">
    <?//php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'enableClientValidation' => true, 'validateOnSubmit' => true, 'options' => ['data-pjax' => false]]); ?>

    <?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'options' => ['data-pjax' => 0]]); ?>
    <?php echo $form->errorSummary($model);?>
    <div class='box-body'>
        <div class="row">
            <div class="col col-md-12">
                <?= $form->field($model, 'month_name')->widget(DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                        'id' => 'month_name',
                        'readonly' => true,
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'startView'=>'year',
                        'minViewMode'=>'months',
                        'format' => 'YYYY-MM'
                    ]
                ]); ?>
                <?= $form->field($model, 'summa')->textInput(['maxlength' => true, 'id' => 'summa']) ?>

            </div>
        </div>
    </div>

    <div class='box-footer text-right'>
        <button type="button" class="btn btn-flat btn-default"
                data-dismiss="modal"><?= __('Close') ?></button>
        <?php if(!$model->isNewRecord): ?>
            <?= $this->getResourceLink(__('Delete'), ['finance/scholarship', 'month' => $model->id,  'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
        <?php endif;?>
        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>



    </div>


    <?php ActiveForm::end(); ?>

</div>

<?//php Pjax::end() ?>