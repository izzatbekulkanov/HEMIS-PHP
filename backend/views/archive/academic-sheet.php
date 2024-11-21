<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\student\EStudentMeta;
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
/* @var $searchModel \common\models\archive\EStudentAcademicSheetMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php $form = ActiveForm::begin(); ?>
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
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <?= GridView::widget([
                'id' => 'data-grid',
                'sticky' => '#sidebar',
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EStudentMeta $data) {
                            return Html::a(sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number), linkTo(['academic-sheet', 'id' => $data->id]), ['data-pjax' => 0, 'target' => '_blank']);
                        },
                    ],

                    [
                        'attribute' => '_education_type',
                        'format' => 'raw',
                        'value' => function (EStudentMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', @$data->educationType->name, @$data->educationForm->name);
                        },
                    ],
                    [
                        'attribute' => '_specialty_id',
                        'format' => 'raw',
                        'value' => function (EStudentMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', @$data->specialty->mainSpecialty->code, @$data->curriculum->name);
                        },
                    ],
                    [
                        'attribute' => '_semestr',
                        'format' => 'raw',
                        'value' => function (EStudentMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', @$data->semester->name, @$data->educationYear->name);
                        },
                    ],
                    [
                        'attribute' => '_group',
                        'value' => 'group.name',
                    ],
                    [
                        'format' => 'raw',
                        'value' => function (EStudentMeta $data) {
                            return Html::a(__('View Academic Sheet'), linkTo(['academic-sheet', 'id' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0, 'target' => '_blank']);
                        },
                    ],
                    [
                        'format' => 'raw',
                        'value' => function (EStudentMeta $data) {
                            return Html::a(__('Download'), linkTo(['academic-sheet', 'id' => $data->id, 'download' => 1]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
<?php Pjax::end() ?>
