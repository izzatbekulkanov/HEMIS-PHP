<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\curriculum\Semester;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectExamSchedule */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Exam Schedule');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeEducationYearItems('midterm'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeSemesterItems('midterm'),
                    'allowClear' => true,
                    // 'placeholder' => __('-Choose Education Type'),
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeGroupItems('midterm'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeSubjectItems('midterm'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>

            <div class="col col-md-2">
                <?= $form->field($searchModel, 'final_exam_type')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeFinalExamTypeItems('midterm'),
                    'allowClear' => true,
                  //  'disabled' => !$searchModel->_subject,
                    // 'placeholder' => __('-Choose Education Type'),
                ])->label(false); ?>
            </div>




            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
		'dataProvider' => $dataProvider,
        'columns' => [
			['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'_education_year',
                'value' => 'educationYear.name',
            ],
			[
                'attribute'=>'_semester',
            	'value' => function ($data) {
                    return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                },
			],
			[
                'attribute'=>'_group',
                'format' => 'raw',
				'value' => function($data){
                    if($data->_exam_type == ExamType::EXAM_TYPE_FINAL)
                        return Html::a($data->group->name,['teacher/check-overall-rating', 'id' => $data->id], ['data-pjax' => 0]);
                    else
                        return Html::a($data->group->name,['teacher/check-rating', 'id' => $data->id], ['data-pjax' => 0]);
                    },
			],
            [
                'attribute'=>'exam_date',
                'format' => 'raw',
                'value' => function($data){
                    if($data->_exam_type == ExamType::EXAM_TYPE_FINAL)
                        return Html::a(Yii::$app->formatter->asDate($data->exam_date, 'dd-MM-Y'),['teacher/check-overall-rating', 'id' => $data->id], ['data-pjax' => 0]);
                    else
                        return Html::a(Yii::$app->formatter->asDate($data->exam_date, 'dd-MM-Y'),['teacher/check-rating', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
			[
                'attribute'=>'_subject',
            	'value' => 'subject.name',
			],
			[
                'attribute'=>'_exam_type',
            	'value' => 'examType.name',
                'value' => function($data){
                    return strtoupper($data->examType->name). ' | '.$data->finalExamType->name;
                },
			],
			/*[
                'attribute'=>'exam_name',
            ],*/
			
			[
                'attribute'=>'_lesson_pair',
            	'value' => 'lessonPair.fullName',
			],
			[
                'attribute'=>'_auditorium',
            	'value' => 'auditorium.name',
			],
			/*[
                'attribute'=>'_employee',
            	'value' => 'employee.fullName',
			],*/
      ],
    ]); ?>
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
