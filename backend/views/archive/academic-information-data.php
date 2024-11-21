<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\archive\EAcademicInformationData;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\archive\EAcademicInformationData */
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
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        __('Add'),
                        ['archive/academic-information-data-edit'],
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
                        <?= $form->field($searchModel, '_specialty')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSpecialtyItems(),
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
                'value' => function (EAcademicInformationData $data) {
                    return Html::a(sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number), linkTo(['academic-information-data-edit', 'information' => $data->id], ['data-pjax' => 0]));
                },
            ],

            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function (EAcademicInformationData $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_specialty',
                'format' => 'raw',
                'value' => function (EAcademicInformationData $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', @$data->specialty->mainSpecialty->code, @$data->curriculum->name);
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
                'attribute' => 'blank_number',
                'format' => 'raw',
                'value' => function (EAcademicInformationData $data) {
                    return sprintf('%s / %s <p class="text-muted">%s</p>', $data->blank_number, $data->register_number, Yii::$app->formatter->asDate($data->register_date->getTimestamp()));
                },
            ],
            [
                'format' => 'raw',
                'value' => function (EAcademicInformationData $data) {
                    return Html::a(__('View'), linkTo(['academic-information-data-edit', 'information' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
