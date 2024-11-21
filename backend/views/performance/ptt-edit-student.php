<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\GradeType;
use common\models\performance\EStudentPttCurriculumSubject;
use common\models\performance\EStudentPttMeta;
use common\models\performance\EStudentPttSubject;
use common\models\performance\estudentrestore;
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

/* @var $this \backend\components\View */
/* @var $model \common\models\performance\EStudentPtt */
/* @var $restoreModel \common\models\performance\EStudentPttMeta */
/* @var $restoreMetaModel \common\models\performance\EStudentPttMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$semester = -1;
$this->title = $model->student->getFullName();

$this->params['breadcrumbs'][] = ['url' => ['performance/ptt'], 'label' => __('Performance Ptt')];
if ($model->isNewRecord)
    $this->params['breadcrumbs'][] = ['url' => ['performance/ptt-edit'], 'label' => __('Performance Ptt Edit')];
$this->params['breadcrumbs'][] = $this->title;

$minBorder = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_THREE)->min_border, 0);
$maxBorder = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border, 0);
?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <h4><?= __('Tanlangan talaba') ?></h4>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'layout' => "<div class='box-body no-padding'>{items}</div>",
        'dataProvider' => new \yii\data\ArrayDataProvider(['models' => [isset($meta) ? $meta : $model]]),
        'columns' => [
            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                },
            ],

            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_specialty',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->specialty->mainSpecialty->code, $data->curriculum->name);
                },
            ],
            [
                'attribute' => '_group',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->group->name, $data->department->name);
                },
            ],
        ],
    ]); ?>
</div>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'id' => 'ptt-form', 'options' => ['onsubmit' => 'return validateAcademicRecords()']]); ?>
<div class="box box-default ">
    <div class="box-header bg-gray ">
        <h4><?= __('Shaxsiy jadval ma\'lumotlari') ?></h4>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col col-md-3">
                <?= $form->field($model, 'number')->textInput() ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($model, 'date')->widget(\backend\widgets\DatePickerDefault::classname(), [
                    'options' => [
                        'placeholder' => __('YYYY-MM-DD'),
                    ],
                ]); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($model, '_decree')->widget(Select2Default::class, [
                    'data' => EDecree::getOptions($model->_department, DecreeType::TYPE_STUDENT_PTT),
                    'hideSearch' => false,
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
                <h4><?= __('Shaxsiy grafik fanlari') ?>
                    <span class="text-muted pull-right italic fs-16">* <?= $model->markingSystem->name ?></span>
                </h4>


            </div>
            <?php if ($model->isNewRecord): ?>
                <?php if ($model->_semester): ?>
                    <?= GridView::widget([
                        'layout' => "<div class='box-body no-padding'>{items}</div>",
                        'dataProvider' => $model->getCurriculumSemesterSubjectsProvider(),
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'class' => \yii\grid\CheckboxColumn::class,
                                'name' => 'EStudentPtt[subjectIds][]',
                                'checkboxOptions' => function (ECurriculumSubject $item, $key, $index, $grid) use ($model) {
                                    return [
                                        'disabled' => $item->getAcademicRecord($model->_student) ? true : false,
                                        'class' => 'ptt-items'
                                    ];
                                }
                            ],
                            [
                                'attribute' => '_subject',
                                'format' => 'raw',
                                'value' => function (ECurriculumSubject $item) {
                                    return $item->subject->name;
                                },
                            ],
                            [
                                'attribute' => '_semester',
                                'format' => 'raw',
                                'value' => function (ECurriculumSubject $item) {
                                    return $item->semester->name;
                                },
                            ],
                            [
                                'attribute' => '_subject_type',
                                'format' => 'raw',
                                'value' => function (ECurriculumSubject $item) {
                                    return @mb_substr($item->subjectType->name, 0, 1);
                                },
                            ],
                            [
                                'attribute' => 'total_acload',
                                'format' => 'raw',
                                'value' => function (ECurriculumSubject $item) {
                                    return $item->total_acload;
                                },
                            ],
                            [
                                'attribute' => 'credit',
                                'format' => 'raw',
                                'value' => function (ECurriculumSubject $item) {
                                    return $item->credit;
                                },
                            ],
                            [
                                'header' => __('Mark'),
                                'format' => 'raw',
                                'value' => function (EStudentPttCurriculumSubject $item) use ($model) {
                                    $ac = $item->getAcademicRecord($model->_student);
                                    return $ac ? round($ac->total_point, 1) : '';
                                },
                            ],
                            [
                                'header' => __('Grade'),
                                'format' => 'raw',
                                'value' => function (EStudentPttCurriculumSubject $item) use ($model) {
                                    $ac = $item->getAcademicRecord($model->_student);
                                    return $ac ? round($ac->grade) : '';
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
                    'dataProvider' => $model->getStudentPttSubjectsProvider(),
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'header' => __('Subject'),
                            'attribute' => '_subject',
                            'format' => 'raw',
                            'value' => function (EStudentPttSubject $item) {
                                return $item->curriculumSubject->subject->name;
                            },
                        ],
                        [
                            'attribute' => '_semester',
                            'format' => 'raw',
                            'value' => function (EStudentPttSubject $item) {
                                return $item->curriculumSubject->semester->name;
                            },
                        ],
                        [
                            'header' => __('Fan turi'),
                            'attribute' => '_subject_type',
                            'format' => 'raw',
                            'value' => function (EStudentPttSubject $item) {
                                return @mb_substr($item->curriculumSubject->subjectType->name, 0, 1);
                            },
                        ],
                        [
                            'header' => __('Total Acload'),
                            'attribute' => 'total_acload',
                            'format' => 'raw',
                            'value' => function (EStudentPttSubject $item) {
                                return $item->curriculumSubject->total_acload;
                            },
                        ],
                        [
                            'header' => __('Credit'),
                            'attribute' => 'credit',
                            'format' => 'raw',
                            'value' => function (EStudentPttSubject $item) {
                                return $item->curriculumSubject->credit;
                            },
                        ],
                        [
                            'header' => __('Mark'),
                            'format' => 'raw',
                            'options' => [
                                'width' => 120,
                            ],
                            'value' => function (EStudentPttSubject $item) use ($model, $minBorder, $maxBorder) {
                                $ac = $item->academicRecord;
                                return Html::input('number', "EStudentPtt[ar][total_point][{$item->id}]", $ac ? round($ac->total_point, 1) : '', [
                                    'class' => 'form-control acr',
                                    'data-total' => $item->id,
                                    'step' => 0.1,
                                    'min' => $minBorder,
                                    'max' => $maxBorder,
                                    'onblur' => 'validateAcademicRecord(this)'
                                ]);
                            },
                        ],
                        [
                            'header' => __('Grade'),
                            'format' => 'raw',
                            'options' => [
                                'width' => 120,
                            ],
                            'value' => function (EStudentPttSubject $item) use ($model) {
                                $ac = $item->academicRecord;
                                return Html::input('number', "EStudentPtt[ar][grade][{$item->id}]", $ac ? round($ac->grade) : '', ['class' => 'form-control', 'readonly' => true, 'data-grade' => $item->id]);
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
                           href="<?= linkTo(['performance/ptt-edit', 'ptt' => $model->id, 'delete' => 1]) ?>">
                            <?= __('Delete') ?>
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-default btn-flat"
                       data-pjax="0"
                       href="<?= linkTo(['performance/ptt-edit', 'ptt' => $model->id, 'download' => 1]) ?>">
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
    $link = linkTo(["ptt-edit", "student" => $meta->id]);
else
    $link = linkTo(["ptt-edit", "ptt" => $model->id]);
?>
<script>
    let minBorder = <?=$minBorder?>;
    let maxBorder = <?=$maxBorder?>;

    function validateAcademicRecords() {
        let isValid = true;
        $('.acr').each(function (index, element) {
            isValid = isValid && validateAcademicRecord(element);
        });

        return isValid;
    }

    function validateAcademicRecord(element) {
        if (element.value != '') {
            if (element.value < minBorder || element.value > maxBorder) {
                $(element).parent().addClass('has-error').removeClass('has-success');
                return false;
            } else {
                $(element).parent().addClass('has-success').removeClass('has-error');
            }
        }
        return true;
    }

    function validateSelectedSubjects() {
        let count = $('input.ptt-items:checked').length;
        if (count === 0) {
            alert(<?=json_encode([__('Iltimos, fanlarni tanlang!')])?>[0]);
        } else if (confirm(<?=json_encode([__('Tanlangan {count} ta fan uchun shaxsiy jadval yaratisizmi?')])?>[0].replace('{count}', count))) {
            return true;
        }
        return false;
    }

    function semesterChanged(s) {
        $.pjax.reload('#subjects-grid', {'url': '<?=$link?>&semester=' + s})
    }
</script>
