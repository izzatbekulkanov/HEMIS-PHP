<?php

use backend\widgets\checkbo\CheckBo;
use common\components\Config;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\archive\EStudentAcademicInformationDataAcademicRecord;
use common\models\archive\EAcademicInformation;
use common\models\archive\EAcademicInformationDataSubject;
use backend\widgets\DatePickerDefault;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\ExpelReason;
use common\models\curriculum\Semester;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use yii2mod\editable\EditableColumn;

/* @var $this \backend\components\View */
/* @var $model \common\models\archive\EAcademicInformationData */
/* @var $restoreModel \common\models\archive\EAcademicInformationDataSubject */
/* @var $restoreMetaModel \common\models\archive\EStudentAcademicInformationDataMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$semester = -1;
$this->title = $model->student->getFullName();

$this->params['breadcrumbs'][] = ['url' => ['archive/academic-information-data'], 'label' => __('Archive Academic Information')];
if ($model->isNewRecord)
    $this->params['breadcrumbs'][] = ['url' => ['archive/academic-information-data-edit'], 'label' => __('Archive Academic Information data edit')];
$this->params['breadcrumbs'][] = $this->title;


?>
<?php $disabled  = EAcademicInformation::ACADEMIC_INFORMATION_STATUS_GENERATED ? false : true; ?>


<?php $form = ActiveForm::begin(['enableAjaxValidation' => false,  /*'validateOnSubmit' => false, */'id' => 'academic-information-form', 'options' => ['data-pjax' => 1, 'action' => Url::current()/*'onsubmit' => 'return validateAcademicRecords()'*/]]); ?>
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
            <div class="col col-md-4">
                <?= $form->field($model, 'second_name')->textInput(['disabled' => $disabled]) ?>
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
            <div class="col col-md-4">
                <?= $form->field($model, 'first_name')->textInput(['disabled' => $disabled]) ?>

                <?= $form->field($model, 'group_name')->textInput(['disabled' => $disabled]) ?>

            </div>

            <div class="col col-md-4">
                <?= $form->field($model, 'third_name')->textInput(['disabled' => $disabled]) ?>
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
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'continue_start_date')->widget(
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
                        <?= $form->field($model, 'continue_end_date')->widget(
                            DatePickerDefault::classname(),
                            [
                                'options' => [
                                    'disabled' => $disabled,
                                    'placeholder' => __('Enter date'),
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
                <?= $form->field($model, 'rector_fullname')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'secretary_fullname')->textInput(['disabled' => $disabled]) ?>

                <?= $form->field($model, 'education_form_name')->textInput(['disabled' => $disabled]) ?>




            </div>
            <div class="col col-md-6">
                <?= $form->field($model, 'faculty_name')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'dean_fullname')->textInput(['disabled' => $disabled]) ?>
                <?= $form->field($model, 'moved_hei_name')->textInput(['disabled' => $disabled]) ?>
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'studied_start_date')->widget(
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
                        <?= $form->field($model, 'studied_end_date')->widget(
                            DatePickerDefault::classname(),
                            [
                                'options' => [
                                    'disabled' => $disabled,
                                    'placeholder' => __('Enter date'),
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
                <?= $form->field($model, 'education_form_name_moved')->textInput(['disabled' => $disabled]) ?>


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
                    <div class="col col-md-4">
                        <?= $form->field($model, 'specialty_code')->textInput(['disabled' => $disabled]) ?>
                    </div>
                    <div class="col col-md-8">
                        <?= $form->field($model, 'specialty_name')->textInput(['disabled' => $disabled]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-6">
                        <?php $types = [DecreeType::TYPE_EXPEL, DecreeType::TYPE_ACADEMIC_LEAVE]; ?>
                        <?= $form->field($model, '_decree')->widget(Select2Default::class, [
                            'data' => EDecree::getOptions($model->_department, $types),
                            'hideSearch' => false,
                        ]); ?>
                    </div>
                    <div class="col col-md-6">
                        <?php if($model->isNewRecord): ?>
                        <?= $form->field($model, 'expulsion_decree_reason')->widget(Select2Default::class, [
                            'data' => ExpelReason::getClassifierOptions(),
                            'hideSearch' => false,
                        ]); ?>
                        <?php else:?>
                            <?= $form->field($model, 'expulsion_decree_reason')->textInput(['disabled' => $disabled]) ?>
                        <?php endif;?>
                    </div>
                </div>



            </div>

            <div class="col col-md-6">
                <?= $form->field($model, 'last_education')->textInput(['disabled' => $disabled]) ?>
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'accumulated_points')->textInput(['disabled' => $disabled]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'passing_score')->textInput(['disabled' => $disabled]) ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<div class="box box-default ">
    <div class="box-header bg-gray ">
        <h4><?= __('Academic Information Data') ?></h4>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-3">
                <?= $form->field($model, 'given_city')->textInput() ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($model, 'blank_number')->textInput() ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($model, 'register_number')->textInput() ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($model, 'register_date')->widget(\backend\widgets\DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                    ],
                ]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col col-md-9">
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
                    <span class="text-muted pull-right italic fs-16">* <?= $model->isNewRecord ? $meta->curriculum->markingSystem->name : $model->studentMeta->curriculum->markingSystem->name; ?></span>
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
                                'name' => 'EAcademicInformationData[subjectIds][]',
                                'checkboxOptions' => function (EStudentAcademicInformationDataAcademicRecord $item, $key, $index, $grid) use ($model) {
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
                            ],
                            [
                                'attribute' => 'credit',
                                'header' => __('Credit'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->credit ? round($data->credit, 1) : '';
                                },
                            ],
                            [
                                'header' => __('Mark'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->total_point ? round($data->total_point, 1) : '';
                                },
                            ],
                            [
                                'header' => __('Grade'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->grade ? round($data->grade) : '';
                                },
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
                    'dataProvider' => $model->getStudentAcademicInformationDataSubjectsProvider(),
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            //'header' => __('Subject'),
                            'attribute' => '_subject',
                            'format' => 'raw',
                            'value' => function (EAcademicInformationDataSubject $item) {
                                return $item->subject_name;
                            },
                        ],
                        [
                            'attribute' => '_semester',
                            'format' => 'raw',
                            'value' => function (EAcademicInformationDataSubject $item) {
                                return $item->semester_name;
                            },
                        ],
                        [
                            'header' => __('Total Acload'),
                            'attribute' => 'total_acload',
                            'format' => 'raw',
                            'value' => function (EAcademicInformationDataSubject $item) {
                                return $item->total_acload;
                            },
                        ],
                        [
                            'header' => __('Credit'),
                            'attribute' => 'credit',
                            'format' => 'raw',
                            'value' => function (EAcademicInformationDataSubject $item) {
                                return $item->credit;
                            },
                        ],
                        [
                            'header' => __('Mark'),
                            'attribute' => 'total_point',
                            'format' => 'raw',
                            'value' => function (EAcademicInformationDataSubject $item) {
                                return $item->total_point;
                            },
                        ],
                        [
                            'header' => __('Grade'),
                            'format' => 'raw',
                            'options' => [
                                'width' => 120,
                            ],
                            'value' => function (EAcademicInformationDataSubject $item) {
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
                           href="<?= linkTo(['archive/academic-information-data-edit', 'information' => $model->id, 'delete' => 1]) ?>">
                            <?= __('Delete') ?>
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-default btn-flat"
                       data-pjax="0"
                       href="<?= linkTo(['archive/academic-information-data-edit', 'information' => $model->id, 'download' => 1]) ?>">
                        <i class="fa fa-download"></i> <?= __('Download') ?>
                    </a>
                    <?//php if ($model->canBeUpdated()): ?>
                        <button type="submit"
                                class="btn btn-primary btn-flat">
                            &nbsp; &nbsp; &nbsp; <i class="fa fa-check"></i> <?= __('Save') ?> &nbsp; &nbsp; &nbsp;
                        </button>
                    <?//php endif; ?>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
<?php Pjax::end() ?>
<?php ActiveForm::end(); ?>

<?php
if (isset($meta))
    $link = linkTo(["academic-information-data-edit", "student" => $meta->id]);
else
    $link = linkTo(["academic-information-data-edit", "information" => $model->id]);
?>
<script>


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
        } else if (confirm(<?=json_encode([__('Create a academic information for the selected {count} subject?')])?>[0].replace('{count}', count))) {
            return true;
        }
        return false;
    }

    function semesterChanged(s) {
        $.pjax.reload('#subjects-grid', {'url': '<?=$link?>&semester=' + s})
        $('#semester_id').val(s) ;
    }
</script>
