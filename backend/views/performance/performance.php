<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectExamSchedule */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Performance Performance');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <?php if ($this->_user()->role->code !== "teacher"){ ?>
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6">
                <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => ($faculty),
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                    'data' => ($searchModel->_department) ? $searchModel->getCurriculumItems($searchModel->_department) : [],
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => !($searchModel->_department),
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => ($searchModel->_curriculum) ? $searchModel->getGroupItems($user) : [],
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => !($searchModel->_curriculum),
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEducationYearItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>

            <div class="col col-md-3">
                <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
                    'data' => ($searchModel->_education_year && $searchModel->_curriculum) ? $searchModel->getSemesterItems() : [],
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => !($searchModel->_education_year && $searchModel->_curriculum),
                ])->label(false); ?>
            </div>

            <div class="col col-md-3">
                <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                    'data' => ($searchModel->_curriculum && $searchModel->_semester) ? $searchModel->getSubjectItems() : [],
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => !($searchModel->_curriculum && $searchModel->_semester),
                ])->label(false); ?>
            </div>
            <? /*<div class="col col-md-2">
                <?= $form->field($searchModel, '_exam_type')->widget(Select2Default::classname(), [
                    'data' => ($searchModel->_curriculum && $searchModel->_semester && $searchModel->_subject) ? $searchModel->getExamTypeItems() : [],
                    'allowClear' => true,
                    'hideSearch' => false,
                    'disabled' => !($searchModel->_curriculum && $searchModel->_semester && $searchModel->_subject),
                ])->label(false); ?>
            </div> */?>
            <?php ActiveForm::end(); ?>
        </div>
        <?php } ?>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
		'dataProvider' => $dataProvider,
        'columns' => [
			['class' => 'yii\grid\SerialColumn'],
			[
                'attribute'=>'_group',
                'format' => 'raw',
				'value' => function($data){
                    if($data->_exam_type == ExamType::EXAM_TYPE_FINAL || $data->_exam_type == ExamType::EXAM_TYPE_OVERALL)
                        return Html::a($data->group->name,['performance/rating-info', 'education_year' => $data->_education_year, 'semester' => $data->_semester, 'group' => $data->_group, 'subject' => $data->_subject, 'exam_type' => $data->_exam_type, 'final_exam_type' => $data->final_exam_type], ['data-pjax' => 0]);
                    else
                        return Html::a($data->group->name,['performance/rating-info-simple', 'education_year' => $data->_education_year, 'semester' => $data->_semester, 'group' => $data->_group, 'subject' => $data->_subject, 'exam_type' => $data->_exam_type, 'final_exam_type' => $data->final_exam_type], ['data-pjax' => 0]);
                },
			],
            [
                'attribute'=>'_subject',
                'format' => 'raw',
                'value' => function($data){
                    if($data->_exam_type == ExamType::EXAM_TYPE_FINAL || $data->_exam_type == ExamType::EXAM_TYPE_OVERALL)
                        return Html::a($data->subject->name,['performance/rating-info', 'education_year' => $data->_education_year, 'semester' => $data->_semester, 'group' => $data->_group, 'subject' => $data->_subject, 'exam_type' => $data->_exam_type, 'final_exam_type' => $data->final_exam_type], ['data-pjax' => 0]);
                    else
                        return Html::a($data->subject->name,['performance/rating-info-simple', 'education_year' => $data->_education_year, 'semester' => $data->_semester, 'group' => $data->_group, 'subject' => $data->_subject, 'exam_type' => $data->_exam_type, 'final_exam_type' => $data->final_exam_type], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute'=>'_exam_type',
                'value' => function($data){
                    return strtoupper(@$data->examType->name). ' | '.@$data->finalExamType->name;
                },

            ],
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
           /* [
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
