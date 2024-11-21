<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\structure\EDepartment;
use common\models\curriculum\MarkingSystem;

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use backend\widgets\Select2Default;

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
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_specialty'
                            ],
                        ])->label(false); ?>
                    </div>
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
                                return Html::a($data->name, Url::current(['code' => $data->id]), []);
                            },
                        ],
						[
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, Url::current(['code' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => '_marking_system',
                            'value' =>'markingSystem.name'
                        ],
                        [
                            'attribute' => 'min_border',
                        ],
                        [
                            'attribute' => 'max_border',
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
                <?= $form->field($model, '_marking_system')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(MarkingSystem::find()->orderBy(['code' => SORT_ASC])->all(), 'code', 'name'),
                    'options' => [
                        'class' => 'select2',
                        'placeholder' => _('-Choose-'),
                        'disabled' => ($model->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT)
                    ],
                    'theme' => Select2::THEME_DEFAULT,
                    'pluginLoading' => false,
                    'hideSearch' => true,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
                <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => (!$model->isNewRecord || ($model->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT))])->label() ?>
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'disabled' => ($model->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE || $model->_marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT)]) ?>
                <?= $form->field($model, 'min_border')->textInput(['maxlength' => true]);?>
                <?= $form->field($model, 'max_border')->textInput(['maxlength' => true]);?>

	        </div>
			<div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/grade-type'], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?php if($model->_marking_system != MarkingSystem::MARKING_SYSTEM_RATING && $model->_marking_system != MarkingSystem::MARKING_SYSTEM_FIVE && $model->_marking_system != MarkingSystem::MARKING_SYSTEM_CREDIT):?>
                    <?= $this->getResourceLink(__('Delete'), ['curriculum/grade-type', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
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
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['structure/department'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>
