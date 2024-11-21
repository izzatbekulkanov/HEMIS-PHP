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

    <div class="col col-md-2 col-lg-2" id="sidebar">
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

                <?/*= $form->field($searchModel, '_education_type')->widget(Select2Default::class, [
                    'data' => EducationType::getHighers(),
                    'options' => [
                        'id' => '_education_type_search',
                        'required' => true
                    ]
                ])->label(false) */?>
                <?= $form->field($searchModel, '_category')->radioList($searchModel->byStudentGeneral(),['class'=>'custom-control custom-radio custom-control-inline'])->label(false); ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-10 col-lg-10">
        <div class="box box-default ">
            <div class="box-body no-padding">
                <?php if($searchModel->_category): ?>
                    <br>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Year');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationYear::findOne($searchModel->_education_year)->name;?></b></p>
                        </div>
                        <div class="col col-md-6">
                            <p><i><?php echo __('	Semestr Type');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo SemestrType::findOne($searchModel->_semester_type)->name;?></b></p>
                        </div>
                    </div>

                    <br/>
                <?php endif; ?>

                <!-- nation-->
                <?php
                if($searchModel->_category == 11):
                    $form_level = array();
                    $faculty_form_level = array();
                    $faculty_form_level_all = 0;

                    if(isset($department_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;"><?php echo __('Faculty');?></th>
                                <?php foreach ($education_form_list as $form): ?>
                                    <th colspan="<?= count($level_list);?>" style="text-align: center; vertical-align: center"><?php echo $form->educationForm->name;?></th>
                                <?php endforeach;?>
                                <th rowspan="2" style="text-align: center; vertical-align: center"><?php echo __('All Student');?></th>
                            </tr>
                            <tr>
                                <?php foreach ($education_form_list as $form): ?>
                                <?php foreach ($level_list as $level): ?>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo @$level->level->name;?>
                                    </th>
                                <?php endforeach;?>
                                <?php endforeach;?>
                            </tr>
                            <?php foreach ($department_list as $item2): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php
                                            $dep = EDepartment::findOne($item2);
                                            echo $dep->name;
                                        ?>
                                    </td>
                                    <?php foreach ($education_form_list as $form): ?>
                                        <?php foreach ($level_list as $level): ?>
                                            <th style="text-align: center; vertical-align: center">
                                                <?php echo @$result[$dep->id][$form->_education_form][$level->_level];?>
                                                 <?php @$form_level[$form->_education_form][$level->_level] += @$result[$dep->id][$form->_education_form][$level->_level];?>
                                                 <?php @$faculty_form_level[$dep->id] += @$result[$dep->id][$form->_education_form][$level->_level];?>
                                            </th>
                                        <?php endforeach;?>
                                    <?php endforeach;?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$faculty_form_level[@$dep->id];?>
                                        <?php @$faculty_form_level_all += @$faculty_form_level[@$dep->id];?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary');?></th>
                                <?php foreach ($education_form_list as $form): ?>
                                    <?php foreach ($level_list as $level): ?>
                                        <th style="text-align: center; vertical-align: center">
                                            <?php echo @$form_level[$form->_education_form][$level->_level];?>
                                        </th>
                                    <?php endforeach;?>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo @$faculty_form_level_all;?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                    <?php endif; ?>
                <!-- nation end-->


                <!-- nation-->
                <?php
                if($searchModel->_category == 12):
                    $form_level = array();
                    $faculty_form_level = array();
                    $faculty_form_level_all = 0;
                    $s0 = 0;
                    if(isset($department_list)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th style="text-align: center; vertical-align: middle;"><?php echo __('Faculty');?></th>
                                <th style="text-align: center; vertical-align: middle;"><?php echo __('Social Category');?></th>
                                <th style="text-align: center; vertical-align: middle;"><?php echo __('	Count of Students');?></th>
                            </tr>
                            <?php foreach ($department_list as $item2): ?>
                                    <tr>
                                        <td rowspan="<?=count($social_list)+2?>" style="text-align: center; vertical-align: middle">
                                            <?php
                                                $dep = EDepartment::findOne($item2);
                                                echo $dep->name;
                                            ?>
                                        </td>
                                    </tr>

                                    <?php $s = 0; ?>
                                    <?php foreach ($social_list as $social): ?>
                                    <tr>
                                        <td style="text-align: left; vertical-align: center">
                                            <?php
                                                @$soc = \common\models\system\classifier\SocialCategory::findOne($social);
                                                echo @$soc->name;
                                            ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$dep->id][$social->code];?>
                                            <?php $s += @$result[$dep->id][$social->code];?>
                                            <?//php @$form_level[$form->_education_form][$level->_level] += @$result[$dep->id][$form->_education_form][$level->_level];?>
                                            <?//php @$faculty_form_level[$dep->id] += @$result[$dep->id][$form->_education_form][$level->_level];?>
                                        </td>
                                    </tr>

                                    <?php endforeach;?>

                                    <tr>
                                        <th>
                                            <?php
                                                echo __('Faculty Summary');
                                            ?>
                                        </th>
                                        <th style ="text-align: center;">
                                            <?php
                                                echo $s;
                                                $s0 += $s;
                                            ?>
                                        </th>

                                    </tr>

                            <?php endforeach; ?>
                            <tr>
                                <th></th>
                                <th><?php echo __('Summary');?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $s0;?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- nation end-->
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
