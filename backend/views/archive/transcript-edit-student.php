<?php

use backend\widgets\checkbo\CheckBo;
use common\components\Config;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\GradeType;
use common\models\performance\EStudentPttCurriculumSubject;
use common\models\performance\EStudentPttMeta;
use common\models\performance\EStudentPttSubject;
use common\models\performance\estudentrestore;
use common\models\archive\EAcademicInformation;
use common\models\archive\ETranscriptSubject;
use common\models\archive\EStudentTranscriptStudentSubject;
use common\models\archive\EStudentTranscriptAcademicRecord;
use common\models\archive\EAcademicRecord;
use backend\widgets\DatePickerDefault;
use common\models\student\EStudentRestoreMeta;
use common\models\system\classifier\DecreeType;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\student\EGroup;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\Course;
use yii2mod\editable\EditableColumn;
use common\models\archive\EStudentTranscriptCurriculumSubject;

/* @var $this \backend\components\View */
/* @var $model \common\models\archive\EAcademicInformation */
/* @var $restoreModel \common\models\archive\ETranscriptSubject */
/* @var $restoreMetaModel \common\models\archive\EStudentTranscriptMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$semester = -1;
$this->title = $model->student->getFullName();

$this->params['breadcrumbs'][] = ['url' => ['archive/transcript'], 'label' => __('Archive Transcript')];
if ($model->isNewRecord)
    $this->params['breadcrumbs'][] = ['url' => ['archive/transcript-edit'], 'label' => __('Archive Transcript Edit')];
$this->params['breadcrumbs'][] = $this->title;

$minBorder = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_THREE)->min_border, 0);
$maxBorder = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border, 0);

?>
<?php $disabled  = EAcademicInformation::ACADEMIC_INFORMATION_STATUS_GENERATED ? false : true; ?>


<?php $form = ActiveForm::begin(['enableAjaxValidation' => false,  'validateOnSubmit' => false, 'id' => 'transcript-form', 'options' => ['data-pjax' => 1, 'action' => Url::current()/*'onsubmit' => 'return validateAcademicRecords()'*/]]); ?>
<?php if($model->isNewRecord) echo $form->field($model, 'semester_id')->hiddenInput(['id'=>'semester_id'])->label(false);?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('Student information') ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-12">
                <?= $form->errorSummary($model, ['showAllErrors' => true]); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'student_name')->textInput(['disabled' => $disabled /*|| Config::getLanguageCode() !== Config::LANGUAGE_ENGLISH_CODE*/]) ?>
                <?= $form->field($model, 'student_birthday')->widget(
                    DatePickerDefault::classname(),
                    [
                        'options' => [
                            'disabled' => $disabled,
                            'placeholder' => __('Enter date'),
                        ],
                    ]
                ); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'student_status')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'group_name')->textInput(['disabled' => $disabled]) ?>
            </div>
        </div>
    </div>

</div>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('HEI information') ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-6">
                <?= $form->field($model, 'university_name')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'rector')->textInput(['disabled' => $disabled]) ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'faculty_name')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'dean')->textInput(['disabled' => $disabled]) ?>

            </div>
        </div>
    </div>

</div>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h3 class="box-title"><?= __('Education information') ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-6">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'education_type_name')->textInput(['disabled' => $disabled]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'education_form_name')->textInput(['disabled' => $disabled]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'year_of_entered')->textInput(['disabled' => $disabled]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'year_of_graduated')->textInput(['disabled' => $disabled]) ?>
                    </div>
                </div>



            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'specialty_code')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'specialty_name')->textInput(['disabled' => $disabled]) ?>

                <?//= $form->field($model, 'semester_name')->textInput(['disabled' => $disabled]) ?>

            </div>
        </div>
    </div>

</div>

<div class="box box-default ">
    <div class="box-header bg-gray ">
        <h4><?= __('Transcript Information') ?></h4>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-3">
                <?= $form->field($model, 'academic_register_number')->textInput() ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($model, 'academic_register_date')->widget(\backend\widgets\DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                    ],
                ]); ?>
            </div>

            <div class="col col-md-3">

                <?= $form->field($model, '_semester')->widget(Select2Default::classname(), [
                    'data' => $model->getSemestersByEducationYear(),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'disabled' => !$model->isNewRecord,
                    'options' => [
                        'onchange' => 'semesterChanged(this.value)'
                    ]
                ]) ?>
            </div>
        </div>
    </div>
</div>

<?php Pjax::begin(['id' => 'subjects-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-header bg-gray ">
                <h4><?= __('Fanlar ro`yxati') ?>
                    <span class="text-muted pull-right italic fs-16">* <?= $model->markingSystem->name ?></span>
                </h4>


            </div>
            <?php if ($model->isNewRecord): ?>
                <?php if ($model->_semester): ?>
                    <?= GridView::widget([
                        'layout' => "<div class='box-body no-padding'>{items}</div>",
                        'dataProvider' => $model->getRegisterSubjectsProvider(),
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'class' => \yii\grid\CheckboxColumn::class,
                                'name' => 'EAcademicInformation[subjectIds][]',
                                'checkboxOptions' => function (EStudentTranscriptAcademicRecord $item, $key, $index, $grid) use ($model) {
                                    return [
                                        //'disabled' => $item->getAcademicRecord($model->_student) ? true : false,
                                        'class' => 'ptt-items'
                                    ];
                                }
                            ],
                            [
                                'attribute' => '_subject',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->subject->name;
                                },
                            ],
                            [
                                'attribute' => '_semester',
                                'format' => 'raw',
                                'value' => function ($data)  {
                                    if(Semester::getByCurriculumSemester($data->_curriculum, $data->_semester) != null)
                                        return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                                    elseif($data->semester)
                                        return $data->semester->name;
                                    else
                                        return \common\models\system\classifier\Semester::findOne($data->_semester)->name;
                                },
                            ],
                            [
                                'attribute' => 'total_acload',
                                'format' => 'raw',
                                'header' => __('Total Acload'),
                                'value' => function ($data) {
                                    return $data->total_acload ? round($data->total_acload, 1) : '';
                                },
                                /*'value' => function (EStudentTranscriptAcademicRecord $item) use ($model) {
                                    $ac = $item->getAcademicRecordData($model->_student);
                                    return $ac ? round($ac->total_acload, 1) : '';
                                },*/
                            ],
                            [
                                'attribute' => 'credit',
                                'header' => __('Credit'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->credit ? round($data->credit, 1) : '';
                                },
                                /*'value' => function (EStudentTranscriptAcademicRecord $item) use ($model) {
                                    $ac = $item->getAcademicRecordData($model->_student);
                                    return $ac ? round($ac->credit, 1) : '';
                                },*/
                            ],
                            [
                                'header' => __('Mark'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->total_point ? round($data->total_point, 1) : '';
                                },
                                /*'value' => function (EStudentTranscriptAcademicRecord $item) use ($model) {
                                    $ac = $item->getAcademicRecordData($model->_student);
                                    return $ac ? round($ac->total_point, 1) : '';
                                },*/
                            ],
                            [
                                'header' => __('Grade'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->grade ? round($data->grade) : '';
                                },
                                /*'value' => function (EStudentTranscriptAcademicRecord $item) use ($model) {
                                    $ac = $item->getAcademicRecordData($model->_student);
                                    return $ac ? round($ac->grade) : '';
                                },*/
                            ],

                        ]
                    ]); ?>
                <?php else: ?>
                    <p class="empty">
                        <?= __("Semestrni tanlang") ?>
                    </p>
                <?php endif ?>
            <?php endif ?>

            <?php if (!$model->isNewRecord): ?>
                <?= GridView::widget([
                    'layout' => "<div class='box-body no-padding'>{items}</div>",
                    'dataProvider' => $model->getStudentTranscriptSubjectsProvider(),
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            //'header' => __('Subject'),
                            'attribute' => '_subject',
                            'format' => 'raw',
                            'value' => function (ETranscriptSubject $item) {
                                return $item->subject_name;
                            },
                        ],
                        [
                            'attribute' => '_semester',
                            'format' => 'raw',
                            'value' => function (ETranscriptSubject $item) {
                                return $item->semester_name;
                            },
                        ],
                        [
                            'header' => __('Total Acload'),
                            'attribute' => 'total_acload',
                            'format' => 'raw',
                            'value' => function (ETranscriptSubject $item) {
                                return $item->total_acload;
                            },
                        ],
                        [
                            'header' => __('Credit'),
                            'attribute' => 'credit',
                            'format' => 'raw',
                            'value' => function (ETranscriptSubject $item) {
                                return $item->credit;
                            },
                        ],
                        [
                            'header' => __('Mark'),
                            'attribute' => 'total_point',
                            'format' => 'raw',
                            'value' => function (ETranscriptSubject $item) {
                                return $item->total_point;
                            },
                        ],
                        [
                            'header' => __('Grade'),
                            'format' => 'raw',
                            'options' => [
                                'width' => 120,
                            ],
                            'value' => function (ETranscriptSubject $item) {
                                return $item->grade;
                            },
                        ],
                    ]
                ]); ?>

            <?php endif; ?>
            <?php if ($model->isNewRecord): ?>
                <div class="box-footer text-right">
                    <button type="submit" onclick="return validateSelectedSubjects()"
                            class="btn btn-primary btn-flat">
                        <i class="fa fa-check"></i> <?= __('Save') ?>
                    </button>
                </div>
            <?php endif ?>
            <?php if (!$model->isNewRecord): ?>
                <div class="box-footer text-right">
                    <?php if ($model->canBeDeleted()): ?>
                        <a class="btn btn-danger btn-flat btn-delete"
                           data-pjax="0"
                           href="<?= linkTo(['archive/transcript-edit', 'transcript' => $model->id, 'delete' => 1]) ?>">
                            <?= __('Delete') ?>
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-default btn-flat"
                       data-pjax="0"
                       href="<?= linkTo(['archive/transcript-edit', 'transcript' => $model->id, 'download' => 1]) ?>">
                        <i class="fa fa-download"></i> <?= __('Download') ?>
                    </a>
                    <?php if ($model->canBeUpdated()): ?>
                        <button type="submit"
                                class="btn btn-primary btn-flat">
                            &nbsp; &nbsp; &nbsp; <i class="fa fa-check"></i> <?= __('Save') ?> &nbsp; &nbsp; &nbsp;
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
<?php Pjax::end() ?>
<?php ActiveForm::end(); ?>

<?php
if (isset($meta))
        $link = linkTo(["transcript-edit", "student" => $meta->id]);
else
    $link = linkTo(["transcript-edit", "transcript" => $model->id]);
?>
<script>
    let minBorder = <?=$minBorder?>;
    let maxBorder = <?=$maxBorder?>;

    /*function validateAcademicRecords() {
        let isValid = true;
        $('.acr').each(function (index, element) {
            isValid = isValid && validateAcademicRecord(element);
        });

        return isValid;
    }*/

    /*function validateAcademicRecord(element) {
        if (element.value != '') {
            if (element.value < minBorder || element.value > maxBorder) {
                $(element).parent().addClass('has-error').removeClass('has-success');
                return false;
            } else {
                $(element).parent().addClass('has-success').removeClass('has-error');
            }
        }
        return true;
    }*/

    function validateSelectedSubjects() {
        let count = $('input.ptt-items:checked').length;
        if (count === 0) {
            alert(<?=json_encode([__('Please choose subjects!')])?>[0]);
        } else if (confirm(<?=json_encode([__('Create a transcript for the selected {count} subject?')])?>[0].replace('{count}', count))) {
            return true;
        }
        return false;
    }

    function semesterChanged(s) {
        $.pjax.reload('#subjects-grid', {'url': '<?=$link?>&semester=' + s})
        $('#semester_id').val(s) ;
    }
</script>
