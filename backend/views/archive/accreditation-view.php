<?php

use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\GradeType;
use common\models\student\EStudentMeta;
use common\models\curriculum\Semester;
use kartik\select2\Select2Asset;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model EStudentMeta
 */
$this->registerAssetBundle(Select2Asset::class);
$this->title = __('Student accreditation');
$this->params['breadcrumbs'][] = ['url' => ['archive/accreditation'], 'label' => __('Accreditation')];
$this->params['breadcrumbs'][] = $this->title;
$max = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border, 0);

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
                        <div class="col col-md-6"></div>
                        <div class="col col-md-6">
                            <?= Html::a(
                                "<i class='fa fa-download'></i>&nbsp;&nbsp;" . __('Download'),
                                ['accreditation-view', 'id' => $model->id, 'download' => 1],
                                ['data-pjax' => 0, 'class' => 'btn btn-info pull-right']
                            ) ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="box-body no-padding">
                <?= DetailView::widget(
                    [
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => static function ($data) {
                                    return $data->student->fullName;
                                },
                            ],
                            [
                                'attribute' => '_specialty_id',
                                'value' => static function ($data) {
                                    return $data->specialty->mainSpecialty ? $data->specialty->mainSpecialty->name : $data->specialty->name;
                                },
                            ],
                            [
                                'attribute' => '_education_type',
                                'value' => static function ($data) {
                                    return $data->educationType->name;
                                },
                            ],
                            [
                                'attribute' => '_education_form',
                                'value' => static function ($data) {
                                    return $data->educationForm->name;
                                },
                            ],
                            [
                                'attribute' => '_curriculum',
                                'value' => static function ($data) {
                                    return $data->curriculum->name;
                                },
                            ],
                            [
                                'attribute' => '_marking_system',
                                'label' => __('Marking System'),
                                'value' => static function ($data) {
                                    return $data->curriculum->markingSystem->name;
                                },
                            ],
                        ],
                    ]
                ); ?>
            </div>
            <div class="box box-default ">
                <div class="box-header bg-gray">
                    <?php if ($this->_user()->role->code !== "teacher") { ?>
                        <div class="row" id="data-grid-filters">
                        </div>
                    <?php } ?>
                </div>
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'dataProvider' => $dataProvider,
                        'rowOptions' => function ($data, $key, $index, $grid) use ($model) {
                            if ($p = $data->getStudentSubjectRecord($model->_student)) {
                                return ['class' => 'row-yes'];
                            }
                            return ['class' => 'row-no'];
                        },
                        'beforeRow' => function ($model, $key, $index, $grid) use ($acloads, $balls) {
                            if (!empty($model->_rating_grade) && $model->_rating_grade !== $grid->currentGroup) {
                                $grid->currentGroup = $model->_rating_grade;
                                $acload = $acloads[$model->_rating_grade] ?? '';
                                $ball = $balls[$model->_rating_grade] ?? '';
                                return Html::tag(
                                    'tr',
                                    Html::tag(
                                        'td',
                                        "<b>{$model->ratingGrade->name}</b>",
                                        ['colspan' => 5]
                                    ) . Html::tag(
                                        'td',
                                        "<span class='h4'><b>{$acload}</b></span>"
                                    ) . Html::tag(
                                        'td',
                                        $ball ? sprintf("<span class='h4'><b class=''>%.2f</b></span>", $ball) : '',
                                        ['colspan' => 3]
                                    ),
                                    ['cols' => count($grid->columns)]
                                );
                            }
                            return '';
                        },
                        'columns' => [
                            [
                                '__class' => SerialColumn::class,
                            ],
                            [
                                'attribute' => 'name',
                                'format' => 'raw',
                                'label' => __('Curriculum subjects'),
                                'value' => function ($data) use ($model) {
                                    $exists = $model->getSubjects()->andFilterWhere(
                                        ['e_student_subject._subject' => $data->_subject]
                                    )->exists();
                                    if (!$exists) {
                                        return Html::a(
                                            $data->subject->name,
                                            currentTo(
                                                [
                                                    'student' => $model->_student,
                                                    'subject' => $data->id,
                                                    'rating' => 1,
                                                ]
                                            )
                                        );
                                    }
                                    if ($p = $data->getStudentSubjectRecord($model->_student)) {
                                        return Html::a(
                                            $data->subject->name,
                                            'javascript:void(0);',
                                            [
                                                'class' => 'showModalButton',
                                                'modal-class' => "modal-small",
                                                'value' => currentTo(
                                                    [
                                                        'record' => $p->id,
                                                        'subject' => $data->id,
                                                        'rating' => 1,
                                                    ]
                                                ),
                                                'title' => __(
                                                    'Rating subject "{subject}"',
                                                    ['subject' => $data->subject->name]
                                                ),
                                            ]
                                        );
                                    }
                                    return Html::a(
                                        $data->subject->name,
                                        'javascript:void(0);',
                                        [
                                            'class' => 'showModalButton',
                                            'modal-class' => "modal-small",
                                            'value' => currentTo(
                                                [
                                                    'student' => $model->_student,
                                                    'subject' => $data->id,
                                                    'rating' => 1,
                                                ]
                                            ),
                                            'title' => __(
                                                'Rating subject "{subject}"',
                                                ['subject' => $data->subject->name]
                                            ),
                                        ]
                                    );
                                },
                            ],
                            [
                                'attribute' => 'subject_name',
                                'format' => 'raw',
                                'label' => __('Studied subjects'),
                                'value' => function ($data) use ($model) {
                                    if ($p = $data->getStudentSubjectRecord($model->_student)) {
                                        return $p->subject_name;
                                    }
                                    return $data->subject->name;
                                },
                            ],
                            [
                                'label' => __('Semestr'),
                                'attribute' => '_semester',
                                'value' => function ($data) {
                                    return Semester::getByCurriculumSemester(
                                        $data->_curriculum,
                                        $data->_semester
                                    )->name;
                                },
                            ],
                            [
                                'label' => __('O\'zlashtirgan'),
                                'value' => function ($data) use ($model) {
                                    if ($p = $data->getStudentSubjectRecord($model->_student)) {
                                        return __('Yes');
                                    }
                                    return __('No');
                                },
                            ],
                            [
                                'attribute' => __('Acload/credit'),
                                'value' => static function (ECurriculumSubject $curriculumSubject) use ($model) {
                                    if ($p = $curriculumSubject->getStudentSubjectRecord($model->_student)) {
                                        return $p->total_acload . ' / ' . $p->credit;
                                    }
                                    return $curriculumSubject->total_acload . ' / ' . $curriculumSubject->credit;
                                },
                            ],
                            [
                                'attribute' => 'ball',
                                'format' => 'raw',
                                'label' => __('Ball'),
                                'value' => function ($data) use ($model) {
                                    if ($p = $data->getStudentSubjectRecord($model->_student)) {
                                        return $p->total_point;
                                    }
                                    return '-';
                                },
                            ],
                            [
                                'attribute' => 'grade',
                                'format' => 'raw',
                                'label' => __('Grade'),
                                'value' => function ($data) use ($model, $max) {
                                    if ($p = $data->getStudentSubjectRecord($model->_student)) {
                                        return $p->grade;
                                    }
                                    return '-';
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{delete}',
                                'buttons' => [
                                    'delete' => function ($url, $data) use ($model) {
                                        if (!$model->accreditation_accepted && $p = $data->getStudentSubjectRecord($model->_student)) {
                                            $url = Url::current(['record' => $p->id, 'delete' => 1]);
                                            return Html::a(
                                                '<span class="fa fa-trash"></span>',
                                                $url,
                                                [
                                                    'title' => __('Delete'),
                                                    'data-confirm' => __('	Are you sure to delete?'),
                                                    'data-pjax' => '0',
                                                ]
                                            );
                                        }
                                        return '';
                                    }
                                ],
                            ],
                        ],
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>


<?php Pjax::end() ?>
