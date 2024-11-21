<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;

use common\models\system\classifier\EducationForm;
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
use common\models\archive\EStudentEmployment;


//$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['url' => ['curriculum/subject'], 'label' => __('List Subject')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-3" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>

        <div class="box box-default ">

            <div class="box-body">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),

                    'options' => [
                        'id' => '_education_year_search',
                        'required' => true,
                    ]
                ])->label(false); ?>

                <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN) { ?>
                    <?= $form->field($searchModel, '_faculty')->widget(Select2Default::classname(), [
                        'data' => EDepartment::getFaculties(),
                        'options' => [
                            'id' => '_faculty_search',
                        ],
                    ])->label(false) ?>
                <?php } ?>

                <?php
                $specialties = array();
                if ($searchModel->_faculty) {
                    $specialties = ESpecialty::getHigherSpecialty($searchModel->_faculty);
                }

                ?>
                <?= $form->field($searchModel, '_specialty')->widget(DepDrop::classname(), [
                    'data' => $specialties,
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'id' => '_specialty',
                        'placeholder' => __('-Choose Specialty-'),
                    ],
                    'pluginOptions' => [
                        'depends' => ['_faculty_search'],
                        'url' => Url::to(['/ajax/get_specialty']),
                        'placeholder' => __('-Choose Specialty-'),
                    ],
                ])->label(false); ?>


                <?= $form->field($searchModel, '_education_form')->widget(Select2Default::class, [
                    'data' => EducationForm::getClassifierOptions(),
                    'options' => [
                        'id' => '_education_form_search',
                    ]
                ])->label(false) ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-9 col-lg-9">
        <div class="box box-default ">
            <div class="box-body no-padding">
                <?php if($searchModel->_education_year): ?>
                    <br>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Year');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationYear::findOne($searchModel->_education_year)->name;?></b></p>
                        </div>
                        <div class="col col-md-6">
                            <p><i><?php echo __('Faculty');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php if($faculty) echo EDepartment::findOne($faculty)->name;?></b></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p> <i><?php echo __('Education Form');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php if($searchModel->_education_form) echo EducationForm::findOne($searchModel->_education_form)->name;?></b></p>
                        </div>
                        <div class="col col-md-6">

                        </div>
                    </div>
                    <br/>
                    <?php
                        $form_level = array();
                        $faculty_form_level = array();
                        $faculty_form_level_all = 0;
                        $summary_all_employment = $summary_all_employment_female = 0;
                        $summary_all_inactive = $summary_all_inactive_female = 0;
                        $summary_all_inactive_second = $summary_all_inactive_female_second = 0;
                        $s1 = $s2 = $s3 = $s4 = $ss1 = $ss2 = $ss3 = $ss4 = [];
                        $s10 = $s20 = $s30 = $s40 =  [];
                        $st1 = $st2 = $st3 = $st4 = $sst1 = $sst2 = $sst3 = $sst4 = [];
                        $st12 = $st22 = $st32 = $st42 = $sst12 = $sst22 = $sst32 = $sst42 = [];
                        $sz1 = $sz2 = $sz3 = $sz4 = [];
                        $sz12 = $sz22 = $sz32 = $sz42 = [];
                        $sz13 = $sz23 = $sz33 = $sz43 = [];
                        $sz_all = $sz_female = 0;
                        $sz_all2 = $sz_female2 = 0;
                        $sz_all3 = $sz_female3 = 0;
                        if(isset($education_types)):?>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo __('#');?></th>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo __('Indicators');?></th>
                                <?php foreach ($education_types as $item): ?>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo $item->name;?></th>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo __('Including women');?></th>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo __('Grant');?></th>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo __('Contract Label');?></th>
                                <?php endforeach; ?>

                                    <th><?php echo __('Summary');?></th>
                                    <th style="text-align: center; vertical-align: middle;"><?php echo __('Including women');?></th>
                                </tr>
                                <? $i=1; ?>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">I</th>
                                    <th style="text-align: left; vertical-align: middle;" colspan="11">Iqtisodiyot sohalari bo‘yicha ishga joylashish, shu jumladan:</th>
                                </tr>
                                <?php foreach ($graduate_field_type_list as $field): ?>
                                    <?php $all_summary = $all_summary_female = 0;?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo $i++;?></td>
                                        <td style="text-align: left; vertical-align: middle;"><?php echo $field->name;?></td>
                                        <?php foreach ($education_types as $item): ?>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    $s1[$item->code] = @$result[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$field->code]['field_b'] + @$result[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$field->code]['field_c'];
                                                    @$ss1[$item->code] += @$s1[$item->code];
                                                    echo @$s1[$item->code];
                                                    @$all_summary += $s1[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    $s2[$item->code] = @$result[$item->code][Gender::GENDER_FEMALE][$field->code]['field_female'];
                                                    @$ss2[$item->code] += @$s2[$item->code];
                                                    echo @$s2[$item->code];
                                                    @$all_summary_female += $s2[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    $s3[$item->code] = @$result[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$field->code]['field_b'];
                                                    @$ss3[$item->code] += @$s3[$item->code];
                                                    echo @$s3[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    $s4[$item->code] = @$result[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$field->code]['field_c'];
                                                    @$ss4[$item->code] += @$s4[$item->code];
                                                    echo @$s4[$item->code];
                                                ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td style="text-align: center; vertical-align: middle;">
                                            <?= $all_summary;?>
                                            <?php $summary_all_employment += $all_summary;?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?= $all_summary_female;?>
                                            <?php $summary_all_employment_female += $all_summary_female;?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <? $i=1;

                                ?>
                                <?php foreach ($workplace_compatibility_list as $key=>$compatibility): ?>
                                    <?php $all_summary = $all_summary_female = 0;?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo $i++;?></td>
                                        <td style="text-align: left; vertical-align: middle;"><?php echo $compatibility;?></td>
                                        <?php foreach ($education_types as $item): ?>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    echo $s10[$item->code] =  @$result_work[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$key]['work_b'] + @$result_work[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$key]['work_c'];
                                                    @$all_summary += $s10[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    echo $s20[$item->code] = @$result_work[$item->code][Gender::GENDER_FEMALE][$key]['work_female'];
                                                    @$all_summary_female += $s20[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php echo @$result_work[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$key]['work_b'];?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php echo @$result_work[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$key]['work_c'];?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td style="text-align: center; vertical-align: middle;"><?= $all_summary;?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?= $all_summary_female;?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"></td>
                                    <th style="text-align: left; vertical-align: middle;">Jami</th>
                                    <?php foreach ($education_types as $item): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$ss1[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$ss2[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$ss3[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$ss4[$item->code]?>
                                        </td>

                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=$summary_all_employment;?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=$summary_all_employment_female;?>
                                    </td>
                                </tr>


                                <? $i=1; ?>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">II</th>
                                    <th style="text-align: left; vertical-align: middle;" colspan="11">Iqtisodiy faol bo‘lmagan bitiruvchilar, shu jumladan:</th>
                                </tr>
                                <?php foreach ($graduate_inactive_type_list_first as $inactive): ?>
                                    <?php $all_summary = $all_summary_female = 0;?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo $i++;?></td>
                                        <td style="text-align: left; vertical-align: middle;"><?php echo $inactive->name;?></td>
                                        <?php foreach ($education_types as $item): ?>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    @$st1[$item->code] = @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$inactive->code]['inactive_b'] + @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$inactive->code]['inactive_c'];
                                                    @$sst1[$item->code] += @$st1[$item->code];
                                                    echo @$st1[$item->code];
                                                    @$all_summary += $st1[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    @$st2[$item->code] = @$result_inactive[$item->code][Gender::GENDER_FEMALE][$inactive->code]['inactive_female'];
                                                    @$sst2[$item->code] += @$st2[$item->code];
                                                    echo @$st2[$item->code];
                                                    @$all_summary_female += $st2[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    @$st3[$item->code] = @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$inactive->code]['inactive_b'];
                                                    @$sst3[$item->code] += @$st3[$item->code];
                                                    echo @$st3[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                    @$st4[$item->code] = @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$inactive->code]['inactive_c'];
                                                    @$sst4[$item->code] += @$st4[$item->code];
                                                    echo @$st4[$item->code];
                                                ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td style="text-align: center; vertical-align: middle;">
                                            <?= $all_summary;?>
                                            <?php $summary_all_inactive += $all_summary;?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?= $all_summary_female;?>
                                            <?php $summary_all_inactive_female += $all_summary_female;?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"></td>
                                    <th style="text-align: left; vertical-align: middle;">Jami</th>
                                    <?php foreach ($education_types as $item): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst1[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst2[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst3[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst4[$item->code]?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=$summary_all_inactive;?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=$summary_all_inactive_female;?>
                                    </td>
                                </tr>

                                <? $i=1; ?>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">III</th>
                                    <th style="text-align: left; vertical-align: middle;" colspan="11">Ishga joylashish jarayonidagi bitiruvchilar, shu jumladan</th>
                                </tr>

                                <?php foreach ($graduate_inactive_type_list_second as $inactive): ?>
                                    <?php $all_summary = $all_summary_female = 0;?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo $i++;?></td>
                                        <td style="text-align: left; vertical-align: middle;"><?php echo $inactive->name;?></td>
                                        <?php foreach ($education_types as $item): ?>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                @$st12[$item->code] = @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$inactive->code]['inactive_b'] + @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$inactive->code]['inactive_c'];
                                                @$sst12[$item->code] += @$st12[$item->code];
                                                echo @$st12[$item->code];
                                                @$all_summary += $st12[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                @$st22[$item->code] = @$result_inactive[$item->code][Gender::GENDER_FEMALE][$inactive->code]['inactive_female'];
                                                @$sst22[$item->code] += @$st22[$item->code];
                                                echo @$st22[$item->code];
                                                @$all_summary_female += $st22[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                @$st32[$item->code] = @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_BUDGET][$inactive->code]['inactive_b'];
                                                @$sst32[$item->code] += @$st32[$item->code];
                                                echo @$st32[$item->code];
                                                ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <?php
                                                @$st42[$item->code] = @$result_inactive[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT][$inactive->code]['inactive_c'];
                                                @$sst42[$item->code] += @$st42[$item->code];
                                                echo @$st42[$item->code];
                                                ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td style="text-align: center; vertical-align: middle;">
                                            <?= $all_summary;?>
                                            <?php $summary_all_inactive_second += $all_summary;?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?= $all_summary_female;?>
                                            <?php $summary_all_inactive_female_second += $all_summary_female;?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"></td>
                                    <th style="text-align: left; vertical-align: middle;">Jami</th>
                                    <?php foreach ($education_types as $item): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst12[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst22[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst32[$item->code]?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php echo @$sst42[$item->code]?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=$summary_all_inactive_second;?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=$summary_all_inactive_female_second;?>
                                    </td>
                                </tr>


                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"></td>
                                    <th style="text-align: left; vertical-align: middle;"><?= __('All Summary')?></th>
                                    <?php foreach ($education_types as $item): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz1[$item->code] =  @$ss1[$item->code] + @$sst1[$item->code] + @$sst12[$item->code];
                                            echo @$sz1[$item->code];
                                            @$sz_all += $sz1[$item->code];
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz2[$item->code] = @$ss2[$item->code] + @$sst2[$item->code] + @$sst22[$item->code];
                                            echo @$sz2[$item->code];
                                            @$sz_female += $sz2[$item->code];
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz3[$item->code] = @$ss3[$item->code] + @$sst3[$item->code] + @$sst32[$item->code];
                                            echo @$sz3[$item->code];

                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz4[$item->code] = @$ss4[$item->code] + @$sst4[$item->code] + @$sst42[$item->code];
                                            echo @$sz4[$item->code];
                                            ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=@$sz_all;?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=@$sz_female;?>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"></td>
                                    <th style="text-align: left; vertical-align: middle;"><?= __('Count of foreign graduates')?></th>
                                    <?php foreach ($education_types as $item): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz13[$item->code] = @$result_citizen[$item->code][PaymentForm::PAYMENT_FORM_BUDGET]['b'] + @$result_citizen[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT]['c'];
                                            echo @$sz13[$item->code];
                                            @$sz_all3 += $sz13[$item->code];
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz23[$item->code] = @$result_citizen[$item->code][Gender::GENDER_FEMALE]['female'];
                                            echo @$sz23[$item->code];
                                            @$sz_female3 += $sz23[$item->code];
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz33[$item->code] = @$result_citizen[$item->code][PaymentForm::PAYMENT_FORM_BUDGET]['b'];
                                            echo @$sz33[$item->code];

                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                            @$sz43[$item->code] = @$result_citizen[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT]['c'];
                                            echo @$sz43[$item->code];
                                            ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=@$sz_all3;?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=@$sz_female3;?>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="text-align: center; vertical-align: middle;"></td>
                                    <th style="text-align: left; vertical-align: middle;"><?= __('Count of all graduates')?></th>
                                    <?php foreach ($education_types as $item): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                                @$sz12[$item->code] = @$result_student[$item->code][PaymentForm::PAYMENT_FORM_BUDGET]['b'] + @$result_student[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT]['c'];
                                                echo @$sz12[$item->code];
                                                @$sz_all2 += $sz12[$item->code];
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                                @$sz22[$item->code] = @$result_student[$item->code][Gender::GENDER_FEMALE]['female'];
                                                echo @$sz22[$item->code];
                                                @$sz_female2 += $sz22[$item->code];
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                                @$sz32[$item->code] = @$result_student[$item->code][PaymentForm::PAYMENT_FORM_BUDGET]['b'];
                                                echo @$sz32[$item->code];

                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <?php
                                                @$sz42[$item->code] = @$result_student[$item->code][PaymentForm::PAYMENT_FORM_CONTRACT]['c'];
                                                echo @$sz42[$item->code];
                                            ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=@$sz_all2;?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?=@$sz_female2;?>
                                    </td>
                                </tr>

                            </table>
                        <?php endif; ?>



                <?php endif; ?>
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
