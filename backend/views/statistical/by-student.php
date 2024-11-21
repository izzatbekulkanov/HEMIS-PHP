<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EStudentMeta;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\Gender;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\depdrop\DepDrop;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use common\models\system\AdminRole;

//$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['url' => ['curriculum/subject'], 'label' => __('List Subject')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>

        <div class="box box-default ">

            <div class="box-body">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),
                    'disabled' => true,
                    'readonly' => true,
                    'options' => [
                        'id' => '_education_year_search',
                        'required' => true,
                    ]
                ])->label(false); ?>
                <?= $form->field($searchModel, '_semester_type')->widget(Select2Default::class, [
                    'data' => SemestrType::getClassifierOptions(),
                    'disabled' => true,
                    'readonly' => true,
                    'options' => [
                        'id' => '_semester_type_search',
                        'required' => true
                    ]
                ])->label(false); ?>

                <?= $form->field($searchModel, '_faculty')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'disabled' => $this->_user()->role->isDeanOrTutorRole(),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_faculty_search',
                        'required' => true
                    ],
                ])->label(false) ?>

                <?= $form->field($searchModel, '_education_type')->widget(Select2Default::class, [
                    'data' => EducationType::getHighers(),
                    'options' => [
                        'id' => '_education_type_search',
                        'required' => true
                    ]
                ])->label(false) ?>
                <?= $form->field($searchModel, '_education_form')->widget(Select2Default::class, [
                    'data' => EducationForm::getClassifierOptions(),
                    'options' => [
                        'id' => '_education_form_search',
                        'required' => true
                    ]
                ])->label(false); ?>
                <?= $form->field($searchModel, '_category')->radioList($searchModel->byStudent(), ['class' => 'custom-control custom-radio custom-control-inline'])->label(false); ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name' => 'btn']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-body no-padding">
                <?php if ($searchModel->_category): ?>
                    <br>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Year'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationYear::findOne($searchModel->_education_year)->name; ?></b>
                            </p>
                        </div>
                        <div class="col col-md-6">
                            <p><i><?php echo __('Faculty'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EDepartment::findOne($searchModel->_faculty)->name; ?></b>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Education Type'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationType::findOne($searchModel->_education_type)->name; ?></b>
                            </p>
                        </div>
                        <div class="col col-md-6">
                            <p><i><?php echo __('Education Form'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationForm::findOne($searchModel->_education_form)->name; ?></b>
                            </p>
                        </div>
                    </div>
                    <br/>
                <?php endif; ?>
                <!-- level-->
                <?php
                if ($searchModel->_category == 11):
                    $b_male = $b_female = $b_all = $c_male = $c_female = $c_all = $bc_male = $bc_female = $bc_all = 0;
                    if (isset($level_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2"
                                    style="text-align: center; vertical-align: middle;"><?php echo __('Level'); ?></th>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('Budget Student'); ?></th>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('Contract Student'); ?></th>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                            </tr>
                            <tr>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('All Student'); ?></th>
                            </tr>
                            <?php foreach ($level_list as $item): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo @$item->level->name; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE]; ?>
                                        <?php $b_male += @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE]; ?>
                                        <?php $b_female += @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE]; ?>
                                        <?php $b_all = $b_male + $b_female; ?>
                                    </td>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                        <?php $c_male += @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $c_female += @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $c_all = $c_male + $c_female; ?>
                                    </td>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                        <?php $bc_male += @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $bc_female += @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE] + @$result[$item->_level][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $bc_all = $bc_male + $bc_female; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $b_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $b_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $b_all; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $c_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $c_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $c_all; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all; ?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- level end-->

                <!-- specialty-->
                <?php
                if ($searchModel->_category == 12):
                    $b_male = $b_female = $b_all = $c_male = $c_female = $c_all = $bc_male = $bc_female = $bc_all = 0;
                    if (isset($specialty_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2"
                                    style="text-align: center; vertical-align: middle;"><?php echo __('Specialty'); ?></th>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('Budget Student'); ?></th>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('Contract Student'); ?></th>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                            </tr>
                            <tr>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('All Student'); ?></th>
                            </tr>
                            <?php foreach ($specialty_list as $item): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo \common\models\student\ESpecialty::getSpecialtyName($item, $searchModel->_faculty)->name; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE]; ?>
                                        <?php $b_male += @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE]; ?>
                                        <?php $b_female += @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE]; ?>
                                        <?php $b_all = $b_male + $b_female; ?>
                                    </td>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                        <?php $c_male += @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $c_female += @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $c_all = $c_male + $c_female; ?>
                                    </td>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                        <?php $bc_male += @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $bc_female += @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_MALE] + @$result[$item][PaymentForm::PAYMENT_FORM_BUDGET][Gender::GENDER_FEMALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_MALE] + @$result[$item][PaymentForm::PAYMENT_FORM_CONTRACT][Gender::GENDER_FEMALE]; ?>
                                        <?php $bc_all = $bc_male + $bc_female; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $b_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $b_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $b_all; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $c_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $c_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $c_all; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all; ?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>


                <!-- specialty end-->

                <!-- nation-->
                <?php
                if ($searchModel->_category == 13):
                    $b_male = array();
                    $b_female = array();
                    $b_all = array();
                    $c_male = array();
                    $c_female = array();
                    $c_all = array();
                    $bc_male = $bc_female = $bc_all = 0;
                    if (isset($nation_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2"
                                    style="text-align: center; vertical-align: middle;"><?php echo __('Nationality'); ?></th>
                                <?php foreach ($level_list as $item): ?>
                                    <th colspan="3"
                                        style="text-align: center; vertical-align: center"><?php echo @$item->level->name; ?></th>
                                <?php endforeach; ?>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                            </tr>
                            <tr>
                                <?php foreach ($level_list as $item): ?>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Male'); ?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Female'); ?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('All Student'); ?>
                                    </th>
                                <?php endforeach; ?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('All Student'); ?></th>
                            </tr>
                            <?php foreach ($nation_list as $item2): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo \common\models\system\classifier\Nationality::findOne($item2)->name; ?>
                                    </td>
                                    <?php foreach ($level_list as $item): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2][$item->_level][Gender::GENDER_MALE]; ?>
                                            <?php @$b_male[$item->_level] += @$result[$item2][$item->_level][Gender::GENDER_MALE]; ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                            <?php @$b_female[$item->_level] += @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2][$item->_level][Gender::GENDER_MALE] + @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                            <?php @$b_all[$item->_level] += @$result[$item2][$item->_level][Gender::GENDER_MALE] + @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                        </td>
                                        <?php
                                        @$c_male [$item2] += @$result[$item2][$item->_level][Gender::GENDER_MALE];
                                        @$c_female [$item2] += @$result[$item2][$item->_level][Gender::GENDER_FEMALE];
                                        @$c_all [$item2] += @$result[$item2][$item->_level][Gender::GENDER_MALE] + @$result[$item2][$item->_level][Gender::GENDER_FEMALE];
                                        ?>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo $c_male [$item2]; ?>
                                        <?php $bc_male += $c_male [$item2]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo $c_female[$item2]; ?>
                                        <?php $bc_female += $c_female[$item2]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo $c_all[$item2]; ?>
                                        <?php $bc_all += $c_all[$item2]; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary'); ?></th>
                                <?php foreach ($level_list as $item): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_male[$item->_level]; ?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_female[$item->_level]; ?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_all[$item->_level]; ?></th>
                                <?php endforeach; ?>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all; ?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- nation end-->

                <!-- province-->
                <?php
                if ($searchModel->_category == 14):
                    $b_male = array();
                    $b_female = array();
                    $b_all = array();
                    $c_male = array();
                    $c_female = array();
                    $c_all = array();
                    $bc_male = $bc_female = $bc_all = 0;
                    if (isset($province_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2"
                                    style="text-align: center; vertical-align: middle;"><?php echo __('Province'); ?></th>
                                <?php foreach ($level_list as $item): ?>
                                    <th colspan="3"
                                        style="text-align: center;vertical-align: center"><?php echo @$item->level->name; ?></th>
                                <?php endforeach; ?>
                                <th colspan="3"
                                    style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                            </tr>
                            <tr>
                                <?php foreach ($level_list as $item): ?>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Male'); ?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Female'); ?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('All Student'); ?>
                                    </th>
                                <?php endforeach; ?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female'); ?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('All Student'); ?></th>
                            </tr>
                            <?php foreach ($province_list as $item2): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo \common\models\system\classifier\Soato::findOne($item2)->name; ?>
                                    </td>
                                    <?php foreach ($level_list as $item): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2][$item->_level][Gender::GENDER_MALE]; ?>
                                            <?php @$b_male[$item->_level] += @$result[$item2][$item->_level][Gender::GENDER_MALE]; ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                            <?php @$b_female[$item->_level] += @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2][$item->_level][Gender::GENDER_MALE] + @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                            <?php @$b_all[$item->_level] += @$result[$item2][$item->_level][Gender::GENDER_MALE] + @$result[$item2][$item->_level][Gender::GENDER_FEMALE]; ?>
                                        </td>
                                        <?php
                                        @$c_male [$item2] += @$result[$item2][$item->_level][Gender::GENDER_MALE];
                                        @$c_female [$item2] += @$result[$item2][$item->_level][Gender::GENDER_FEMALE];
                                        @$c_all [$item2] += @$result[$item2][$item->_level][Gender::GENDER_MALE] + @$result[$item2][$item->_level][Gender::GENDER_FEMALE];
                                        ?>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo $c_male [$item2]; ?>
                                        <?php $bc_male += $c_male [$item2]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo $c_female[$item2]; ?>
                                        <?php $bc_female += $c_female[$item2]; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo $c_all[$item2]; ?>
                                        <?php $bc_all += $c_all[$item2]; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary'); ?></th>
                                <?php foreach ($level_list as $item): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_male[$item->_level]; ?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_female[$item->_level]; ?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_all[$item->_level]; ?></th>
                                <?php endforeach; ?>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all; ?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- province end-->


            </div>
        </div>
    </div>

</div>

<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
