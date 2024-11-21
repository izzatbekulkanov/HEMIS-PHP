<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\attendance\EAttendanceControl;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\Semester;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectSchedule */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = __('Subject Schedule');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default">

    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeEducationYearItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeSemesterItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeGroupItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeSubjectItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <?/*
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map(ESubjectSchedule::find()->where([ '_education_year' => EducationYear::getCurrentYear()->code, '_employee'=>Yii::$app->user->identity->_employee])->all(), '_group', 'group.name'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>

            <div class="col col-md-3">
                <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map(ESubjectSchedule::find()->where([ '_education_year' => EducationYear::getCurrentYear()->code, '_employee'=>Yii::$app->user->identity->_employee])->all(), '_subject', 'subject.name'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>*/?>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_training_type')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map(ESubjectSchedule::find()->where([ '_education_year' => EducationYear::getCurrentYear()->code, '_employee'=>Yii::$app->user->identity->_employee])->all(), '_training_type', 'trainingType.name'),
                    'allowClear' => true,
                    'hideSearch' => false,
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
				//	return Html::a($data->group->name,['teacher/check-lesson', 'id' => $data->id], ['data-pjax' => 0]);
                    return Html::a($data->group->name,['teacher/check-lesson', 'id' => $data->id], ['data-pjax' => 0]);
				},
			],
            [
                'attribute'=>'lesson_date',
                'format' => 'raw',
                'value' => function($data){
                 //   return Html::a(Yii::$app->formatter->asDate($data->lesson_date, 'dd-MM-Y'),['teacher/check-lesson', 'id' => $data->id], ['data-pjax' => 0]);
                    $attendance_control = EAttendanceControl::findOne(['_subject_schedule' => $data->id, '_employee' => $data->_employee]);
                    $color = $attendance_control ? "green" : "red";
                    return Html::a(Yii::$app->formatter->asDate($data->lesson_date, 'dd-MM-Y'),['teacher/check-lesson', 'id' => $data->id], ['data-pjax' => 0, 'style'=>'color:'.$color]);
                },
            ],
			[
                'attribute'=>'_subject',
            	'value' => 'subject.name',
			],
			[
                'attribute'=>'_training_type',
            	'value' => 'trainingType.name',
			],
			
			[
                'attribute'=>'_lesson_pair',
            	'value' => 'lessonPair.fullName',
			],
			[
                'attribute'=>'_auditorium',
            	'value' => 'auditorium.name',
			],
			[
                'attribute'=>'_employee',
            	'value' => 'employee.fullName',
			],

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
