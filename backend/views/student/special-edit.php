<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\AdminResource;

use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\LocalityType;
use common\models\structure\EDepartment;
use common\models\system\classifier\MasterSpeciality;
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

$this->title = $model->isNewRecord ? __('Create Specialty') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['student/special'], 'label' => __('Special')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>
<div class="row">
    <div class="col col-md-9" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN) { ?>
                            <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => false,
                            ]) ?>
                        <?php } ?>
                        <?= $form->field($model, '_education_type')->widget(Select2Default::classname(), [
                            'data' => EducationType::getHighers(),
                            'allowClear' => false,
                            'options' => [
                                'id' => '_education_type',
                            ],
                        ]) ?>

                        <?php
                        $specialties = array();
                        if ($model->_education_type == EducationType::EDUCATION_TYPE_BACHELOR) {
                            $specialties = BachelorSpeciality::getChildClassifierOptions();
                        } elseif ($model->_education_type == EducationType::EDUCATION_TYPE_MASTER) {
                            $specialties = MasterSpeciality::getChildClassifierOptions();
                        }
                        ?>
                        <?= $form->field($model, 'specialty_id')->widget(DepDrop::classname(), [
                            'data' => $specialties,
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => 'code',
                                'placeholder' => __('-Choose-'),
                            ],
                            'pluginOptions' => [
                                'depends' => ['_education_type'],
                                'url' => Url::to(['/ajax/get-special-classifiers']),
                            ],
                        ]) ?>
                        <?= $form->field($model, '_type')->widget(Select2Default::classname(), [
                            'data' => LocalityType::getClassifierOptions(),
                            'allowClear' => false,
                            'options' => [

                            ]
                        ]) ?>

                        <? //= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        <? //= $form->field($model, 'code')->textInput(['maxlength' => true])->label() ?>


                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?=$this->getResourceLink(__('Delete'), ['student/special-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
