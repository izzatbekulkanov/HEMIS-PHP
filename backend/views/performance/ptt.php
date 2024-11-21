<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\performance\estudentptt;
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
/* @var $searchModel \common\models\performance\EStudentPtt */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        __('Add'),
                        ['performance/ptt-edit'],
                        ['class' => 'btn btn-flat btn-success btn-block ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-10">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationTypeItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_specialty')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSpecialtyItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationFormItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getCurriculumItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
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
                'value' => function (EStudentPtt $data) {
                    return Html::a(sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number), linkTo(['ptt-edit', 'ptt' => $data->id], ['data-pjax' => 0]));
                },
            ],

            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function (EStudentPtt $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_specialty',
                'format' => 'raw',
                'value' => function (EStudentPtt $data) {
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
            [
                'attribute' => 'number',
                'format' => 'raw',
                'value' => function (EStudentPtt $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->number, Yii::$app->formatter->asDate($data->date->getTimestamp()));
                },
            ],
            [
                'attribute' => 'subjects_count',
                'format' => 'raw',
                'value' => function (EStudentPtt $data) {
                    return sprintf('%s / %s', $data->subjects_count, $data->graded_count);
                },
            ],
            [
                'format' => 'raw',
                'value' => function (EStudentPtt $data) {
                    return Html::a(__('Kiritish'), linkTo(['ptt-edit', 'ptt' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
