<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\academic\EDecree;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\Nationality;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Country;
use common\models\system\classifier\Soato;
use common\models\system\classifier\Gender;
use common\models\system\classifier\Course;
use common\models\system\classifier\Accommodation;
use common\models\system\classifier\SocialCategory;
use common\models\system\classifier\StudentType;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\curriculum\Semester;
use common\models\curriculum\ECurriculum;
use common\models\structure\EDepartment;
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

$this->title = $model->isNewRecord ? __('Create Student') : @$model->fullName . " ({$model->student_id_number})";
$this->params['breadcrumbs'][] = ['url' => [$ctg ? 'student/student-contingent' : 'student/student'], 'label' => __('Student')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initStudentForm()");
\yii\widgets\MaskedInputAsset::register($this);
$disabled = $model->student_id_number != null && in_array($model->_citizenship, [CitizenshipType::CITIZENSHIP_TYPE_UZB, CitizenshipType::CITIZENSHIP_TYPE_NOTCITIZENSHIP]);
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'id' => 'student_form', 'options' => ['data' => ['pjax' => false]]]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Education Info'); ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($contingent, '_department')->widget(Select2Default::classname(), [
                                    'data' => EDepartment::getFaculties(),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                    'disabled' => $faculty != null,
                                    'options' => [
                                        'id' => '_department',

                                    ],
                                ]) ?>
                            </div>
                            <div class="col-md-8">
                                <?php
                                $specialties = array();
                                if ($contingent->_department) {
                                    $specialties = ESpecialty::getHigherSpecialty($contingent->_department);
                                }
                                if ($faculty) {
                                    $specialties = ESpecialty::getHigherSpecialty($faculty);
                                }
                                ?>
                                <?= $form->field($contingent, '_specialty_id')->widget(DepDrop::classname(), [
                                    'data' => $specialties,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'disabled' => (!$contingent->isNewRecord && $contingent->_student_status != StudentStatus::STUDENT_TYPE_APPLIED && $contingent->_level != Course::COURSE_FIRST),
                                    'pluginLoading' => false,
                                    'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                                    'options' => [
                                        'id' => '_specialty',
                                        'placeholder' => __('-Choose Specialty-'),
                                    ],
                                    'pluginOptions' => [
                                        'depends' => ['_department'],
                                        'url' => Url::to(['/ajax/get_specialty']),
                                        'placeholder' => __('-Choose Specialty-'),
                                    ],
                                ]); ?>
                            </div>

                            <div class="col-md-4">
                                <?= $form->field($model, 'year_of_enter')->widget(Select2Default::classname(), [
                                    'data' => \common\models\student\EStudent::getYearOfEnterOptions(),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($contingent, '_payment_form')->widget(Select2Default::classname(), [
                                    'data' => PaymentForm::getClassifierOptions(),
                                    'allowClear' => false,
                                    'placeholder' => false,
                                    'options' => [
                                        'id' => '_payment_form'
                                    ],
                                ]) ?>
                            </div>
                            <div class="col col-md-4">
                                <?= $form->field($model, '_decree_enroll')->widget(Select2Default::class, [
                                    'data' => EDecree::getOptions(null, DecreeType::TYPE_STUDENT_ENROLL),
                                    'hideSearch' => false,
                                ]); ?>
                            </div>

                        </div>
                        <div class="row">
                            <?php if ($contingent->_student_status == StudentStatus::STUDENT_TYPE_STUDIED): ?>
                                <div class="col-md-3">
                                    <?php
                                    $groups = array();
                                    if ($contingent->_specialty_id) {
                                        $groups = EGroup::getOptionsByFaculty($contingent->_department, $contingent->_specialty_id);
                                    }
                                    ?>
                                    <?= $form->field($contingent, '_group')->widget(DepDrop::classname(), [
                                        'data' => $groups,
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'pluginLoading' => false,
                                        'disabled' => true,
                                        'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                        'options' => [
                                            'allowClear' => false,
                                            'id' => '_group',
                                            'placeholder' => __('-Choose Group-'),
                                        ],
                                        'pluginOptions' => [
                                            'depends' => ['_department', '_specialty'],
                                            'url' => Url::to(['/ajax/get-group-by-specialty']),
                                            'placeholder' => __('-Choose Group-'),
                                        ],
                                    ]); ?>

                                    <? /*= $form->field($contingent, '_group')->widget(Select2Default::classname(), [
                                'data' => $groups,
                                'allowClear' => false,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_group'
                                ],
                            ]) */ ?>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $semesters = array();
                                    if ($contingent->_group) {
                                        $group = EGroup::findOne($contingent->_group);
                                        $semesters = Semester::getSemesterByCurriculum($group->_curriculum);
                                    }
                                    ?>
                                    <?= $form->field($contingent, '_semestr')->widget(DepDrop::classname(), [
                                        'data' => ArrayHelper::map($semesters, 'code', 'name'),
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'disabled' => true,
                                        'pluginLoading' => false,
                                        'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                        'options' => [
                                            'id' => '_semestr',
                                            'placeholder' => __('-Choose Semester-'),
                                        ],
                                        'pluginOptions' => [
                                            'depends' => ['_group'],
                                            'url' => Url::to(['/ajax/get-semester-by-group']),
                                            'placeholder' => __('-Choose Semester-'),
                                        ],
                                    ]); ?>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $levels = array();
                                    if ($contingent->_curriculum) {
                                        $curriculum = ECurriculum::findOne($contingent->_curriculum);
                                        $first_level = Course::COURSE_FIRST;
                                        $education_period = $curriculum->education_period;
                                        $i = 0;
                                        while ($i < (int)$education_period) {
                                            $i++;
                                            $levels [$first_level] = Course::findOne($first_level)->name;
                                            $first_level++;
                                        }
                                        //$groups = EGroup::getOptionsByFaculty($contingent->_department, $contingent->_specialty_id);
                                    }
                                    ?>
                                    <?= $form->field($contingent, '_level')->widget(Select2Default::classname(), [
                                        'data' => $levels,
                                        'allowClear' => false,
                                        'disabled' => true,
                                        'options' => [
                                            'id' => '_level'
                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $educationYears = array();
                                    if ($contingent->_group) {
                                        $group = EGroup::findOne($contingent->_group);
                                        $educationYears = Semester::getSemesterByCurriculum($group->_curriculum);
                                    }
                                    ?>
                                    <?= $form->field($contingent, '_education_year')->widget(DepDrop::classname(), [
                                        'data' => ArrayHelper::map($educationYears, '_education_year', 'educationYear.name'),
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'pluginLoading' => false,
                                        'disabled' => true,
                                        'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                                        'options' => [
                                            'id' => '_education_year',
                                            'placeholder' => __('-Choose Education Year-'),
                                        ],
                                        'pluginOptions' => [
                                            'depends' => ['_group'],
                                            'url' => Url::to(['/ajax/get-education-year-by-semestr']),
                                            'placeholder' => __('-Choose Education Year-'),
                                        ],
                                    ]); ?>

                                    <? /*= $form->field($contingent, '_education_year')->widget(Select2Default::classname(), [
                                        //'data' => EducationYear::getEducationYears(),
                                        'data' => ArrayHelper::map(Semester::getSemesterByCurriculum($contingent->_curriculum), '_education_year', 'educationYear.name'),
                                        'allowClear' => false,
                                        'hideSearch' => false,
                                    ]) */ ?>
                                </div>
                            <?php endif; ?>
                        </div>
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
                                    'readonly' => $model->passport_number != null,
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
                                $pinBtn = "<span class='showModalButton hint' value='$link' title='$title'>$title</span>";
                                ?>
                                <?= $form->field($model, 'passport_pin', ['template' => "<label class='control-label' for='passport_pin'>$label </label>$pinBtn<div class='input-group'>{input}$btn</div>{error}"])->textInput([
                                    'id' => 'passport_pin',
                                    'readonly' => $model->passport_pin != null,
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
                                        'readonly' => true,
                                    ],
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
                                <?= $form->field($model, '_nationality')->widget(Select2Default::classname(), [
                                    'data' => Nationality::getClassifierOptions(),
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
                                'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple' => false,
                                'accept' => 'image/*',
                                'clientOptions' => [
                                ],
                            ]); ?>
                        <?= $this->getResourceLink(
                            __('Edit'),
                            ['student/student-passport-edit', 'id' => $model->id],
                            ['class' => 'underline', 'data-pjax' => 0]
                        ) ?>
                    </div>
                </div>
            </div>
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __("Qo'shimcha ma'lumotlar"); ?></h3>
            </div>
            <div class="box-body">
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
                        <?= $form->field($model, '_student_type')->widget(Select2Default::classname(), [
                            'data' => StudentType::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'placeholder' => false,
                            'options' => [
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'other')->textInput(['maxlength' => true, 'placeholder' => __('Other info placeholder')]) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $adr = boolval(@$_COOKIE['adr']);
        $cur = boolval(@$_COOKIE['cur']);
        ?>
        <div class="box box-default <?= $adr ? 'collapsed-box' : '' ?>">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Address Information'); ?></h3>
                <div class="box-tools pull-right">
                    <button onclick="return savePanelState('adr')"
                            type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa <?= $adr ? 'fa-plus' : 'fa-minus' ?>"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_country')->widget(Select2Default::classname(), [
                            'data' => Country::getClassifierOptions(),
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
            </div>
        </div>
        <div class="box box-default <?= $cur ? 'collapsed-box' : '' ?>">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Current Address Information'); ?></h3>
                <div class="box-tools pull-right">
                    <button onclick="savePanelState('cur')"
                            type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa <?= $cur ? 'fa-plus' : 'fa-minus' ?>"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, '_accommodation')->widget(Select2Default::classname(), [
                            'data' => Accommodation::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, '_current_province')->widget(Select2Default::classname(), [
                            'data' => Soato::getParentClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_current_province',
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, '_current_district')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($model->_current_province ? Soato::getChildrenOption($model->_current_province) : [], 'code', 'name'),
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'select2Options' => ['pluginOptions' => ['allowClear' => false], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose-'),
                                'id' => '_current_district',
                            ],
                            'pluginOptions' => [
                                'depends' => ['_current_province'],
                                'placeholder' => __('-Choose-'),
                                'url' => Url::to(['/ajax/get-region'])
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'current_address')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'roommate_count')->textInput(['maxlength' => true]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, '_student_roommate_type')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\StudentRoommateType::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, '_student_living_status')->widget(Select2Default::classname(), [
                            'data' => \common\models\system\classifier\StudentLivingStatus::getClassifierOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'geo_location')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'phone')->widget(MaskedInputDefault::className(), [
                            'mask' => '|+|9|98 99 999-99-99',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'parent_phone')->widget(MaskedInputDefault::className(), [
                            'mask' => '|+|9|98 99 999-99-99',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'person_phone')->widget(MaskedInputDefault::className(), [
                            'mask' => '|+|9|98 99 999-99-99',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="box box-default">
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord && $user->canAccessToResource('student/delete-from-database')): ?>
                    <?= $this->getResourceLink(__('Delete'),
                        [
                            'student/student-edit',
                            'id' => $model->id,
                            'delete' => 1
                        ],
                        [
                            'class' => 'btn btn-danger btn-flat',
                            'onclick' => 'return deleteItem()',
                        ]) ?>


                <?php endif; ?>
                <?= $this->getResourceLink(__('Tozalash'), ['student/student-edit'], ['class' => 'btn btn-default btn-flat']) ?>

                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat', 'id' => 'submitButton', 'onclicks' => 'return submitStudentForm()']) ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<div id="studentModal" class="fade modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>
<script>
    function savePanelState(name) {
        Cookies.set(name, Cookies.get(name) === '1' ? '0' : '1');
        return true;
    }

    var hemisIntegration = <?=HEMIS_INTEGRATION ? 'true' : 'false'?>;
    var infoErrors = 0;

    function deleteItem() {
        if (confirm(<?=json_encode([__('Are you sure you want to delete this student?')])?>)) {
            return true;
        } else {
            return false;
        }
    }

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

        $('#student_form').on('beforeSubmit', function (e) {
            if ($('#student_form .has-error').length) {
                return false;
            }
            $('#submitButton').attr("disabled", true);
            return true;
        });
        $('#myCollapsible').on('hidden.bs.collapse', function () {
            console.log(1)
        })
    }

    function getStudentInfo() {
        var pin = $('#passport_pin').val();
        var num = $('#passport_number').val();
        var citizenship = $('#_citizenship').val();
        //if (num.length === 9 && pin.length === 14 && citizenship === '11' && hemisIntegration) {
        if (pin.search('_') == -1 && num.search('_') == -1) {
            $('#fa_search').hide();
            $('#fa_spinner').show();
            $.ajax({
                url: '<?=Url::current(['info' => 1]);?>',
                type: "GET",
                data: {passport: num, pin: pin, citizenship: citizenship},
                dataType: "json",
                success: function (data) {
                    $('#fa_search').show();
                    $('#fa_spinner').hide();

                    if (data.studentData.success == false) {
                        alert(data.studentData.error);

                        if (data.studentData.code == 100) {
                            $('#submitButton').attr('disabled', true);
                        }
                    } else {
                        $('#submitButton').attr('disabled', false);
                    }

                    if (data.students > 0) {
                        $('#studentModal').modal('show')
                            .find('#modalContent')
                            .html(data.content);
                    }

                    if (citizenship == '11') {
                        data = data.hemis;

                        if (data.success) {
                            $("#first_name").val(data.first_name);
                            $("#second_name").val(data.second_name);
                            $("#third_name").val(data.third_name);
                            $('#birth_date').val(data.birth_date);
                            $("#_gender").val(data.gender);
                            $('#_gender').trigger('change');
                            $("#passport_number").attr('readonly', true);
                            $("#passport_pin").attr('readonly', true);
                        } else {
                            infoErrors++;
                            if (data.manual || infoErrors > 3) {
                                $("#first_name").attr('readonly', false);
                                $("#second_name").attr('readonly', false);
                                $("#third_name").attr('readonly', false);
                                $("#birth_date").attr('readonly', false);
                                $("#_gender").attr('readonly', false);
                            }
                            if (data.error)
                                alert(data.error);
                        }
                    }

                }
            });
        }
        //}
    }
</script>