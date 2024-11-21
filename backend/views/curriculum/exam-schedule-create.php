<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeMeta;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\curriculum\EStudentSubject;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use kartik\date\DatePicker;
use backend\widgets\DatePickerDefault;
\kartik\date\DatePickerAsset::registerBundle($this, '3.x');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Exam Schedules'), 'url' => ['exam-schedule']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => true, 'options' => ['data-pjax' => true]]); ?>

<?php
    echo $form->errorSummary($model);
?>
<?php if (!$model->isNewRecord){
   // echo $model->week->fullName;
} ?>
<?php echo $form->field($model, '_curriculum')->hiddenInput(['value'=>$curriculum, 'id'=>'_curriculum'])->label(false);?>
<?php echo $form->field($model, '_education_year')->hiddenInput(['value'=>$education_year, 'id'=>'_education_year'])->label(false);?>
<?php echo $form->field($model, '_semester')->hiddenInput(['value'=>$semester, 'id'=>'_semester'])->label(false);?>
<?php echo $form->field($model, '_group')->hiddenInput(['value'=>$group, 'id'=>'_group'])->label(false);?>

<div class="row">
    <div class="col col-md-12" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <div class="row">
                            <div class="col col-md-6">
                                <?= $form->field($model, 'exam_date')->widget(DatePickerDefault::classname(), [
                                    'options' => [
                                        'placeholder' => __('YYYY-MM-DD'),
                                        'id' => 'exam_date',
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'daysOfWeekDisabled' => [0, 7],
                                        'weekStart' => '1',
                                    ]
                                ]); ?>

                                <?= $form->field($model, '_subject')->widget(Select2Default::classname(), [
                                    'data' => ArrayHelper::map($subjects, '_subject','subject.name'),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => '_subject',
                                    ],
                                ]) ?>

                                <?php
                                    $exams = array();
                                    if($model->_subject){
                                        $rating_grade =  ECurriculumSubject::getByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject)->_rating_grade;
                                        if($rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
                                            $exams = ArrayHelper::map(ECurriculumSubjectExamType::getExamTypeByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject), '_exam_type', 'examType.name');
                                        }
                                        else{
                                            $exams = ArrayHelper::map(ECurriculumSubjectExamType::getOtherExamTypeByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject), '_exam_type', 'examType.name');
                                        }
                                    }
                                ?>
                                <?= $form->field($model, '_exam_type')->widget(DepDrop::classname(), [
                                    'data' => $exams,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options'=>['pluginOptions'=>['allowClear'=>true], 'theme' => Select2::THEME_DEFAULT],
                                    'options' => [
                                        'id' => '_exam_type',
                                        'placeholder' => __('-Choose-'),
                                        'required' => true
                                    ],
                                    'pluginOptions' => [
                                        'depends'=>['_curriculum', '_semester', '_subject'],
                                        'url'=>Url::to(['/ajax/get-curriculum-subject-exam-type']),

                                    ],
                                ]);?>

                                <?/*= $form->field($model, '_exam_type')->widget(Select2Default::classname(), [
                                    'data' => ExamType::getClassifierOptions(),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => '_exam_type',
                                    ],
                                ]) */?>
                                <?// $display = ($model->_exam_type == ExamType::EXAM_TYPE_FINAL || $model->_exam_type == ExamType::EXAM_TYPE_OVERALL) ? "display:show" : "display:none;" ?>
                                <div id="final_exam_types" style="<?//= $display;?>">
                                    <?php
                                    $final_exam_types = array();
                                    if($model->_exam_type){
                                        $final_exam_types = FinalExamType::getFinalExamTypeOptions($model->curriculum->markingSystem->count_final_exams);
                                    }
                                    ?>
                                    <?= $form->field($model, 'final_exam_type')->widget(DepDrop::classname(), [
                                        'data' => $final_exam_types,
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'pluginLoading' => false,
                                        'select2Options'=>['pluginOptions'=>['allowClear'=>true], 'theme' => Select2::THEME_DEFAULT],
                                        'options' => [
                                            'id' => 'final_exam_type',
                                            'placeholder' => __('-Choose-'),
                                        ],
                                        'pluginOptions' => [
                                            'depends'=>['_curriculum', '_exam_type'],
                                            'url'=>Url::to(['/ajax/get-curriculum-subject-final-exam']),

                                        ],
                                ]);?>
                                </div>

                                <?//= $form->field($model, 'exam_name')->textInput(['placeholder'=>__('1-qaydnoma, 2-qaydnoma,... ')])->hint(__('1-qaydnoma, 2-qaydnoma,... ')); ?>

                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($model, '_lesson_pair')->widget(Select2Default::classname(), [
                                    'data' => ArrayHelper::map($pairs, 'code','fullName'),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                ]) ?>
                                <?= $form->field($model, '_auditorium')->widget(Select2Default::classname(), [
                                    'data' => ArrayHelper::map($auditoriums, 'code','name'),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                ]) ?>
                                <?php
                                $groups = array();
                                if($model->_subject){
                                    $groups = EStudentSubject::getGroupsByCurriculumSemesterSubject($curriculum, $semester, $model->_subject);
                                }
                                ?>
                                <?= $form->field($model, 'groups')->widget(DepDrop::classname(), [
                                    'data' =>  ArrayHelper::map($groups, '_group','group.name'),
                                    'language' => 'en',
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                                    'options' => [
                                        'placeholder' => __('-Choose-'),
                                        'id' => 'group',
                                        'multiple' => ($model->isNewRecord),
                                    ],
                                    'pluginOptions' => [
                                        'depends'=>['_curriculum', '_semester', '_subject', '_group'],
                                        'placeholder' => __('-Choose-'),
                                        'url'=>Url::to(['/ajax/get-curriculum-semester-subject-groups']),
                                    ],
                                ])?>


                                <?/*= $form->field($model, '_group')->widget(Select2Default::classname(), [
                                    'data' => ArrayHelper::map($groups, '_group','group.name'),
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                    'options' => [
                                        'multiple' => false,
                                    ],
                                ]) */?>

                                <?php
                                $employees = array();
                                if($model->_subject && $model->_exam_type){
                                    if($model->_exam_type != ExamType::EXAM_TYPE_FINAL && $model->_exam_type != ExamType::EXAM_TYPE_OVERALL) {
                                        $employees = ArrayHelper::map(ESubjectSchedule::getTeacherByCurriculumSemesterSubject($curriculum, $semester, $model->_subject), '_employee', 'employee.fullName');
                                    }
                                    else{
                                        $employees = EEmployeeMeta::getTeachers();
                                    }

                                }
                                ?>
                                <?= $form->field($model, '_employee')->widget(DepDrop::classname(), [
                                    'data' => $employees,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options'=>['pluginOptions'=>['allowClear'=>true], 'theme' => Select2::THEME_DEFAULT],
                                    'options' => [
                                        'id' => '_group_search',
                                        'placeholder' => __('-Choose-'),
                                        'required' => true
                                    ],

                                    'pluginOptions' => [
                                        'depends'=>['_curriculum', '_semester', '_subject', '_exam_type'],
                                        'url'=>Url::to(['/ajax/get-subject-teachers']),

                                    ],
                                ]);?>

                                <?/*= $form->field($model, '_employee')->widget(Select2Default::classname(), [
                                    'data' => $employees,
                                    'allowClear' => false,
                                    'hideSearch' => false,
                                ]) */?>
                            </div>
                        </div>





                    </div>

                </div>
            </div>
                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord): ?>
                        <?= Html::submitButton( __('Cancel'), ['class' => 'btn btn-default btn-flat', 'data-dismiss'=>'modal']) ?>
                        <?= $this->getResourceLink(__('Delete'), ['curriculum/exam-schedule-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat', 'data-confirm' => __('Are you sure to delete?'),'data-pjax' => '0',]) ?>
                    <?php endif; ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>

        </div>
    </div>

</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
<script>
    /*$('#_exam_type').change(function () {
        var id = $(this).val();
        if(id == 13 || id == 14){
            $('#final_exam_types').show();
            $("#final_exam_type").attr("required", true);
        }
        else{
            $('#final_exam_types').hide();
            $("#final_exam_type").attr("required", false);
        }
    })*/
</script>