<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\EducationType;
use common\models\curriculum\SubjectGroup;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/subject'], 'label' => __('List Subject')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
					<?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => EducationType::getHighers(),
                            'allowClear' => true,
                            'options' => [

                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_subject_group')->widget(Select2Default::classname(), [
                            'data' => ArrayHelper::map(SubjectGroup::getOptions(), 'code', 'name'),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [

                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
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
							'attribute'=>'_education_type',
							'value' => 'educationType.name',
						],
                        [
                            'attribute'=>'_subject_group',
                            'value' => 'subjectGroup.name',
                        ],
						[
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, Url::current(['code' => $data->id]), []);
                            },
                        ],
		            ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(); ?>
        <div class="box box-default ">
			<?php //echo $form->errorSummary($model)?>
            <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                    <div class="row" id="data-grid-filters">
                        <div class="col col-md-6">
                        </div>
                        <div class="col col-md-6">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Export Subjects'),
                                ['curriculum/subject', 'download' => 1],
                                ['class' => 'btn btn-flat btn-success  btn-block pull-right', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="box-body">


                <?= $form->field($model, '_education_type')->widget(Select2Default::classname(), [
                    'data' => EducationType::getHighers(),
                    'allowClear' => true,
                    'options' => [
                        'id' => '_education_type',
                    ]
                ]); ?>
                <?= $form->field($model, '_subject_group')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map(SubjectGroup::getOptions(), 'code', 'name'),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_subject_group',
                    ]
                ]); ?>

                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label() ?>
            </div>
			<div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/subject'], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?= $this->getResourceLink(__('Delete'), ['curriculum/subject', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
        $.get('<?= Url::to(['curriculum/subject'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>
