<?php
/*
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use common\models\system\classifier\TeacherStatus;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>
<div class="row">
    <div class="col col-md-9 col-lg-9">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_faculty')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => true,
                                'placeholder' => __('-Choose Faculty-'),
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_department')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDepartment::getDepartments(),
                                'allowClear' => true,
                                'placeholder' => __('-Choose Department-'),
                            ]
                        )->label(false); ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <?= GridView::widget(
                [
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                        ],
                        [
                            'attribute' => '_employee',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->employee->fullName;
                            },
                        ],
                        [
                            'attribute' => '_department',
                            'value' => 'department.name',
                            'header' => __('Structure Department'),
                        ],
                        [
                            'attribute' => '_position',
                            'value' => 'staffPosition.name',
                        ],
                        [
                            'attribute' => 'employmentForm.name',
                            'header' => __('Employment Form')
                        ],
                        [
                            'attribute' => '_employment_staff',
                            'value' => 'employmentStaff.name',
                        ],
                        [
                            'attribute' => '_employee_status',
                            'value' => 'employeeStatus.name',
                        ],
                    ],
                ]
            ); ?>
        </div>
    </div>
    <div class="col col-md-3" id="sidebar">
        <div class="box box-default ">
            <div class="box-body">
                <?php $form2 = ActiveForm::begin(
                    [
                        'id' => 'employee_status_form',
                        'action' => ['/employee/to-status'],
                        'enableAjaxValidation' => false,
                        'validateOnSubmit' => false,
                        'options' => ['data-pjax' => 1, 'method' => 'post'],
                    ]
                ); ?>

                <?= $form->field($searchModelFix, '_employee_status')->widget(
                    Select2Default::classname(),
                    [
                        'data' => TeacherStatus::getClassifierOptions(),
                        'allowClear' => true,
                        'placeholder' => __('Status type'),
                        'hideSearch' => false,
                        'options' => [
                            'id' => '_employee_status',
                            'required' => true
                        ],
                    ]
                )->label(__('Status type')); ?>

                <?= $form2->field($searchModelFix, 'contract_number')->textInput(
                    ['maxlength' => true, 'required' => true, 'id' => 'contract_number']
                )->label(__('Document number')) ?>
                <?= $form->field($searchModelFix, 'contract_date')->widget(
                    DatePickerDefault::classname(),
                    [
                        'options' => [
                            'placeholder' => __('Document date'),
                            'id' => 'contract_date',
                            'required' => true,
                            //'value' => empty($model->birth_date) ? null : Yii::$app->formatter->asDate($model->birth_date, 'php:Y-m-d'),
                            //'disabled' => true
                        ],
                    ]
                )->label(__('Document date')); ?>

            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton(
                    '<i class="fa fa-check"></i> ' . __('OK'),
                    ['class' => 'btn btn-primary btn-flat', 'id' => 'assign']
                ) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<script>
    var base_url = '<?= \Yii::$app->request->hostInfo; ?>';
</script>
<?php
$script = <<< JS
	$("#assign").click(function(e){
	    if (!$('#employee_status_form').is(':valid')) {
	        $('#employee_status_form').yiiActiveForm('updateMessages')
	        return false;
	    }
	    e.preventDefault();
		var keys = $('#data-grid').yiiGridView('getSelectedRows');
		var contract_number =  $('#contract_number').val();
		var contract_date =  $('#contract_date').val();
		var status =  $('#_employee_status').val();
		$.post({
           url: '/employee/to-status',
           data: {selection: keys, contract_number: contract_number, contract_date: contract_date, status: status },
           dataType:"json"
        });
	});
JS;
$this->registerJs($script);
?>

<?php
$this->registerJs(
    '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
)
?>

<?php Pjax::end() ?>
