<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\student\EStudentMeta;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\SubjectGroup;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\depdrop\DepDrop;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\system\AdminRole;
use yii2mod\chosen\ChosenSelect;

//$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['url' => ['curriculum/subject'], 'label' => __('List Subject')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-12" id="sidebar">
        <div class="box box-default ">

            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::class, [
                            'data' => ECurriculum::getOptions($faculty),
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_curriculum_search',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),

                            'options' => [
                                'id' => '_education_year_search',
                                'required' => true,
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?php
                        $semesters = array();
                        if ($searchModel->_curriculum && $searchModel->_education_year) {
                            $semesters = Semester::getByCurriculumYear($searchModel->_curriculum, $searchModel->_education_year);
                        }
                        ?>
                        <?= $form->field($searchModel, '_semester')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($semesters, 'code', 'name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_semester_search',
                                'placeholder' => __('-Choose Semester-'),
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search'],
                                'url' => Url::to(['/ajax/get-semester-years']),
                                'required' => true
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?php
                        $groups = array();
                        if ($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semester) {
                            $groups = EStudentMeta::getContingentByCurriculumSemester($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semester);
                        }
                        ?>
                        <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($groups, '_group', 'group.name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_group_search',
                                'placeholder' => __('-Choose Group-'),
                                'required' => true
                            ],

                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search', '_semester_search'],
                                'url' => Url::to(['/ajax/get-group-semesters']),
                                'required' => true
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <div class="form-group">
                            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name' => 'btn']) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?php if (isset($students)) : ?>
                    <div class="table-responsive">

                        <table class="table table-bordered">
                            <tr>
                                <th rowspan="2" style="text-align:center; vertical-align:middle;"><?= __('№'); ?></th>
                                <th rowspan="2"
                                    style="text-align:center; vertical-align:middle;"><?= __('Fullname of Student'); ?></th>
                                <?php foreach ($curriculum_subjects as $item) { ?>
                                    <th colspan="2" style="text-align:center; vertical-align:middle;">
                                        <?= $item->subject->name; ?>
                                    </th>
                                <?php } ?>
                                <th colspan="2"
                                    style="text-align:center; vertical-align:middle;"><?= __('Summary'); ?></th>
                            </tr>
                            <tr>
                                <?php foreach ($curriculum_subjects as $item) { ?>
                                    <th style="text-align: center">
                                        <?= __('S'); ?>
                                    </th>
                                    <th style="text-align: center">
                                        <?= __('SZ'); ?>
                                    </th>
                                <?php } ?>
                                <th style="text-align: center">
                                    <?= __('S'); ?>
                                </th>
                                <th style="text-align: center">
                                    <?= __('SZ'); ?>
                                </th>
                            </tr>
                            <?php
                            $i = 1;

                            foreach ($students as $item) {
                                ?>
                                <?php
                                $s1 = 0;
                                $sz1 = 0;
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo @$item->student->fullName; ?></td>
                                    <?php foreach ($curriculum_subjects as $subject) { ?>
                                        <td style="text-align: center">
                                            <?php
                                            if (isset($absent_on[$subject->_subject][$item->_student]) && $absent_on[$subject->_subject][$item->_student] != 0) {
                                                echo $absent_on[$subject->_subject][$item->_student];
                                                $s1 += $absent_on[$subject->_subject][$item->_student];
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center">
                                            <?php
                                            if (isset($absent_off[$subject->_subject][$item->_student]) && $absent_off[$subject->_subject][$item->_student] != 0) {
                                                echo $absent_off[$subject->_subject][$item->_student];
                                                $sz1 += $absent_off[$subject->_subject][$item->_student];
                                            }
                                            ?>
                                        </td>
                                    <?php } ?>
                                    <td style="text-align: center">
                                        <?= $s1; ?>
                                    </td>
                                    <td style="text-align: center">
                                        <?= $sz1; ?>
                                    </td>

                                </tr>
                            <?php } ?>

                        </table>

                    </div>
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
