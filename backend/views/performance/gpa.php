<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\performance\EStudentGpa;
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

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\performance\EStudentGpa */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col col-md-1">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        __('Add GPA'),
                        ['performance/gpa-add'],
                        ['class' => 'btn btn-flat btn-success btn-block ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-11">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudentgpa-_education_year").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = $searchModel->_department == null,
                            'options' => [
                                'onchange' => '$("#estudentgpa-_education_type").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationTypeItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_education_year == null),
                            'options' => [
                                'onchange' => '$("#estudentgpa-_education_form").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationFormItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_education_type == null),
                            'options' => [
                                'onchange' => '$("#estudentgpa-_curriculum").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getCurriculumItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_education_form == null),
                            'options' => [
                                'onchange' => '$("#estudentgpa-_group").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_curriculum == null),
                        ])->label(false); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                },
            ],

            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_group',
                'value' => 'group.name',
            ],
            [
                'attribute' => '_level',
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->level->name, $data->educationYear->name);
                },
            ],
            [
                'attribute' => 'subjects',
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return Html::a(sprintf('%s / %s', $data->subjects, $data->credit_sum), '#', [
                        'class' => 'showModalButton',
                        'title' => __('GPA of {name}', ['name' => $data->student->getFullName()]),
                        'value' => currentTo(['subjects' => $data->id]),
                        'data-pjax' => 0
                    ]);
                },
            ],
            [
                'attribute' => 'gpa',
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return $data->markingSystem->isCreditMarkingSystem() ? $data->gpa : '-';
                },
            ],
            [
                'attribute' => 'debt_subjects',
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],
            [
                'format' => 'raw',
                'value' => function (EStudentGpa $data) {
                    return Html::a(__('Recalculate'), ['gpa', 'recalculate' => $data->id], ['data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
