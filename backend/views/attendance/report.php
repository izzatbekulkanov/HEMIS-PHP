<?php

use backend\widgets\GridView;
use common\models\attendance\EAttendanceReport;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\attendance\EAttendanceReport */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $faculty int */
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'transfer-form']]); ?>
    <div class="row">
        <div class="col col-md-3 col-sm-push-9" style="padding-left: 0">
            <div class="box box-default " id="data-grid-filters">
                <div class="box-body">
                    <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                        'data' => $searchModel->getDepartmentItems(),
                        'allowClear' => $searchModel->_education_type == null,
                        'disabled' => $faculty != null,
                        'hideSearch' => false,
                        'options' => [
                            'onchange' => '$("#eattendancereport-_education_type").val("")'
                        ],
                    ])->label(false); ?>
                    <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                        'data' => $searchModel->getEducationTypeItems(),
                        'allowClear' => $searchModel->_curriculum == null,
                        'disabled' => $disabled = $searchModel->_department == null,
                        'hideSearch' => false,
                        'options' => [
                            'onchange' => '$("#eattendancereport-_curriculum").val("")'
                        ],
                    ])->label(false); ?>

                    <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                        'data' => $searchModel->getCurriculumItems(),
                        'allowClear' => $searchModel->_group == null,
                        'hideSearch' => false,
                        'disabled' => $disabled = $disabled || $searchModel->_education_type == null,
                        'options' => [
                            'onchange' => '$("#eattendancereport-_group").val("")'
                        ],
                    ])->label(false); ?>
                    <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                        'data' => $searchModel->getGroupItems($user),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'disabled' => $disabled = $disabled || $searchModel->_curriculum == null,
                    ])->label(false); ?>
                </div>
                <div class="box-header bg-gray"></div>
                <div class="box-body">
                    <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
                        'data' => $searchModel->getSemesterItems(),
                        'allowClear' => $searchModel->_subject == null,
                        'hideSearch' => false,
                        'disabled' => $disabled = ($disabled || $searchModel->_group == null),
                        'options' => [
                            'onchange' => '$("#eattendancereport-_subject").val("")'
                        ],
                    ])->label(false); ?>
                    <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                        'data' => $searchModel->getSubjectItems(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'disabled' => $disabled = ($disabled || $searchModel->_semester == null),
                    ])->label(false); ?>

                </div>

                <div class="box-footer text-right">
                    <?= Html::a('<i class="fa fa-close"></i> ' . __('Clear Filter'), ['report', 'clear-filter' => 1], [
                        'class' => 'btn btn-default btn-flat',
                        'data-pjax' => 0
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col col-md-9 col-sm-pull-3">
            <div class="box box-default">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'rowOptions' => function ($data, $key, $index, $grid) use ($searchModel) {
                        $percent = 0;
                        if (@$searchModel->subjectRealTrainingTypes->academic_load) {
                            $percent = round($data->absent_off / $searchModel->subjectRealTrainingTypes->academic_load * 100, 2) . ' %';
                        }
                        return [
                            'class' => $percent > 25 ? 'alert-danger' : ''
                        ];
                    },
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'student_name',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) {
                                return $data->student->getShortName();
                            },
                        ],
                        [
                            'attribute' => '_group',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) {
                                return @$data->group->name;
                            },
                        ],

                        [
                            'attribute' => '_subject',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) use ($searchModel) {
                                return $searchModel->subjectRealTrainingTypes ? $searchModel->subjectRealTrainingTypes->subject->name : '';
                            },
                        ],
                        [
                            'attribute' => 'academic_load',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) use ($searchModel) {
                                return $searchModel->subjectRealTrainingTypes ? $searchModel->subjectRealTrainingTypes->academic_load : '';
                            },
                        ],
                        [
                            'attribute' => 'absent_on',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) {
                                return $data->absent_on !== null ? $data->absent_on : 0;
                            },
                        ],
                        [
                            'attribute' => 'absent_off',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) {
                                return $data->absent_off !== null ? $data->absent_off : 0;
                            },
                        ],
                        [
                            'attribute' => 'total',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) {
                                return $data->total !== null ? $data->total : 0;
                            },
                        ],
                        [
                            'attribute' => 'percent',
                            'format' => 'raw',
                            'value' => function (EAttendanceReport $data) use ($searchModel) {
                                if (@$searchModel->subjectRealTrainingTypes->academic_load) {
                                    return round($data->absent_off / $searchModel->subjectRealTrainingTypes->academic_load * 100, 2) . ' %';
                                }
                                return '';
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
<?php Pjax::end() ?>