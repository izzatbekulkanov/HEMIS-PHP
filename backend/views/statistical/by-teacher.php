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
                <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN) { ?>
                    <?= $form->field($searchModel, '_faculty')->widget(Select2Default::class, [
                        'data' => EDepartment::getFaculties(),
                        'options' => [
                            'id' => '_faculty_search',
                            'required' => true
                        ]
                    ])->label(false) ?>
                <?php } ?>

                <?= $form->field($searchModel, '_category')->radioList($searchModel->byTeacher(),['class'=>'custom-control custom-radio custom-control-inline'])->label(false); ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-body no-padding" style="overflow: auto;">
                <?php if($searchModel->_category): ?>
                    <br>
                    <div class="col-md-12">
                        <div class="col col-md-6">
                            <p><i><?php echo __('Faculty');?>:</i>: &nbsp;&nbsp;&nbsp;&nbsp; <b><?php echo EDepartment::findOne($faculty)->name;?></b></p>
                        </div>
                    </div>
                    <br/>
                <?php endif; ?>


                <!-- degree-->
                <?php
                if($searchModel->_category == 11):
                    $b_male = array();
                    $b_female = array();
                    $b_all = array();
                    $c_male = array();
                    $c_female = array();
                    $c_all = array();
                    $bc_male = $bc_female = $bc_all = 0;


                    if(isset($departments)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;"><?php echo __('Academic Degree');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th colspan="3" style="text-align: center; vertical-align: center"><?php echo $item->name;?></th>
                                <?php endforeach;?>
                                <th colspan="3" style="text-align: center; vertical-align: center"><?php echo __('Summary');?></th>
                            </tr>
                            <tr>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Male');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Female');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Summary');?>
                                    </th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male');?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female');?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('Summary');?></th>
                            </tr>
                            <?php foreach ($departments as $item2): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo $item2->name;?>
                                    </td>
                                    <?php foreach ($degrees as $item): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                            <?php @$b_male[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_female[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE]+@$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_all[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <?php
                                        @$c_male [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];
                                        @$c_female [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        @$c_all [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        ?>
                                    <?php endforeach;?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_male[$item2->id];?>
                                        <?php $bc_male += @$c_male[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_female[$item2->id];?>
                                        <?php $bc_female += @$c_female[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_all[$item2->id];?>
                                        <?php $bc_all += @$c_all[$item2->id];?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_male[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_female[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_all[$item->code];?></th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all;?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- degree end-->


                <!-- rank-->
                <?php
                if($searchModel->_category == 12):
                    $b_male = array();
                    $b_female = array();
                    $b_all = array();
                    $c_male = array();
                    $c_female = array();
                    $c_all = array();
                    $bc_male = $bc_female = $bc_all = 0;


                    if(isset($departments)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;"><?php echo __('Academic Rank');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th colspan="3" style="text-align: center; vertical-align: center"><?php echo $item->name;?></th>
                                <?php endforeach;?>
                                <th colspan="3" style="text-align: center; vertical-align: center"><?php echo __('Summary');?></th>
                            </tr>
                            <tr>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Male');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Female');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Summary');?>
                                    </th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male');?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female');?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('Summary');?></th>
                            </tr>
                            <?php foreach ($departments as $item2): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo $item2->name;?>
                                    </td>
                                    <?php foreach ($degrees as $item): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                            <?php @$b_male[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_female[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE]+@$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_all[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <?php
                                        @$c_male [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];
                                        @$c_female [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        @$c_all [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        ?>
                                    <?php endforeach;?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_male[$item2->id];?>
                                        <?php $bc_male += @$c_male[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_female[$item2->id];?>
                                        <?php $bc_female += @$c_female[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_all[$item2->id];?>
                                        <?php $bc_all += @$c_all[$item2->id];?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_male[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_female[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_all[$item->code];?></th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all;?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- rank end-->


                <!-- position-->
                <?php
                if($searchModel->_category == 13):
                    $b_male = array();
                    $b_female = array();
                    $b_all = array();
                    $c_male = array();
                    $c_female = array();
                    $c_all = array();
                    $bc_male = $bc_female = $bc_all = 0;


                    if(isset($departments)):?>

                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;"><?php echo __('Position Name');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th colspan="3" style="text-align: center; vertical-align: center"><?php echo $item->name;?></th>
                                <?php endforeach;?>
                                <th colspan="3" style="text-align: center; vertical-align: center"><?php echo __('Summary');?></th>
                            </tr>
                            <tr>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="vertical-align: center">
                                        <?php echo __('Male');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Female');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Summary');?>
                                    </th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male');?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female');?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('Summary');?></th>
                            </tr>
                            <?php foreach ($departments as $item2): ?>
                                <tr>
                                    <td style="vertical-align: center">
                                        <?php echo $item2->name;?>
                                    </td>
                                    <?php foreach ($degrees as $item): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                            <?php @$b_male[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_female[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE]+@$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_all[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <?php
                                        @$c_male [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];
                                        @$c_female [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        @$c_all [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        ?>
                                    <?php endforeach;?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_male[$item2->id];?>
                                        <?php $bc_male += @$c_male[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_female[$item2->id];?>
                                        <?php $bc_female += @$c_female[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_all[$item2->id];?>
                                        <?php $bc_all += @$c_all[$item2->id];?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_male[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_female[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_all[$item->code];?></th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all;?></th>
                            </tr>

                        </table>

                    <?php endif; ?>
                <?php endif; ?>
                <!-- position end-->


                <!-- employment form-->
                <?php
                if($searchModel->_category == 15):
                    $b_male = array();
                    $b_female = array();
                    $b_all = array();
                    $c_male = array();
                    $c_female = array();
                    $c_all = array();
                    $bc_male = $bc_female = $bc_all = 0;


                    if(isset($departments)):?>
                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2" style="text-align: center; vertical-align: middle;"><?php echo __('Employment Form');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th colspan="3" style="text-align: center; vertical-align: center"><?php echo $item->name;?></th>
                                <?php endforeach;?>
                                <th colspan="3" style="text-align: center; vertical-align: center"><?php echo __('Summary');?></th>
                            </tr>
                            <tr>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Male');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Female');?>
                                    </th>
                                    <th style="text-align: center; vertical-align: center">
                                        <?php echo __('Summary');?>
                                    </th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Male');?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo __('Female');?></th>
                                <th style="text-align: center; vertical-align: middle"><?php echo __('Summary');?></th>
                            </tr>
                            <?php foreach ($departments as $item2): ?>
                                <tr>
                                    <td style=" vertical-align: center">
                                        <?php echo $item2->name;?>
                                    </td>
                                    <?php foreach ($degrees as $item): ?>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                            <?php @$b_male[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_female[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <td style="text-align: center; vertical-align: center">
                                            <?php echo @$result[$item2->id][$item->code][Gender::GENDER_MALE]+@$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                            <?php @$b_all[$item->code] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];?>
                                        </td>
                                        <?php
                                        @$c_male [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE];
                                        @$c_female [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        @$c_all [$item2->id] += @$result[$item2->id][$item->code][Gender::GENDER_MALE] + @$result[$item2->id][$item->code][Gender::GENDER_FEMALE];
                                        ?>
                                    <?php endforeach;?>

                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_male[$item2->id];?>
                                        <?php $bc_male += @$c_male[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_female[$item2->id];?>
                                        <?php $bc_female += @$c_female[$item2->id];?>
                                    </td>
                                    <td style="text-align: center; vertical-align: center">
                                        <?php echo @$c_all[$item2->id];?>
                                        <?php $bc_all += @$c_all[$item2->id];?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php echo __('Summary');?></th>
                                <?php foreach ($degrees as $item): ?>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_male[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_female[$item->code];?></th>
                                    <th style="text-align: center; vertical-align: center"><?php echo @$b_all[$item->code];?></th>
                                <?php endforeach;?>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_male;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_female;?></th>
                                <th style="text-align: center; vertical-align: center"><?php echo $bc_all;?></th>
                            </tr>

                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- employment form end-->


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
