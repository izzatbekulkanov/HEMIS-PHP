<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\archive\EStudentReference;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\archive\EStudentReference */
/* @var $dataProvider yii\data\ActiveDataProvider */

$disabled = false;
if ($this->_user()->role->code === \common\models\system\AdminRole::CODE_DEAN) {
    $disabled = true;
}
if ($department != "") {
    $searchModel->_department = $department;
}

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">

            <div class="col col-md-12">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled
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
                            'data' => $searchModel->getCurriculumItems($searchModel->_department),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems($searchModel->_department, $searchModel->_curriculum),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <?/*<div class="col col-md-4">
                        <?= $form->field($searchModel, '_specialty')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSpecialtyItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>*/?>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
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
                'value' => function (EStudentReference $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->getFullName(), $data->student->student_id_number);
                },
            ],

            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function (EStudentReference $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_specialty',
                'format' => 'raw',
                'value' => function (EStudentReference $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', @$data->specialty->code, @$data->curriculum->name);
                },
            ],
            [
                'attribute' => '_level',
                'format' => 'raw',
                'value' => function (EStudentReference $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', @$data->level->name, @$data->semester->name);
                },
            ],
            [
                'attribute' => 'reference_number',
                'format' => 'raw',
                'value' => function (EStudentReference $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->reference_number, Yii::$app->formatter->asDate($data->reference_date->getTimestamp()));
                },
            ],
            [
                'attribute' => 'filename',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(
                        '<i class="fa fa-download"></i>',
                        linkTo(['reference', 'file' => $data->id]),
                        [
                            'class' => 'btn btn-default btn-flat',
                            'data-pjax' => 0
                        ]);
                },
            ]

        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
