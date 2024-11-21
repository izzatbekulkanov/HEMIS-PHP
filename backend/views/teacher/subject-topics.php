<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\Semester;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\structure\EDepartment;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\system\classifier\TrainingType;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ECurriculumSubject */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::class, [
                    //'data' => ArrayHelper::map($dataProvider->getModels(), '_curriculum', 'curriculum.name'),
                    'data' => $searchModel->getCurriculumItems($department),
                    'placeholder' => __('-Choose Curriculum-'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false) ?>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => $searchModel->getEducationYearItems(),
                    'placeholder' => __('-Choose Education Year-'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false) ?>
            </div>

            <div class="col col-md-4">
                <?= $form->field($searchModel, '_semester')->widget(Select2Default::class, [
                    'data' => $searchModel->getSemesterItems(),
                    'placeholder' => __('-Choose Semester-'),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
	<?= GridView::widget([
        'id' => 'data-grid',
		'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => '_subject',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->subject->name, ['teacher/subject-topic-info', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_curriculum',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->curriculum->name, ['teacher/subject-topic-info', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_education_year',
                //'value' => 'semester.educationYear.name',
                'value' => function ($data) {
                    return
                        Semester::getByCurriculumSemester($data->_curriculum, $data->_semester) != null ?
                            (Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->educationYear ?
                                Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->educationYear->name : '')
                        : '';
                },
                'header' => __(	'Education Year'),
            ],
            [
                'attribute' => '_semester',
                'value' => function ($data) {
                    return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester) != null ?
                        Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name : '';
                },
            ],
			[
				'attribute'=>'_education_type',
				'value' => 'curriculum.educationType.name',
                'header' => __(	'Education Type'),
			],

            [
                'attribute'=>'reorder',
                'value' => function ($data) {
                    return $data->acceptedOptions[!$data->reorder];
                },
            ],
            [
                'attribute' => '_department',
                'value' => 'department.name',
                'visible' => $this->_user()->role->code != AdminRole::CODE_DEPARTMENT,
            ],
            [
                'attribute'=>'_employee',
                'visible' => $this->_user()->role->code == AdminRole::CODE_DEPARTMENT,
                'format' => 'raw',
                'value' => function ($data) {
	                $employee = $data->employee ? $data->employee->fullName : "Not Set";
                    return Html::a($employee, '#', [
                        'class' => 'showModalButton ',
                        'modal-class' => 'modal-lg',
                        'title' => $data->subject->name,
                        'value' => Url::to(['teacher/subject-topics',
                            'code' => $data->id
                        ]),
                        'data-pjax' => 0
                    ]);
                },
            ],
            [
                //'attribute'=>'',
                'header' => __('Trainings'),
                'format' => 'raw',
                'value' => function ($data) {

                    $result="";
                    $trainings = ECurriculumSubjectDetail::getTrainingByCurriculumSemesterSubject($data->_curriculum, $data->_semester, $data->_subject);
                    foreach ($trainings as $key=>$item){
                        //$result .= $item->trainingType->name;
                        if($item->_training_type != TrainingType::TRAINING_TYPE_INDEPENDENT) {
                            if ($item->academic_load <= (count($item->trainingTopics) * 2))
                                $result .= '<span class="badge bg-green"> ' . $item->trainingType->name . ' [' . $item->academic_load . ' / ' . (count($item->trainingTopics) * 2) . ']' . "</span>";
                            else
                                $result .= '<span class="badge bg-red"> ' . $item->trainingType->name . ' [' . $item->academic_load . ' / ' . (count($item->trainingTopics) * 2) . ']' . "</span>";
                        }
                    }
                    return $result;
                },

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
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['curriculum/curriculum'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>
