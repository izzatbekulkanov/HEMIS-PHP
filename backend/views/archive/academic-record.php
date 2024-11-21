<?php
use backend\widgets\DatePickerDefault;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\curriculum\EducationYear;
use common\models\performance\EPerformance;
use common\models\curriculum\Semester;
use backend\widgets\Select2Default;
use common\models\system\classifier\FinalExamType;
use common\models\curriculum\ESubjectExamSchedule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php if ($this->_user()->role->code !== "teacher"){ ?>
                    <div class="row" id="data-grid-filters">

                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                                //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_education_year', 'educationYear.name'),
                                //'data' => ArrayHelper::map($dataProvider->getModels(), '_education_year', 'educationYear.name'),
                               // 'data' => EducationYear::getEducationYears(),
                                'data' => $searchModel->getEducationYearItems(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-9">
                            <?php
                                /*if($faculty){
                                    $curriculums = $searchModel->_education_year ? EPerformance::find()->where(
                                        [
                                            'e_performance._education_year' => $searchModel->_education_year,
                                            'e_student_meta._department' => $faculty,
                                        ]
                                    )->select(['e_performance._education_year', 'e_performance._exam_schedule'])->distinct(true)->joinWith('studentMeta')->all()
                                        : [];
                                }
                                else{
                                    $curriculums = $searchModel->_education_year ? EPerformance::find()->where(
                                        ['e_performance._education_year' => $searchModel->_education_year]
                                    )->select(['e_performance._education_year', 'e_performance._exam_schedule'])->distinct(true)->joinWith('studentMeta')->all()
                                        : [];

                                }*/

                            ?>
                            <?= $form->field($searchModel, '_curriculum')->widget(
                                Select2Default::classname(),
                                [
                                   /* 'data' => ArrayHelper::map(
                                        $curriculums,
                                        'examSchedule._curriculum',
                                        'examSchedule.curriculum.name'
                                    ),*/
                                    'data' => $searchModel->getCurriculumItems(),
                                    'placeholder' => __('-Choose Curriculum-'),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    //'disabled' => empty($curriculums)
                                ]
                            )->label(false); ?>
                        </div>

                        <div class="col col-md-3">
                            <?php
                              /*  $semesters = ($searchModel->_education_year && $searchModel->_curriculum) ? EPerformance::find()->where(
                                [
                                        'e_performance._education_year' => $searchModel->_education_year,
                                        'e_student_meta._curriculum' => $searchModel->_curriculum,
                                ]
                            )->select(['e_performance._education_year', 'e_performance._semester', 'e_performance._exam_schedule'])->distinct(true)->joinWith('studentMeta')->orderBy('e_performance._semester')->all()
                                : [];
                                if(is_array($semesters)){
                                    $list = [];
                                    foreach ($semesters as $item){
                                        $list[$item->_semester] = Semester::getByCurriculumSemester($searchModel->_curriculum, $item->_semester)->name;
                                    }
                                }*/
                            ?>
                            <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
                                //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
//                                'data' => $list,
                                'data' => ($searchModel->_education_year && $searchModel->_curriculum) ? $searchModel->getSemesterItems($searchModel->_education_year, $searchModel->_curriculum) : [],
                                'allowClear' => true,
                                'hideSearch' => false,
                                'disabled' => !($searchModel->_education_year && $searchModel->_curriculum),
                               // 'disabled' => empty($semesters)
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?php
                            /*$subjects = ($searchModel->_education_year && $searchModel->_semester && $searchModel->_curriculum) ? EPerformance::find()->where(
                                [
                                        'e_performance._education_year' => $searchModel->_education_year,
                                        'e_performance._semester' => $searchModel->_semester,
                                        'e_student_meta._curriculum' => $searchModel->_curriculum,
                                ]
                            )->select(['e_performance._education_year', 'e_performance._subject'])->distinct(true)->joinWith('studentMeta')->all()
                                : [];*/
                            ?>
                            <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                                //'data' => ArrayHelper::map($dataProvider->getModels(), '_subject', 'subject.name'),
                                /*'data' => ArrayHelper::map(
                                    $subjects,
                                    '_subject',
                                    'subject.name'
                                ),*/
                                'data' => ($searchModel->_education_year && $searchModel->_curriculum && $searchModel->_semester) ? $searchModel->getSubjectItems($searchModel->_curriculum, $searchModel->_semester): [],
                                'allowClear' => true,
                                'hideSearch' => false,
                                'disabled' => !($searchModel->_education_year && $searchModel->_curriculum && $searchModel->_semester),
                               // 'disabled' => empty($subjects)
                            ])->label(false); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                        <div class="col col-md-3 ">
                            <div class="form-group pull-right">
                            <?= $this->getResourceLink('<i class="fa fa-eye"></i> ' . __('View'), ['archive/academic-record-view'], ['data-pjax' => 0, 'class' => 'btn btn-info btn-flat']) ?>
                            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Transfer'), [ 'class' => 'btn btn-primary btn-flat', 'id'=>'assign', 'onclick' => 'assignStudents()']) ?>
                            </div>
                        </div>

                    </div>
                <?php } ?>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    //'sticky' => '#sidebar',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                        ],
                        /*[
                            'attribute' => '_student',
                            'value' => 'student.fullName',
                            // 'enableSorting' => true,
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->student->fullName, ['archive/diploma', 'id' => $data->_student], ['data-pjax' => 0]);
                            },
                        ],*/
                        [
                            'attribute'=>'_student',
                            'value' => 'student.fullName',
                        ],
                        [
                            'attribute'=>'_education_year',
                            'value' => 'educationYear.name',
                        ],
                        [
                            'attribute'=>'_semester',
                            'value' => function ($data) {
                                if($data->studentMeta !== null && Semester::getByCurriculumSemester($data->studentMeta->_curriculum, $data->_semester) != null)
                                    return Semester::getByCurriculumSemester($data->studentMeta->_curriculum, $data->_semester)->name;
                                elseif($data->semester)
                                    return $data->semester->name;
                                else
                                    return \common\models\system\classifier\Semester::findOne($data->_semester)->name;
                            },
                        ],
                        [
                            'attribute'=>'_subject',
                            'value' => 'subject.name',
                        ],
                        [
                            'attribute'=>'grade',
                        ],

/*                        [
                            'attribute'=>'_subject',
                            'value' => function (EPerformance $data) {
                                return $data->grade;
                            //return $data->group ? '<b>'.$data->group->curriculum->markingSystem->name.'</b>' : '';
                            }

                        ],*/

                    ],
                ]); ?>
            </div>
        </div>
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

    function assignStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert(<?=json_encode([__('Talabalarni tanlang')])?>[0]);
        } else if (keys.length > 0) {
            $.post({
                url:  '/archive/to-record',
                data: {
                    selection: keys,
                },
                dataType: "json",
            });
        }
    }
</script>
<?php
/*$script = <<< JS
	$("#assign").click(function(){
		alert("fdsfsd");
	    var keys = $('#data-grid').yiiGridView('getSelectedRows');
		$.post({
           url:  '/archive/to-record',
           data: {selection: keys },
           dataType:"json",
        });
	});
JS;
$this->registerJs($script);*/
?>

<?php Pjax::end() ?>
