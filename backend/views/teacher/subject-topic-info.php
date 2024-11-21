<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
use common\models\curriculum\Semester;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-topics'], 'label' => $this->title];
$this->params['breadcrumbs'][] = $curriculum_subject->curriculum->name;
$this->params['breadcrumbs'][] = Semester::getByCurriculumSemester($curriculum_subject->_curriculum, $curriculum_subject->_semester)->name;
$this->params['breadcrumbs'][] = $curriculum_subject->subject->name;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
					<?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-4">
                        <?php
                            $trainings = array();
                            $trainings = ArrayHelper::map(ECurriculumSubjectDetail::getTrainingByCurriculumSemesterSubject($curriculum_subject->_curriculum, $curriculum_subject->_semester, $curriculum_subject->_subject), '_training_type', 'fullName');
                        ?>
                        <?= $form->field($searchModel, '_training_type')->widget(Select2Default::class, [
                            'data' => $trainings,
                            'options' => [
                                'id' => '_search_training_type',
                            ]
                        ])->label(false); ?>


                    </div>

					<?php ActiveForm::end(); ?>
				</div>
			</div>
	
            <div class="box-body no-padding">
               <?= GridView::widget([
                    'id' => 'data-grid',
                    'sortable' => true,
                    'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        /*[
                            'attribute' => 'position',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->position, ['curriculum/subject-topic', 'id' => $data->_curriculum, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],*/
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($curriculum_subject) {
                                return Html::a($data->name, ['teacher/subject-topic-info', 'id' => $curriculum_subject->id, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute'=>'_training_type',
                            'format' => 'raw',
                            'value' => function ($data) use ($curriculum_subject) {
                                return Html::a($data->trainingType->name, ['teacher/subject-topic-info', 'id' => $curriculum_subject->id, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute'=>'_semester',
                            'value' => function ($data) {
                                return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?//php $form = ActiveForm::begin(); ?>
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => true, 'options' => ['data-pjax' => 1]]); ?>

        <div class="box box-default ">
			<?php //echo $form->errorSummary($model)?>
            <div class="box-body">
                <?php if ($curriculum_subject->reorder): ?>
                    <div class="alert alert-warning">
                        <?php echo __('Subject is accepted');?>
                    </div>
                <?php endif; ?>

                <?= $form->field($model, '_curriculum')->hiddenInput(['id'=>'_curriculum', 'value'=>$curriculum_subject->_curriculum])->label(false); ?>

                <?php if($model->isNewRecord) {
                    if($searchModel->_training_type)
                        $model->_training_type = $searchModel->_training_type;
                }?>

                <?= $form->field($model, '_training_type')->widget(Select2Default::class, [
                    'data' => $trainings,
                    'options' => [
                        'id' => '_training_type',
                    ]
                ]); ?>

                <?= $form->field($model, 'name')->textarea(['maxlength' => true])->label() ?>

            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['teacher/subject-topic-info', 'id'=>$curriculum_subject->id], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?php if (!$curriculum_subject->reorder): ?>
                        <?= $this->getResourceLink(__('Delete'), ['teacher/subject-topic-info', 'id'=>$curriculum_subject->id, 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>

                <?php else: ?>
                <?php endif; ?>
                <?php if (!$curriculum_subject->reorder): ?>
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

<?php Pjax::end() ?>

