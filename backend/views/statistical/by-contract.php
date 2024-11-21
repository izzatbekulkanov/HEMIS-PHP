<?php

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\Course;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\system\AdminRole;

/**
 * @var $this \backend\components\View
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>

        <div class="box box-default ">

            <div class="box-body">
                <?= $form->field($searchModel, '_faculty')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'allowClear' => false,
                    'disabled' => $this->_user()->role->isDeanOrTutorRole(),
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

                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),

                    'options' => [
                        'id' => '_education_year_search',
                        'required' => true,
                    ]
                ])->label(false); ?>
                <?= $form->field($searchModel, '_semester_type')->widget(Select2Default::class, [
                    'data' => SemestrType::getClassifierOptions(),
                    'options' => [
                        'id' => '_semester_type_search',
                        'required' => true
                    ]
                ])->label(false); ?>

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
                <?php if ($searchModel->_education_type): ?>
                    <br>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Year'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationYear::findOne($searchModel->_education_year)->name; ?></b>
                            </p>
                        </div>
                        <div class="col col-md-6">
                            <p><i><?php echo __('Faculty'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EDepartment::findOne($searchModel->_faculty)->name; ?></b></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Education Type'); ?></i>:
                                &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationType::findOne($searchModel->_education_type)->name; ?></b>
                            </p>
                        </div>
                        <div class="col col-md-6">

                        </div>
                    </div>
                    <br/>

                    <!-- level-->
                    <?php
                    $c_all_student = $c_all_contract = [];
                    $summary_all_student = $summary_all_contract = 0;
                    if (isset($education_form_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2"
                                    style="text-align: center; vertical-align: middle;"><?php echo __('Level'); ?></th>
                                <?php foreach ($level_list as $level): ?>
                                    <th colspan="2" style="text-align: center; vertical-align: center">
                                        <?php echo Course::findOne($level)->name; ?>
                                    </th>
                                <?php endforeach; ?>

                                <th colspan="2"
                                    style="text-align: center; vertical-align: center"><?php echo __('All Student'); ?></th>
                            </tr>
                            <tr>
                                <?php foreach ($level_list as $level): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo __('Count of Contract Student'); ?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo __('Count Contracts'); ?></th>
                                <?php endforeach; ?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Count of Contract Student'); ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Count Contracts'); ?></th>
                            </tr>
                            <?php foreach ($education_form_list as $form): ?>
                                <?php $by_level_student = $by_level_contract = 0; ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?= EducationForm::findOne($form)->name; ?>

                                    </td>
                                    <?php foreach ($level_list as $level): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo $level_edu_student = @$result[$form][$level]; ?>
                                            <?php @$c_all_student[$level] += @$level_edu_student; ?>
                                            <?php @$by_level_student += @$level_edu_student; ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo $level_edu_contract = @$resultContract[$form][$level]; ?>
                                            <?php @$c_all_contract[$level] += @$level_edu_contract; ?>
                                            <?php @$by_level_contract += @$level_edu_contract; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td style="text-align: center; vertical-align: center">
                                        <?= $by_level_student; ?>
                                        <?php $summary_all_student += $by_level_student; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?= $by_level_contract; ?>
                                        <?php $summary_all_contract += $by_level_contract; ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary'); ?></th>
                                <?php foreach ($level_list as $level): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$c_all_student[$level]; ?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$c_all_contract[$level]; ?></th>
                                <?php endforeach; ?>
                                <th style="text-align: center; vertical-align: center"><?php echo $summary_all_student; ?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $summary_all_contract; ?></th>

                            </tr>

                        </table>
                    <?php endif; ?>
                    <!-- level end-->


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
