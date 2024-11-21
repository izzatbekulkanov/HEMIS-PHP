<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\AdminResource;

use common\models\system\classifier\LocalityType;
use common\models\system\classifier\ScienceBranch;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

$this->title = $model->isNewRecord ? __('Create Doctorate Specialty') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['science/specialty'], 'label' => __('Specialty')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>
<div class="row">
    <div class="col col-md-9" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?php
                        //echo $model->specialty_id;
                       // echo $model->specialty_id;
                        $specialties = array();
                            $specialties = ScienceBranch::getChildClassifierOptions();
                        ?>
                        <?= $form->field($model, 'specialty_id')->widget(Select2Default::classname(), [
                            'data' => $specialties,
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [
                                'placeholder' => __('Choose Doctorate Specialty'),
                            ]
                        ])->label(__('Doctorate Specialty')) ?>
                        <?= $form->field($model, '_type')->widget(Select2Default::classname(), [
                            'data' => LocalityType::getClassifierOptions(),
                            'allowClear' => false,
                            'options' => [

                            ]
                        ]) ?>




                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?=$this->getResourceLink(__('Delete'), ['science/specialty-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>

</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
