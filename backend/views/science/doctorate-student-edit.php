<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use common\components\Config;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\Nationality;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Country;
use common\models\system\classifier\Soato;
use common\models\system\classifier\Gender;
use common\models\system\classifier\Course;
use common\models\system\classifier\Accommodation;
use common\models\system\classifier\SocialCategory;
use common\models\system\classifier\ScienceBranch;
use common\models\system\classifier\DoctoralStudentType;
use common\models\system\classifier\DoctorateStudentStatus;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\curriculum\Semester;
use common\models\curriculum\ECurriculum;
use common\models\structure\EDepartment;
use common\models\science\EDoctorateStudent;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use common\models\system\classifier\StudentStatus;
use common\models\system\AdminRole;

/* @var $this \backend\components\View */
/* @var $model \common\models\student\EStudent */

$this->title = $model->isNewRecord ? __('Create Doctorate Student') : $model->fullName;
$this->params['breadcrumbs'][] = ['url' => ['science/doctorate-student'], 'label' => __('Doctorate Student')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initStudentForm()");
\yii\widgets\MaskedInputAsset::register($this);
$disabled = $model->student_id_number != null && in_array($model->_citizenship, [CitizenshipType::CITIZENSHIP_TYPE_UZB, CitizenshipType::CITIZENSHIP_TYPE_NOTCITIZENSHIP]);
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data' => ['pjax' => false]]]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Education Info'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_doctoral_student_type')->widget(Select2Default::classname(), [
                            'data' => DoctoralStudentType::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [

                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, '_science_branch_id')->widget(Select2Default::classname(), [
                            'data' => ScienceBranch::getParentClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [

                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                            $specialties = array();
                            $specialties = ESpecialty::getDoctorateSpecialtyList();
                        ?>
                        <?= $form->field($model, '_specialty_id')->widget(Select2Default::classname(), [
                            'data' => $specialties,
                            'allowClear' => false,
                            'placeholder' => false,
                            'options' => [
                                'id' => '_specialty_id'
                            ],
                        ])->label(__('Doctorate Specialty')) ?>
                    </div>

                    <div class="col-md-2">
                        <?= $form->field($model, '_payment_form')->widget(Select2Default::classname(), [
                            'data' => PaymentForm::getClassifierOptions(),
                            'allowClear' => false,
                            'placeholder' => false,
                            'options' => [
                                'id' => '_payment_form'
                            ],
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_doctorate_student_status')->widget(Select2Default::classname(), [
                            'data' => DoctorateStudentStatus::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [

                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">

                        <?= $form->field($model, '_level')->widget(Select2Default::classname(), [
                            'data' => EDoctorateStudent::getLevelStatusOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [

                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">

                        <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getDepartments(),
                            'allowClear' => true,
                            'placeholder' => false,
                            'options' => [
                                'id' => '_department'
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'accepted_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'id' => 'accepted_date',
                            ],
                           // 'disabled' => $disabled
                        ]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'dissertation_theme')->textArea(['maxlength' => true]) ?>
                    </div>
                </div>

            </div>
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Passport Information'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-10">
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, '_citizenship')->widget(Select2Default::classname(), [
                                    'data' => CitizenshipType::getClassifierOptions(),
                                    'allowClear' => false,
                                    'disabled' => $model->student_id_number != null,
                                    'options' => [
                                        'id' => '_citizenship',
                                    ],
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'passport_number')->textInput([
                                    'id' => 'passport_number',
                                    'readonly' => $model->student_id_number != null,
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                $btn = '<span class="input-group-btn"><button class="btn btn-default" onclick="getStudentInfo()" type="button"><i id="fa_search" class="fa fa-search"></i><i id="fa_spinner" style="display: none" class="fa fa-spinner fa-spin"></i> </button></span>';
                                ?>
                                <?php
                                $title = __('Bu qanday kod?');
                                $label = $model->getAttributeLabel('passport_pin');
                                $link = Url::current(['pin_hint' => 1]);
                                $pinBtn = "<span class='showModalButton hint' value='$link' title='$title'> $title</span>";
                                ?>
                                <?= $form->field($model, 'passport_pin', ['template' => "<label class='control-label' for='passport_pin'>$label </label>$pinBtn<div class='input-group'>{input}$btn</div>{error}"])->textInput([
                                    'id' => 'passport_pin',
                                    'readonly' => $model->student_id_number != null,
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
                                    'data' => Gender::getClassifierOptionsByName(),
                                    'allowClear' => false,
                                    'disabled' => $disabled,
                                    'options' => [
                                        'id' => '_gender',
                                    ],
                                ]) ?>
                            </div>

                            <div class="col-md-4">
                                <?= $form->field($model, '_nationality')->widget(Select2Default::classname(), [
                                    'data' => Nationality::getClassifierOptionsByName(),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                    'options' => [
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="col col-md-2">
                        <?= $form->field($model, 'image')
                            ->widget(Upload::className(), [
                                'url' => ['dashboard/file-upload', 'type' => 'profile'],
                                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'sortable' => true,
                                'maxFileSize' => 10 * 1024 * 1024, // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple' => false,
                                'clientOptions' => [],
                            ]); ?>
                    </div>
                </div>
            </div>
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Address Information'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_country')->widget(Select2Default::classname(), [
                            'data' => Country::getClassifierOptionsByName(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [
                            ],
                        ]) ?>

                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, '_province')->widget(Select2Default::classname(), [
                            'data' => Soato::getParentClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_province',
                            ],
                        ]) ?>
                    </div>
                    <?php
                    $regions = array();
                    if ($model->_province) {
                        $regions = Soato::getChildrenOption($model->_province);
                    }
                    ?>
                    <div class="col-md-3">
                        <?= $form->field($model, '_district')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($regions, 'code', 'name'),
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose-'),
                                'id' => '_district',
                            ],
                            'pluginOptions' => [
                                'depends' => ['_province'],
                                'placeholder' => __('-Choose-'),
                                'url' => Url::to(['/ajax/get-region'])
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'home_address')->textInput(['maxlength' => true, 'id' => 'home_address']) ?>
                    </div>

                </div>
                <?/*
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_social_category')->widget(Select2Default::classname(), [
                            'data' => SocialCategory::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'placeholder' => false,
                            'options' => [
                            ],
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, 'year_of_enter')->widget(Select2Default::classname(), [
                            'data' => \common\models\student\EStudent::getYearOfEnterOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [

                            ],
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, '_accommodation')->widget(Select2Default::classname(), [
                            'data' => Accommodation::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [

                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'current_address')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'other')->textArea(['maxlength' => true]) ?>
                    </div>
                </div>
                */?>
            </div>
            <div class="box-footer text-right">
                <?//php if (!$model->isNewRecord && $user->canAccessToResource('student/delete-from-database')): ?>
                    <?= $this->getResourceLink(__('Delete'), ['science/doctorate-student-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete']) ?>
                <?//php endif; ?>


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
        return $('#_citizenship').val() === '11' || $('#_citizenship').val() === '13';
    }

    function initCitizenship() {
        var id = $('#_citizenship').val();

        if (!$('#_citizenship').is(':disabled')) {
            if (id === '11' || id === '13') {
                if (id === '11') {
                    if (hemisIntegration) {
                        $("#first_name").attr('readonly', true);
                        $("#second_name").attr('readonly', true);
                        $("#third_name").attr('readonly', true);
                        $("#birth_date").attr('readonly', true);
                        $("#_gender").attr('readonly', true);
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

    function initStudentForm() {
        $('#_citizenship').change(function () {
            initCitizenship();
        });
        initCitizenship();
    }

    function getStudentInfo() {
        var pin = $('#passport_pin').val();
        var num = $('#passport_number').val();
        var citizenship = $('#_citizenship').val();
        if (num.length === 9 && pin.length === 14 && citizenship === '11' && hemisIntegration) {
            if (pin.search('_') == -1 && num.search('_') == -1) {
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