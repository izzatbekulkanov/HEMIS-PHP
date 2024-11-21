<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use backend\widgets\SelectizeDefault;
use common\components\Config;
use common\models\curriculum\EducationYear;
use common\models\employee\EEmployeeAcademicDegree;
use common\models\system\classifier\Gender;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use common\models\system\classifier\CitizenshipType;
use backend\widgets\Select2Default;
use common\models\system\classifier\ScienceBranch;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;


/* @var $this \backend\components\View */
/* @var $model \common\models\student\EStudentOlympiad */

$this->title = $model->isNewRecord ? __('Insert Student Olympiad') : $model->student->getFullName();
$this->params['breadcrumbs'][] = ['url' => ['student/olympiad'], 'label' => __('Student Olympiad')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'id' => 'employee_form', 'options' => ['data' => ['pjax' => false]]]); ?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                            'data' => EducationYear::getEducationYears(),
                            'allowClear' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <?= $form->field($model, '_student', ['options' => ['class' => '']])
                                ->widget(SelectizeDefault::className(), [
                                    'loadUrl' => Url::current(['students' => 1]),
                                    'options' => [
                                        'disabled' => !$model->isNewRecord,
                                    ],
                                    'clientOptions' => [
                                        'maxItems' => 1,
                                        'maxOptions' => 10,
                                        'hideSelected' => true,
                                        'preload' => true,
                                        'openOnFocus' => true,
                                        'valueField' => 'id',
                                        'labelField' => 'name',
                                        'searchField' => ['name', 'code'],
                                        'options' => [[
                                            'id' => $model->_student,
                                            'name' => $model->_student ? $model->student->getFullName() : '',
                                            'code' => $model->_student ? $model->student->student_id_number : '',
                                        ]],
                                        'create' => true,
                                        'render' => [
                                            'option' => new JsExpression("
                                                function(item, escape) {
                                                    return '<div>' +
                                                        '<span class=\"item-option\">' + escape(item.name) + '</span>' +
                                                        '<span class=\"item-label\">' + escape(item.code) + '</span>' +
                                                    '</div>';
                                                }"),
                                        ]
                                    ],
                                ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= $form->field($model, 'olympiad_type')->widget(Select2Default::classname(), [
                                'data' => \common\models\student\EStudentOlympiad::getTypeOptions(),
                                'allowClear' => false,
                                'hideSearch' => false,
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'olympiad_name')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'olympiad_section_name')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_country')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\Country::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-9">
                        <?= $form->field($model, 'olympiad_place')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'olympiad_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'readonly' => true,
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'student_place')->input('number', ['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'diploma_serial')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'diploma_number')->input('number', ['maxlength' => true]) ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['student/olympiad', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['id' => 'submitButton', 'class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div class="row">
    <div class="col-md-5">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>

<script>

</script>