<?php

use backend\components\View;
use backend\widgets\Select2Default;
use common\models\curriculum\ECurriculum;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentSuccess;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this View
 */
$this->title = $model->isNewRecord ? __('Create Qualification') : __('Manage Qualification');
$this->params['breadcrumbs'][] = ['url' => ['student/qualification'], 'label' => __('Qualification')];
$this->params['breadcrumbs'][] = $this->title;
if ($this->_user()->role->isDeanRole() && Yii::$app->user->identity->employee->deanFaculties) {
    $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
} else {
    $faculty = "";
}
?>
<?php
$form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-12" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($model, '_specialty')->widget(
                            Select2Default::classname(),
                            [
                                'data' => ESpecialty::getHigherSpecialty($faculty),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                ]
                            ]
                        )->label(); ?>
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'description')->textarea(['rows' => 12, 'maxlength' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer text-right">
            <?php
            if (!$model->isNewRecord): ?>
                <?= $this->getResourceLink(
                    __('Delete'),
                    ['student/qualification-edit', 'id' => $model->id, 'delete' => 1],
                    ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]
                ) ?>
            <?php
            endif; ?>
            <?= Html::submitButton(
                '<i class="fa fa-check"></i> ' . __('Save'),
                ['class' => 'btn btn-primary btn-flat']
            ) ?>
        </div>
    </div>
</div>

</div>
<?php
ActiveForm::end(); ?>
<?php
$this->registerJs(
    '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
)
?>
