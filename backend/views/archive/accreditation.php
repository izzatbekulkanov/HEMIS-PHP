<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\RatingGrade;
use common\models\student\EStudentMeta;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationForm;
use common\models\student\ESpecialty;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;

/**
 * @var $this \backend\components\View
 * @var $searchModel EStudentMeta
 */
$this->params['breadcrumbs'][] = $this->title;

$disabled = false;
if ($this->_user()->role->code === \common\models\system\AdminRole::CODE_DEAN) {
    $disabled = true;
}
if ($faculty != "") {
    $searchModel->_department = $faculty;
}
?>
<?php Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php if ($this->_user()->role->code !== "teacher") { ?>
                    <div class="row" id="data-grid-filters">

                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_department')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => EDepartment::getFaculties(),
                                    'allowClear' => true,
                                    'placeholder' => __('-Choose Faculty-'),
                                    'options' => [
                                        'id' => '_faculty',
                                    ],
                                    'disabled' => $disabled
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <? //= $form->field($searchModel, '_department')->hiddenInput(['value' => $faculty, 'id' => '_faculty'])->label(false); ?>

                            <?= $form->field($searchModel, '_education_type')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => EducationType::getHighers(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => '_education_type',
                                    ]
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $specialties = [];
                            if ($searchModel->_department || $searchModel->_education_type) {
                                $specialties = ESpecialty::getHigherSpecialtyByType(
                                    $searchModel->_education_type,
                                    $searchModel->_department
                                );
                            }
                            ?>
                            <?= $form->field($searchModel, '_specialty_id')->widget(
                                DepDrop::classname(),
                                [
                                    'data' => $specialties,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options' => [
                                        'pluginOptions' => ['allowClear' => true],
                                        'theme' => Select2::THEME_DEFAULT
                                    ],
                                    'options' => [
                                        'id' => '_specialty',
                                        'placeholder' => __('-Choose Specialty-'),
                                    ],
                                    'pluginOptions' => [
                                        'depends' => ['_faculty', '_education_type'],
                                        'url' => Url::to(['/ajax/get-specialties-by-faculty']),
                                        'placeholder' => __('-Choose Specialty-'),
                                    ],
                                ]
                            )->label(false); ?>
                        </div>


                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_education_form')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => EducationForm::getClassifierOptions(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => '_education_form',
                                    ]
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $curriculums = [];
                            if ($searchModel->_department && ($searchModel->_education_type && $searchModel->_specialty_id && $searchModel->_education_form)) {
                                $curriculums = ECurriculum::getOptionsByEduTypeFormSpec(
                                    $searchModel->_education_type,
                                    $searchModel->_education_form,
                                    $searchModel->_department,
                                    $searchModel->_specialty_id
                                );
                            }
                            ?>
                            <?= $form->field($searchModel, '_curriculum')->widget(
                                DepDrop::classname(),
                                [
                                    'data' => $curriculums,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options' => [
                                        'pluginOptions' => ['allowClear' => true],
                                        'theme' => Select2::THEME_DEFAULT
                                    ],
                                    'options' => [
                                        'id' => '_curriculum',
                                        'placeholder' => __('-Choose Curriculum-'),
                                    ],
                                    'pluginOptions' => [
                                        'depends' => ['_faculty', '_education_type', '_specialty', '_education_form'],
                                        'url' => Url::to(['/ajax/get-curriculum-by-specialty']),
                                        'placeholder' => __('-Choose Curriculum-'),
                                    ],
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?php
                            $groups = array();
                            if ($searchModel->_curriculum) {
                                $groups = EGroup::getOptions($searchModel->_curriculum);
                            }
                            ?>
                            <?= $form->field($searchModel, '_group')->widget(
                                DepDrop::classname(),
                                [
                                    'data' => $groups,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options' => [
                                        'pluginOptions' => ['allowClear' => true],
                                        'theme' => Select2::THEME_DEFAULT
                                    ],
                                    'options' => [
                                        'id' => '_group',
                                        'placeholder' => __('-Choose Group-'),
                                        'required' => true
                                    ],
                                    'pluginOptions' => [
                                        'depends' => ['_curriculum'],
                                        'url' => Url::to(['/ajax/get-group-by-curruculum']),
                                        'required' => true
                                    ],
                                ]
                            )->label(false); ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                <?php } ?>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'sticky' => '#sidebar',
                        'toggleAttribute' => 'accreditation_accepted',
                        'toggleDisable' => function ($studentMeta) {
                            $countSubjects = EStudentMeta::getStudentSubjects($studentMeta, false, true);
                            $countStudied = EStudentMeta::getMarkedSubjects($studentMeta, true);
                            return $countSubjects !== $countStudied;
                        },
                        'toggleHeader' => __('Accept'),
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                            ],
                            [
                                'attribute' => '_student',
                                // 'enableSorting' => true,
                                'format' => 'raw',
                                'value' => static function ($data) {
                                    return Html::a(
                                        $data->student->fullName,
                                        ['archive/accreditation-view', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                },
                            ],
                            /*[
                                'attribute' => '_student',
                                'value' => 'student.fullName',
                            ],*/
                            [
                                'attribute' => '_specialty_id',
                                'value' => 'specialty.name',
                            ],
                            [
                                'attribute' => '_education_type',
                                'value' => 'educationType.name',
                            ],
                            [
                                'attribute' => '_education_form',
                                'value' => 'educationForm.name',
                            ],
                            [
                                'attribute' => '_curriculum',
                                'value' => 'curriculum.name',
                            ],

                            [
                                'label' => __('Total acload'),
                                'format' => 'raw',
                                'value' => static function (EStudentMeta $studentMeta) {
                                    $total = '-';
                                    if ($studentMeta->student !== null) {
                                        $studentSubjects = EStudentMeta::getStudentSubjects($studentMeta);
                                        $total = sprintf(
                                            '%s / %s',
                                            array_reduce(
                                                $studentSubjects->andFilterWhere(['in', '_rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]])->all(),
                                                function ($acc, $item) use ($studentMeta) {
                                                    $r = $item->getStudentSubjectRecord($studentMeta->_student);
                                                    return $acc + ($r ? $r->total_acload: 0);
                                                },
                                                0
                                            ),
                                            array_reduce(
                                                $studentSubjects->andFilterWhere(['in', '_rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]])->all(),
                                                function ($acc, $item) use ($studentMeta) {
                                                    $r = $item->getStudentSubjectRecord($studentMeta->_student);
                                                    return $acc + ($r ? $r->credit: 0);
                                                },
                                                0
                                            )
                                        );
                                        return $total;
                                    }

                                    return $total;
                                },
                            ],
                            [
                                'label' => __('Curriculum subjects count'),
                                'format' => 'raw',
                                'value' => static function (EStudentMeta $studentMeta) {
                                    $link = '-';
                                    if ($studentMeta->student !== null) {
                                        $studentSubjects = EStudentMeta::getStudentSubjects($studentMeta, false, true);
                                        $link = Html::a(
                                            $studentSubjects,
                                            '#',
                                            [
                                                'class' => 'showModalButton ',
                                                'modal-class' => 'modal-small',
                                                'title' => $studentMeta->student->getFullName(),
                                                'value' => Url::to(
                                                    [
                                                        'archive/accreditation',
                                                        'code' => $studentMeta->id,
                                                        'plan' => 1
                                                    ]
                                                ),
                                                'data-pjax' => 0
                                            ]
                                        );
                                        return $link;
                                    }

                                    return $link;
                                },
                            ],
                            [
                                'label' => __('subjects count'),
                                'format' => 'raw',
                                'value' => static function (EStudentMeta $studentMeta) {
                                    $link = '-';
                                    if ($studentMeta->student->academicRecords !== null) {
                                        $studentMarkedSubjects = EStudentMeta::getMarkedSubjects($studentMeta, true);
                                        $link = Html::a(
                                            $studentMarkedSubjects,
                                            '#',
                                            [
                                                'class' => 'showModalButton ',
                                                'modal-class' => 'modal-small',
                                                'title' => $studentMeta->student->getFullName(),
                                                'value' => Url::to(
                                                    [
                                                        'archive/accreditation',
                                                        'code' => $studentMeta->id,
                                                        'marked' => 1
                                                    ]
                                                ),
                                                'data-pjax' => 0
                                            ]
                                        );
                                        return $link;
                                    }

                                    return '-';
                                },
                            ],
                            [
                                'attribute' => 'difference',
                                'format' => 'raw',
                                'label' => __('Difference'),
                                'value' => function (EStudentMeta $studentMeta) {
                                    $countSubjects = 0;
                                    $countStudied = 0;
                                    $link = '-';
                                    if ($studentMeta->student !== null) {
                                        $countSubjects = EStudentMeta::getStudentSubjects($studentMeta, false, true);
                                    }
                                    if ($studentMeta->student->academicRecords !== null) {
                                        $countStudied = EStudentMeta::getMarkedSubjects($studentMeta, true);
                                    }
                                    //return $countSubjects - $countStudied;

                                    $link = Html::a(
                                        $countSubjects - $countStudied,
                                        '#',
                                        [
                                            'class' => 'showModalButton ',
                                            'modal-class' => 'modal-small',
                                            'title' => $studentMeta->student->getFullName(),
                                            'value' => Url::to(
                                                [
                                                    'archive/accreditation',
                                                    'code' => $studentMeta->id,
                                                    'diff' => 1
                                                ]
                                            ),
                                            'data-pjax' => 0
                                        ]
                                    );
                                    return $link;
                                },
                            ],

                        ],
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>


<?php Pjax::end() ?>
