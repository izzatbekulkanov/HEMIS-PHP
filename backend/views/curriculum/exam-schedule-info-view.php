<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\student\EStudentMeta;
use common\models\structure\EDepartment;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\depdrop\DepDrop;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use common\models\system\AdminRole;
use common\models\curriculum\EStudentSubject;

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
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getFaculties(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => (!empty($faculty)),
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?php $faculty = $this->_user()->role->code == AdminRole::CODE_DEAN ? Yii::$app->user->identity->employee->deanFaculties->id : ""; ?>
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::class, [
                            'data' => $faculty ? ECurriculum::getOptions($faculty) : ECurriculum::getOptions($searchModel->_department),
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_curriculum_search',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),
                            'options' => [
                                'id' => '_education_year_search',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-3">
                        <?php
                        $semesters = array();
                        if($searchModel->_curriculum && $searchModel->_education_year){
                            $semesters = Semester::getByCurriculumYear($searchModel->_curriculum, $searchModel->_education_year);
                        }
                        ?>
                        <?= $form->field($searchModel, '_semester')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($semesters, 'code','name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,

                            'select2Options'=>['pluginOptions'=>['allowClear'=>true,'required' => true ], 'theme' => Select2::THEME_DEFAULT, ],
                            'options' => [
                                'id' => '_semester_search',
                                'placeholder' => __('-Choose-'),
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'depends'=>['_curriculum_search', '_education_year_search'],
                                'url'=>Url::to(['/ajax/get-semester-years']),

                            ],
                        ])->label(false);?>
                    </div>
                    <div class="col col-md-6">
                        <?php
                        $groups = array();
                        if($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semester){
                            $groups = EStudentMeta::getContingentByCurriculumSemester($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semester);
                        }
                        ?>
                        <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($groups, '_group','group.name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_group_search',
                                'placeholder' => __('-Choose-'),
                                'required' => true
                            ],

                            'pluginOptions' => [
                                'depends'=>['_curriculum_search', '_education_year_search', '_semester_search'],
                                'url'=>Url::to(['/ajax/get-group-semesters']),
                                'required' => true
                            ],
                        ])->label(false);?>
                    </div>


                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    //'toggleAttribute' => 'active',

                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute'=>'_curriculum',
                            'value' => 'curriculum.name',
                        ],
                        [
                            'attribute'=>'_education_year',
                            'value' => 'educationYear.name',
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                        ],
                        [
                            'attribute'=>'_semester',
                            'value' => function ($data) {
                                return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                            },
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                        ],
                        [
                            'attribute'=>'_group',
                            'format' => 'raw',
                            'contentOptions' => [
                                'class' => 'nowrap'
                            ],
                            'value' => function ($data) {
                                return Html::a($data->group->name, ['curriculum/exam-schedule-view',
                                    'FilterForm[_curriculum]' => $data->_curriculum,
                                    'FilterForm[_education_year]'=>$data->_education_year,
                                    'FilterForm[_semester]' => $data->_semester,
                                    'FilterForm[_group]' => $data->_group,
                                ], []);
                            },

                        ],
                        /* [
                             'header' => __('Count of Weeks'),
                             'attribute'=>'count_lesson',
                             'value' => 'count_lesson',
                             'value' => function ($data) {
                                 return ECurriculumWeek::getWeekCountByCurriculum($data->_curriculum, $data->_semester);
                             },
                         ],*/
                        [
                            'attribute'=>'count_lesson',
                            'header' => __('Subjects [midterm, final]'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                $result="";
                                $curriculum_subjects = EStudentSubject::getSubjectByCurriculumSemesterGroup($data->_curriculum, $data->_education_year, $data->_semester, $data->_group);
                                foreach ($curriculum_subjects as $key=>$item){
                                    $lessons = ESubjectExamSchedule::getExamByCurriculumSubject($item->_subject, $data->_curriculum, $data->_semester, $data->_group);
                                    if($lessons > 0)
                                        $result .= '<span class="badge bg-green"> №'. ($item->subject->name). ' ['.$lessons.'] '. '  '."</span>";
                                    else
                                        $result .= '<span class="badge bg-red"> №'. ($item->subject->name). ' ['.$lessons.'] '. '  '."</span>";
                                }
                                return $result;
                            },

                        ],
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

<?php Pjax::end() ?>
