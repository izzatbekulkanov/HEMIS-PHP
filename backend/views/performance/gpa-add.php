<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\performance\EStudentGpaMeta;
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
/* @var $searchModel \common\models\performance\EStudentGpaMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['url' => ['performance/gpa'], 'label' => __('Performance Gpa')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'gpa-form']]); ?>
<div class="row">
    <div class="col col-md-8">
        <div class="box box-default ">
            <?= GridView::widget([
                'id' => 'data-grid',
                'sticky' => '#sidebar',
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\CheckboxColumn'],
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EStudentGpaMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                        },
                    ],
                    [
                        'attribute' => '_group',
                        'value' => 'group.name',
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
                        'attribute' => '_level',
                        'value' => 'level.name',
                    ],
                    [
                        'header' => __('Gpa'),
                        'value' => function (EStudentGpaMeta $data) {
                            return $data->markingSystem->isCreditMarkingSystem() && $data->studentGpa ? $data->studentGpa->gpa : "-";
                        },
                    ],
                    [
                        'header' => __('Debt'),
                        'value' => function (EStudentGpaMeta $data) {
                            return $data->studentGpa ? $data->studentGpa->debt_subjects : "-";
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudentgpameta-_education_year").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = $searchModel->_department == null,
                            'options' => [
                                'onchange' => '$("#estudentgpameta-_education_type").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationTypeItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_education_year == null),
                            'options' => [
                                'onchange' => '$("#estudentgpameta-_education_form").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationFormItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_education_type == null),
                            'options' => [
                                'onchange' => '$("#estudentgpameta-_curriculum").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getCurriculumItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_education_form == null),
                            'options' => [
                                'onchange' => '$("#estudentgpameta-_group").val("")'
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_curriculum == null),
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Calculate GPA'), ['class' => 'btn btn-primary btn-flat', 'onclick' => 'return confirmCalculateGpa()']) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<script>
    function confirmCalculateGpa() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            if (confirm(<?=json_encode([__('Are you sure to calculate GPA for {count} students?')])?>[0].replace('{count}', keys.length))) {
                $('#gpa-form').submit();
            }
        } else {
            alert(<?=json_encode([__('Please, select students')])?>[0])
        }

        return false;
    }
</script>
<?php Pjax::end() ?>
