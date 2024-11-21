<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculum;
use common\models\structure\EDepartment;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\EducationType;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\depdrop\DepDrop;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

//$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['url' => ['curriculum/subject'], 'label' => __('List Subject')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-12" id="sidebar">
        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
                    <?//php $form = ActiveForm::begin(); ?>
                    <?php $form = ActiveForm::begin(['method' => 'get', 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>

                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),
                            'disabled' => true,
                            'readonly' => true,
                            'options' => [
                                'id' => '_education_year_search',
                                'required' => true,
                            ]
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_semester_type')->widget(Select2Default::class, [
                            'data' => SemestrType::getClassifierOptions(),
                            'disabled' => true,
                            'readonly' => true,
                            'options' => [
                                'id' => '_semester_type',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::class, [
                            'data' => EducationType::getHighers(),
                            'options' => [
                                'id' => '_education_type',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-5">
                        <?= $form->field($searchModel, '_faculty')->widget(Select2Default::class, [
                            'data' => EDepartment::getFaculties(),
                            'disabled' => $faculty != null,
                            'options' => [
                                'id' => '_faculty_search',
                                'required' => true
                            ]
                        ])->label(false) ?>
                    </div>

                    <div class="col col-md-1">
                        <div class="form-group">
                            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
                        </div>
                    </div>


					<?php ActiveForm::end(); ?>
				</div>
			</div>
	
            <div class="box-body no-padding">
            <?php if(isset($dataProvider)) : ?>
                <?= GridView::widget([
                    'id' => 'data-grid',

                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute'=>'_student',
                            'value' => 'student.fullName',
                        ],


                        [
                            'attribute'=>'_specialty_id',
                            'header' => __('Specialty'),
                            'value' => 'student.studentMeta.specialty.code',
                        ],
                        [
                            'attribute'=>'_education_form',
                            'header' => __('Education Form'),
                            'value' => 'student.studentMeta.educationForm.name',
                        ],
                        [
                            'attribute'=>'_group',
                            'header' => __('Group'),
                            'value' => 'student.studentMeta.group.name',
                        ],
                        [
                            'attribute'=>'_semester',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if(Semester::getByCurriculumSemester($data->student->studentMeta->_curriculum, $data->_semester) != null)
                                    $semester  = Semester::getByCurriculumSemester($data->student->studentMeta->_curriculum, $data->_semester)->name;
                                elseif($data->semester)
                                    $semester =  $data->semester->name;
                                else
                                    $semester = \common\models\system\classifier\Semester::findOne($data->_semester)->name;

                                return sprintf("%s<p class='text-muted'> </p>", $semester);

                   // return Semester::getByCurriculumSemester($data->student->studentMeta->_curriculum, $data->_semester)->name;
                            },
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                        ],
                        [
                            'attribute'=>'summary',
                            'header'=>__('Summary'),
                            'value' => function ($data) {
                                return $data->summary;
                            },
                        ],
                        [
                            'attribute'=>'absent_on',
                            'value' => function ($data) {
                                return $data->absent_on;
                            },
                        ],
                        [
                            'attribute'=>'absent_off',
                            'value' => function ($data) {
                                return $data->absent_off;
                            },
                        ],

		            ],
                ]); ?>
                <?php endif;?>
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

<?php Pjax::end() ?>
