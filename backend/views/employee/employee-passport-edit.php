<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\system\classifier\Gender;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use common\models\system\classifier\CitizenshipType;
use backend\widgets\Select2Default;
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
/* @var $model \common\models\employee\EEmployee */

$this->title = __('Edit Employee Passport Data');
$this->params['breadcrumbs'][] = ['url' => ['employee/employee'], 'label' => __('Employee Employee')];
$this->params['breadcrumbs'][] = ['url' => ['employee/employee', 'id' => $model->id], 'label' => $model->fullName];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initEmployeeForm()");
$disabled = !$user->canAccessToResource('employee/employee-passport-edit');

\yii\widgets\MaskedInputAsset::register($this);
?>


<? //php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data' => ['pjax' => false]]]); ?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-10">
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, '_citizenship')->widget(Select2Default::classname(), [
                                    'data' => CitizenshipType::getClassifierOptions(),
                                    'allowClear' => false,
                                    'disabled' => $disabled,
                                    'options' => [
                                        'id' => '_citizenship'
                                    ],
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'passport_number')->textInput([
                                    'id' => 'passport_number',
                                    'readonly' => $disabled,
                                ]) ?>
                            </div>
                            <?php
                            $btn = '<span class="input-group-btn"><button class="btn btn-default" onclick="getEmployeeInfo()" ' . ($disabled ? 'disabled' : '') . ' type="button"><i id="fa_search" class="fa fa-search"></i><i id="fa_spinner" style="display: none" class="fa fa-spinner fa-spin"></i> </button></span>';
                            ?>
                            <div class="col-md-4">
                                <?php
                                $title = __('Bu qanday kod?');
                                $label = $model->getAttributeLabel('passport_pin');
                                $link = Url::current(['pin_hint' => 1]);
                                $pinBtn = "<span class='showModalButton hint' value='$link' title='$title'>$title</span>";
                                ?>
                                <?= $form->field($model, 'passport_pin', ['template' => "<label class='control-label' for='passport_pin'>$label </label>$pinBtn<div class='input-group'>{input}$btn</div>{error}"])->textInput([
                                    'id' => 'passport_pin',
                                    'readonly' => true,
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, 'second_name')->textInput(['maxlength' => true, 'id' => 'second_name', 'disabled' => $disabled]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'id' => 'first_name', 'disabled' => $disabled]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'third_name')->textInput(['maxlength' => true, 'id' => 'third_name', 'disabled' => $disabled]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, 'birth_date')->widget(DatePickerDefault::classname(), [
                                    'options' => [
                                        'placeholder' => __('YYYY-MM-DD'),
                                        'id' => 'birth_date',
                                    ],
                                    'disabled' => $disabled
                                ]); ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, '_gender')->widget(Select2Default::classname(), [
                                    'data' => Gender::getClassifierOptions(),
                                    'allowClear' => false,
                                    'disabled' => $disabled,
                                    'options' => [
                                        'id' => '_gender',
                                    ],
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                            </div>

                        </div>
                    </div>

                    <div class="col col-md-2">
                        <?= $form->field($model, 'image')
                            ->widget(Upload::className(), [
                                'url' => ['dashboard/file-upload', 'type' => 'profile'],
                                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'sortable' => true,
                                'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple' => false,
                                'clientOptions' => [],
                            ]); ?>
                    </div>
                </div>
            </div>

            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<script>
    var hemisIntegration = <?=HEMIS_INTEGRATION ? 'true' : 'false'?>;
    var infoErrors = 0;

    function checkCitizenship() {
        return $('#_citizenship').val() === '11';
    }

    function initCitizenship() {
        var id = $('#_citizenship').val();
        if (!$('#_citizenship').is(':disabled')) {
            if (id === '11' || id === '13') {
                if (id === '11') {
                    if (hemisIntegration) {
                        /*$("#first_name").attr('readonly', true);
                        $("#second_name").attr('readonly', true);
                        $("#third_name").attr('readonly', true);
                        $("#birth_date").attr('readonly', true);
                        $("#_gender").attr('readonly', true);*/
                    }
                } else {
                    $("#first_name").attr('readonly', false);
                    $("#second_name").attr('readonly', false);
                    $("#third_name").attr('readonly', false);
                    $("#birth_date").attr('readonly', false);
                    $("#_gender").attr('readonly', false);
                }

                $("#passport_number").inputmask({"clearIncomplete": true, "greedy": true, "mask": ["AA9999999"]});
                $("#passport_pin").inputmask({"clearIncomplete": true, "greedy": true, "mask": ["99999999999999"]});
            } else {
                $("#first_name").attr('readonly', false);
                $("#second_name").attr('readonly', false);
                $("#third_name").attr('readonly', false);
                $("#birth_date").attr('readonly', false);
                $("#_gender").attr('readonly', false);
                $("#passport_number").inputmask('remove');
                $("#passport_pin").inputmask('remove');
            }
        }
    }

    function initEmployeeForm() {
        $('#_citizenship').change(function () {
            initCitizenship();
        });
        initCitizenship();
    }

    function getEmployeeInfo() {
        var pin = $('#passport_pin').val();
        var num = $('#passport_number').val();
        var citizenship = $('#_citizenship').val();
        if (num.length === 9 && pin.length === 14 && citizenship === '11' && hemisIntegration) {

            if (pin.search('_') === -1 && num.search('_') === -1) {
                $('#fa_search').hide();
                $('#fa_spinner').show();
                $.ajax({
                    url: '<?=Url::current(['info' => 1]);?>',
                    type: "GET",
                    data: {passport: num, pin: pin},
                    dataType: "json",
                    success: function (data) {
                        $('#fa_search').show();
                        $('#fa_spinner').hide();
                        if (data.success) {
                            $("#first_name").val(data.first_name);
                            $("#second_name").val(data.second_name);
                            $("#third_name").val(data.third_name);
                            $('#birth_date').val(data.birth_date);
                            $("#_gender").val(data.gender);
                            $('#_gender').trigger('change');
                        } else {
                            infoErrors++;
                            if (data.manual || infoErrors > 3) {
                                $("#first_name").attr('readonly', false);
                                $("#second_name").attr('readonly', false);
                                $("#third_name").attr('readonly', false);
                                $("#birth_date").attr('readonly', false);
                                $("#_gender").attr('readonly', false);
                            }
                            alert(data.error);
                        }
                    }
                });
            }
        }
    }
</script>