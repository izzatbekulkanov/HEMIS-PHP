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

$this->title = $curriculum->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/curriculum'], 'label' => __('List Curriculum')];
$this->params['breadcrumbs'][] = $this->title;
if($searchModel->_semester){
    $this->params['breadcrumbs'][] = Semester::findOne(['code'=>$searchModel->_semester, '_curriculum'=>$curriculum->id])->name;
}

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
					<?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_semester')->widget(Select2Default::class, [
                            'data' => ArrayHelper::map(Semester::getSemesterByCurriculum($curriculum->id), 'code', 'name'),
                            'placeholder' => __('-Choose Semester-'),
                            'options' => [
                                'id' => '_search_semester',
                            ]
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-4">
                        <?php
                        $subjects = array();
                        if($searchModel->_semester){
                            $subjects = ArrayHelper::map(ECurriculumSubject::find()->where('_curriculum=:curriculum AND _semester=:semester AND active=:active', [':curriculum' => $curriculum->id, ':semester' => $searchModel->_semester, ':active'=>true])->all(), '_subject', 'subject.name');
                        }
                        ?>
                        <?= $form->field($searchModel, '_subject')->widget(DepDrop::classname(), [
                            'data' => $subjects,
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose Subject-'),
                                'id' => '_search_subject',
                            ],
                            'pluginOptions' => [
                                //'initialize' => true,
                                'depends'=>['_curriculum', '_search_semester'],
                                'placeholder' => __('-Choose Subject-'),
                                'url'=>Url::to(['/ajax/get-semester-subject']),
                            ],
                        ])->label(false)?>
					</div>
                    <div class="col col-md-4">
                        <?php
                        $trainings = array();
                        if($searchModel->_subject){
                            $trainings = ArrayHelper::map(ECurriculumSubjectDetail::find()->where('_curriculum=:curriculum AND _semester=:semester AND _subject=:subject AND active=:active', [':curriculum' => $curriculum->id, ':semester' => $searchModel->_semester, ':subject' => $searchModel->_subject, ':active'=>true])->all(), '_training_type', 'fullName');
                        }
                        ?>
                        <?= $form->field($searchModel, '_training_type')->widget(DepDrop::classname(), [
                            'data' => $trainings,
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose-'),
                                'id' => '_search_training_type',
                            ],
                            'pluginOptions' => [
                                //'initialize' => true,
                                'depends'=>['_curriculum', '_search_semester', '_search_subject'],
                                'placeholder' => __('-Choose-'),
                                'url'=>Url::to(['/ajax/get-subject-training']),
                            ],
                        ])->label(false)?>
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
                            'value' => function ($data) {
                                return Html::a($data->name, ['curriculum/subject-topic', 'id' => $data->_curriculum, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute'=>'_training_type',
                            'value' => 'trainingType.name',
                        ],
                        [
                            'attribute'=>'_semester',
                            'value' => 'semester.name',
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
                <?= $form->field($model, '_curriculum')->hiddenInput(['id'=>'_curriculum', 'value'=>$curriculum->id])->label(false); ?>

                <?php if($model->isNewRecord) {
                    if($searchModel->_semester)
                        $model->_semester = $searchModel->_semester;
                    if($searchModel->_subject)
                        $model->_subject = $searchModel->_subject;
                    if($searchModel->_training_type)
                        $model->_training_type = $searchModel->_training_type;
                }?>

                <?= $form->field($model, '_semester')->widget(Select2Default::class, [
                    'data' => ArrayHelper::map(Semester::getSemesterByCurriculum($curriculum->id), 'code', 'name'),
                    'placeholder' => __('-Choose Semester-'),
                    'options' => [
                        'id' => '_semester',
                    ]
                ]); ?>

                <?php
                    $subjects = array();
                    if($model->_semester){
                        $subjects = ArrayHelper::map(ECurriculumSubject::find()->where('_curriculum=:curriculum AND _semester=:semester AND active=:active', [':curriculum' => $curriculum->id, ':semester' => $model->_semester, ':active'=>true])->all(), '_subject', 'subject.name');
                    }
                ?>
                <?= $form->field($model, '_subject')->widget(DepDrop::classname(), [
                    'data' => $subjects,
                    'language' => 'en',
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'placeholder' => __('-Choose-'),
                        'id' => '_subject',
                    ],
                    'pluginOptions' => [
                        //'initialize' => true,
                        'depends'=>['_curriculum', '_semester'],
                        'placeholder' => __('-Choose-'),
                        'url'=>Url::to(['/ajax/get-semester-subject']),
                    ],
                ])?>

                <?php
                $trainings = array();
                if($model->_subject){
                    $trainings = ArrayHelper::map(ECurriculumSubjectDetail::find()->where('_curriculum=:curriculum AND _semester=:semester AND _subject=:subject AND active=:active', [':curriculum' => $curriculum->id, ':semester' => $model->_semester, ':subject' => $model->_subject, ':active'=>true])->all(), '_training_type', 'fullName');
                }
                ?>
                <?= $form->field($model, '_training_type')->widget(DepDrop::classname(), [
                    'data' => $trainings,
                    'language' => 'en',
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'placeholder' => __('-Choose-'),
                        'id' => '_training_type',
                    ],
                    'pluginOptions' => [
                        //'initialize' => true,
                        'depends'=>['_curriculum', '_semester', '_subject'],
                        'placeholder' => __('-Choose-'),
                        'url'=>Url::to(['/ajax/get-subject-training']),
                    ],
                ])?>
                <?= $form->field($model, 'name')->textarea(['maxlength' => true])->label() ?>

            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/subject-topic', 'id'=>$curriculum->id], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?//= $this->getResourceLink(__('Delete'), ['curriculum/subject-topic', 'id'=>$curriculum->id, 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php else: ?>
                <?php endif; ?>
                <?//= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
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

