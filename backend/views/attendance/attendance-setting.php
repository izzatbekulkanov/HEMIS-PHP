<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\MarkingSystem;
use common\models\system\classifier\AttendanceSetting;

use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
					<?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_marking_system')->widget(Select2Default::classname(), [
                            'data' => MarkingSystem::getOptions(),
                            'allowClear' => false,
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_marking_system'
                            ],
                        ])->label(false); ?>
                    </div>

					<div class="col col-md-6">
						<?//= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
					</div>

					<?php ActiveForm::end(); ?>
				</div>
			</div>
	
            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
					'toggleAttribute' => 'active',
                    
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'min_border',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->min_border, ['attendance/attendance-setting', 'id' => $data->id], []);
                            },
                        ],
						[
                            'attribute' => '_attendance_setting',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->attendanceSetting->name, ['attendance/attendance-setting', 'id' => $data->id], []);
                            },
                        ],
                        [
                            'attribute' => '_marking_system',
                            'value' =>'markingSystem.name'
                        ],
					
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
			<?php //echo $form->errorSummary($model)?>
            <div class="box-body">

                <?= $form->field($model, '_marking_system')->widget(Select2Default::classname(), [
                    'data' => MarkingSystem::getOptions(),
                    'allowClear' => false,
                ]) ?>
                <?= $form->field($model, '_attendance_setting')->widget(Select2Default::classname(), [
                    'data' => AttendanceSetting::getClassifierOptions(),
                    'allowClear' => false,
                ]) ?>
                <?= $form->field($model, 'min_border')->textInput(['maxlength' => true])->label() ?>

            </div>
			<div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['attendance/attendance-setting'], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?= $this->getResourceLink(__('Delete'), ['attendance/attendance-setting', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>