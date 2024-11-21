<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\archive\EAcademicRecord;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\Semester;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $searchModel EStudentMeta
 */
$this->title = __('Batch rating students');
$this->params['breadcrumbs'][] = $this->title;

$faculty = "";
if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
    $faculty = $this->_user()->employee->deanFaculties->id;
}
?>
<?php
Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php
                if ($this->_user()->role->code !== "teacher") { ?>
                    <div class="row" id="data-grid-filters">
                        <?php
                        $form = ActiveForm::begin(); ?>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_education_type')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => \common\models\system\classifier\EducationType::getHighers(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_education_form')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => \common\models\system\classifier\EducationForm::getClassifierOptions(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_curriculum')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => (!$searchModel->_education_type || !$searchModel->_education_form) ? [] : \common\models\curriculum\ECurriculum::getOptionsByEduTypeForm(
                                        $searchModel->_education_type,
                                        $searchModel->_education_form,
                                        $faculty
                                    ),
                                    'disabled' => !$searchModel->_education_type || !$searchModel->_education_form,
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_semestr')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ($searchModel->_curriculum) ? ArrayHelper::map(
                                        Semester::getSemesterByCurriculum($searchModel->_curriculum),
                                        'code',
                                        'name'
                                    ) : [],
                                    'disabled' => !$searchModel->_curriculum,
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_group')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => (!$searchModel->_semestr || !$searchModel->_curriculum) ? [] : \common\models\student\EGroup::getOptions(
                                        $searchModel->_curriculum
                                    ),
                                    'disabled' => !$searchModel->_semestr,
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false) ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($studentSubject, '_subject')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ($searchModel->_curriculum && $searchModel->curriculum->getSubjects()->andWhere(['_semester' => $searchModel->_semestr])->count())
                                        ? ArrayHelper::map($searchModel->curriculum->getSubjects()->andWhere(['_semester' => $searchModel->_semestr])->all(),
                                        '_subject',
                                        'subject.name'
                                    ) : [],
                                    'disabled' => !$searchModel->_curriculum || !$searchModel->_semestr || !$searchModel->_group,
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false) ?>
                        </div>
                        <?php
                        ActiveForm::end(); ?>
                    </div>
                    <?php
                } ?>
            </div>
            <div class="box-body no-padding">
                <?= Html::beginForm(
                    [
                        'batch-rate',
                        'subject' => $studentSubject->_subject,
                        'curriculum' => $searchModel->_curriculum,
                        'semester' => $searchModel->_semestr
                    ]
                ) ?>
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'sticky' => '#sidebar',
                        'dataProvider' => $studentSubjectProvider,
                        'columns' => [
                            ['__class' => \yii\grid\SerialColumn::class],
                            [
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => static function ($data) {
                                    return $data->student->fullName;
                                },
                            ],
                            [
                                'attribute' => 'subject_name',
                                'format' => 'raw',
                                'label' => __('Studied subjects'),
                                'value' => function ($data) use ($searchModel) {
                                    $r = EAcademicRecord::find()->where(
                                        [
                                            '_curriculum' => $searchModel->_curriculum,
                                            '_subject' => $data->_subject,
                                            '_semester' => $searchModel->_semestr,
                                            '_education_year' => $searchModel->curriculum->_education_year,
                                            '_student' => $data->_student
                                        ]
                                    )->one();
                                    if ($r) {
                                        return $r->subject_name;
                                    }
                                    $subject = \common\models\curriculum\ESubject::findOne($searchModel->_subject);
                                    return $data->subject->name;
                                },
                            ],
                            [
                                'label' => __('O\'zlashtirgan'),
                                'value' => function ($data) use ($searchModel) {
                                    $r = EAcademicRecord::find()->where(
                                        [
                                            '_curriculum' => $searchModel->_curriculum,
                                            '_subject' => $data->_subject,
                                            '_semester' => $searchModel->_semestr,
                                            '_student' => $data->_student
                                        ]
                                    )->one();
                                    if ($r) {
                                        return __('Yes');
                                    }
                                    return __('No');
                                },
                            ],
                            [
                                'attribute' => __('Acload/credit'),
                                'value' => static function ($data) use ($searchModel) {
                                    $r = EAcademicRecord::find()->where(
                                        [
                                            '_curriculum' => $searchModel->_curriculum,
                                            '_subject' => $data->_subject,
                                            '_semester' => $searchModel->_semestr,
                                            '_student' => $data->_student
                                        ]
                                    )->one();
                                    if (!$r) {
                                        $r = \common\models\curriculum\ECurriculumSubject::find()->where(
                                            [
                                                '_curriculum' => $searchModel->_curriculum,
                                                '_subject' => $data->_subject,
                                                '_semester' => $searchModel->_semestr,
                                            ]
                                        )->one();
                                    }
                                    if ($r) {
                                        return $r->total_acload . ' / ' . $r->credit;
                                    }
                                    return '-';
                                },
                            ],
                            [
                                'attribute' => 'marking_system',
                                'header' => __('Marking System'),
                                'value' => function ($data) use ($searchModel) {
                                    return $data->curriculum->markingSystem->name;
                                }
                            ],
                            [
                                'attribute' => 'ball',
                                'format' => 'raw',
                                'label' => __('Ball'),
                                'value' => function ($data) use ($form, $model, $searchModel) {
                                    $r = EAcademicRecord::find()->where(
                                        [
                                            '_curriculum' => $searchModel->_curriculum,
                                            '_subject' => $data->_subject,
                                            '_semester' => $searchModel->_semestr,
                                            '_student' => $data->_student
                                        ]
                                    )->one();
                                    $s = $r ?: \common\models\curriculum\ECurriculumSubject::find()->where(
                                        [
                                            '_curriculum' => $searchModel->_curriculum,
                                            '_subject' => $data->_subject,
                                            '_semester' => $searchModel->_semestr,
                                        ]
                                    )->one();
                                    $max = GradeType::getGradeByCode(
                                        $searchModel->curriculum->_marking_system,
                                        GradeType::GRADE_TYPE_FIVE
                                    )->max_border;
                                    $min = GradeType::getGradeByCode(
                                        $searchModel->curriculum->_marking_system,
                                        GradeType::GRADE_TYPE_THREE
                                    )->min_border;
                                    $disabled = empty($s->total_acload);
                                    if ($searchModel->curriculum->_marking_system === MarkingSystem::MARKING_SYSTEM_CREDIT && empty($s->credit)) {
                                        $disabled = true;
                                    }
                                    return Html::input(
                                        'number',
                                        'student[' . $data->_student . ']grade',
                                        $r ? $r->total_point : '',
                                        ['min' => $min, 'max' => $max, 'step' => 0.01, 'disabled' => $disabled ? (int)$disabled : false]
                                    );
                                }
                            ],
                            [
                                'attribute' => 'grade',
                                'label' => __('Grade'),
                                'format' => 'raw',
                                'value' => function ($data) use ($form, $searchModel) {
                                    $r = EAcademicRecord::find()->where(
                                        [
                                            '_curriculum' => $searchModel->_curriculum,
                                            '_subject' => $data->_subject,
                                            '_semester' => $searchModel->_semestr,
                                            '_student' => $data->_student
                                        ]
                                    )->one();
                                    if ($r) {
                                        return $r->grade;
                                    }
                                    return '-';
                                }
                            ],
                        ],
                    ]
                ); ?>
                <div class="box-footer text-right">
                    <?php if ($studentSubjectProvider->query->count()): ?>
                    <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary btn-flat', 'name' => 'assign']) ?>
                    <?php endif; ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>


<?php
Pjax::end() ?>
