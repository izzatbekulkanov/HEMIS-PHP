<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\structure\EDepartment;
use common\models\system\classifier\SemestrType;

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/rating-grade'], 'label' => __('Rating grades')];
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
						<?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
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
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->name, ['curriculum/rating-grade', 'code' => $data->code], []);
                            },
                        ],
						[
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, ['curriculum/rating-grade', 'code' => $data->code], []);
                            },
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
				
			
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => !$model->isNewRecord])->label() ?>
                <?= $form->field($model, 'template')->textInput(['maxlength' => true, 'disabled' => !$model->isNewRecord])->label() ?>
				
				
            </div>
			<div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?//= $this->getResourceLink(__('Cancel'), ['curriculum/rating-grade'], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?//= $this->getResourceLink(__('Delete'), ['curriculum/rating-grade', 'code' => $model->code, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>


                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                <?php endif; ?>
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