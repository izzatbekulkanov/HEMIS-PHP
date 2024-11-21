<?php

use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\student\EStudentDecreeMeta;
use common\models\system\classifier\DecreeType;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use backend\widgets\DatePickerDefault;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\student\EStudentDecreeMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = ['url' => ['decree/index'], 'label' => __('Decree Index')];
$this->params['breadcrumbs'][] = $this->title;


?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'transfer-form']]); ?>
<div class="row">
    <div class="col col-md-8">
        <div class="box box-default ">
            <?= GridView::widget([
                'id' => 'data-grid',
                'sticky' => '#sidebar',
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function (EStudentDecreeMeta $model, $key, $index, $column) use ($searchModel) {
                            return [
                                'disabled' => !$model->canOperateDecreeApply($searchModel->_decree ? $searchModel->decree : null)
                            ];
                        }
                    ],
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EStudentDecreeMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                        },
                    ],
                    [
                        'attribute' => '_education_type',
                        'format' => 'raw',
                        'value' => function (EStudentDecreeMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', @$data->educationType->name, @$data->educationForm->name);
                        },
                    ],
                    [
                        'attribute' => '_level',
                        'format' => 'raw',
                        'value' => function (EStudentDecreeMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', @$data->level->name, @$data->semester->name);
                        },
                    ],
                    [
                        'attribute' => '_group',
                        'format' => 'raw',
                        'value' => function (EStudentDecreeMeta $data) {
                            return @$data->group->name;
                        },
                    ]
                ],
            ]); ?>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <div class="box box-default " id="data-grid-filters">

            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getCurriculumItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudentdecreemeta-_education_year").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = $searchModel->_curriculum == null,
                            'options' => [
                                'onchange' => '$("#estudentdecreemeta-_level").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_level')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getLevelItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudentdecreemeta-_semestr").val("")'
                            ],
                            'disabled' => $disabled = ($disabled || $searchModel->_education_year == null),
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSemesterItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudentdecreemeta-_group").val("")'
                            ],
                            'disabled' => $disabled = ($disabled || $searchModel->_level == null),
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_semestr == null),
                        ])->label(false); ?>
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?= Html::a('<i class="fa fa-close"></i> ' . __('Clear Filter'), ['apply', 'clear-filter' => 1], [
                    'class' => 'btn btn-default btn-flat',
                    'data-pjax' => 0
                ]) ?>
            </div>
            <div class="box-header bg-gray"></div>
            <div class="box-body">
                <?= $form->field($searchModel, 'selectedStudents')->textInput(['readonly' => true])->label(); ?>
                <?php
                $decrees = EDecree::getOptionsByCurriculum($this->_user(), $searchModel->_curriculum ? $searchModel->curriculum : null, array_keys($handlers));
                ?>
                <?= $form->field($searchModel, '_decree')->widget(Select2Default::class, [
                    'data' => $decrees['options'],
                    'options' => [
                        'id' => '_decree',
                        'required' => true,
                    ],
                    'disabled' => $searchModel->_curriculum == null,
                    'hideSearch' => false,
                    'allowClear' => true,
                ]); ?>
                <?= $form->field($searchModel, 'order_date')->textInput([
                    'disabled' => true,
                    'id' => 'order_date'
                ]); ?>
            </div>

            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Apply to Decree'), [
                    'class' => 'btn btn-primary btn-flat',
                    'disabled' => $searchModel->_decree == null,
                    'onclick' => 'return confirmTransfer()'
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<script>

    function updateSelectedStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        $('#estudentdecreemeta-selectedstudents').val(keys.length)
    }

    function confirmTransfer() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length > 0) {
            if (confirm(<?=json_encode([__('Are you sure to apply selected decree to {count} students?')])?>[0].replace('{count}', keys.length))) {
                $('#transfer-form').submit();
            }
        } else {
            alert(<?=json_encode([__('Please, select students')])?>[0])
        }

        return false;
    }
</script>
<?php
$this->registerJs("$('#data-grid input[type=\"checkbox\"]').on('change',function(){updateSelectedStudents()})")
?>
<?php Pjax::end() ?>


