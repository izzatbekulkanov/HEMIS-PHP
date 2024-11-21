<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\AdminResource;
use common\models\system\AdminRole;

use common\models\system\classifier\EducationForm;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;

use common\models\curriculum\ECurriculum;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use kartik\date\DatePicker;
use common\models\curriculum\Semester;

$this->title = $model->isNewRecord ? __('Create Semester') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/semester'], 'label' => __('Semester')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-5" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">

                        <?= $form->field($model, 'code')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\Semester::getClassifierOptions(),
                            'allowClear' => false,
                            'placeholder' => __('-Choose Semester-'),
                        ])->label(__('Semester')) ?>
                        <?= $form->field($model, '_level')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\Course::getClassifierOptions(),
                            'allowClear' => false,
                        ])->label(__('Level')) ?>
                        <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                            'data' => EducationYear::getEducationYears(),
                            'allowClear' => false,
                        ]) ?>
                        <?= $form->field($model, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => ECurriculum::getOptions($faculty),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>

                        <?php
                        $curriculums = ECurriculum::find()
                            ->where(['_education_year' => $model->_education_year, 'active' => true])
                            ->all();
                        ?>
                        <?= $form->field($model, 'start_date')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => __('Enter start_date')],
                            'layout' => '{input}{picker}{remove}',
                            'pluginOptions' => [
                                'autoclose' => true,
                                'daysOfWeekDisabled' => [0, 7],
                                'weekStart' => '1',
                                'format' => 'yyyy-mm-dd',
                            ]
                        ]); ?>
                        <?= $form->field($model, 'end_date')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => __('Enter end_date')],
                            'layout' => '{input}{picker}{remove}',
                            'pluginOptions' => [
                                'autoclose' => true,
                                'daysOfWeekDisabled' => [0, 7],
                                'weekStart' => '1',
                                'format' => 'yyyy-mm-dd',
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['curriculum/semester-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
