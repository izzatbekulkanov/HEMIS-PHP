<?php

use backend\widgets\AceEditorWidget;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/**
 * @var $itemModel \common\models\system\classifier\_BaseClassifier
 * @var $model \common\models\system\SystemClassifier
 */

$this->title = __('Import Data');
$this->params['breadcrumbs'][] = ['url' => ['system/classifier'], 'label' => __('System Classifier')];
$this->params['breadcrumbs'][] = ['url' => ['system/classifier', 'classifier' => $model->classifier], 'label' => $model->name];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col col-md-8 col-lg-8">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 0]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <?= $form->field($model, 'import', [])->widget(
                    AceEditorWidget::className(),
                    [
                        'mode' => 'text',
                        'containerOptions' => ['style' => 'height:420px'],
                    ]
                ) ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Import'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>