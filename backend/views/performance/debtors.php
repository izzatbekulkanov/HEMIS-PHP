<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculum;
use common\models\structure\EDepartment;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\student\EStudentMeta;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationWeekType;
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
<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

            <div class="box box-default ">
                <div class="box-header bg-gray">
                    <?php $form = ActiveForm::begin(['method' => 'get', 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
                    <div class="row" id="data-grid-filters">
                            <div class="col col-md-2">
                                <div class="form-group">
                                    <?= $this->getResourceLink(
                                        '<i class="fa fa-download"></i> ' . __('Export to Excel'),
                                        [
                                            'performance/debtors',
                                            'FilterForm[_faculty]' => $searchModel->_faculty,
                                            'FilterForm[_curriculum]' => $searchModel->_curriculum,
                                            'FilterForm[_education_year]' => $searchModel->_education_year,
                                            'FilterForm[_semester]' => $searchModel->_semester,
                                            'FilterForm[_group]' => $searchModel->_group,
                                            'download' => 1
                                        ],
                                        ['class' => 'btn btn-flat btn-success btn-primary', 'data-pjax' => 0]
                                    ) ?>
                                </div>
                            </div>
                            <div class="col col-md-4">
                                <?= $form->field($searchModel, '_faculty')->widget(Select2Default::classname(), [
                                    'data' => EDepartment::getFaculties(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'disabled' => (!empty($faculty)),
                                ])->label(false); ?>
                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                                    'data' => ($faculty) ? ECurriculum::getOptions($faculty) : ECurriculum::getOptions($searchModel->_faculty) ,
                                   // 'allowClear' => true,
                                    'hideSearch' => false,
                                   // 'disabled' => !($searchModel->_faculty),
                                    'options' => [
                                        'id' => '_curriculum_search',
                                        'required' => true
                                    ]
                                ])->label(false); ?>
                            </div>
                    </div>
                    <div class="row" id="data-grid-filters">
                        <div class="col col-md-2">
                        </div>
                        <div class="col col-md-2">
                                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                                    'data' => EducationYear::getEducationYears(),

                                    'options' => [
                                        'id' => '_education_year_search',
                                        'required' => true,
                                    ]
                                ])->label(false); ?>
                            </div>
                            <div class="col col-md-2">
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
                                    'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                                    'options' => [
                                        'id' => '_semester_search',
                                        'placeholder' => __('-Choose Semester-'),
                                        'required' => true
                                    ],
                                    'pluginOptions' => [
                                        'depends'=>['_curriculum_search', '_education_year_search'],
                                        'url'=>Url::to(['/ajax/get-semester-years']),
                                        'required' => true
                                    ],
                                ])->label(false);?>
                            </div>
                            <div class="col col-md-6">
                                <?php
                                $groups = array();
                                if($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semester){
                                    $groups = EStudentMeta::getRealContingentByCurriculumSemester($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semester);
                                }
                                ?>
                                <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                                    'data' => ArrayHelper::map($groups, '_group','group.name'),
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options'=>['pluginOptions'=>['allowClear'=>true], 'theme' => Select2::THEME_DEFAULT],
                                    'options' => [
                                        'id' => '_group_search',
                                        'placeholder' => __('-Choose Group-'),
                                        //   'required' => true
                                    ],

                                    'pluginOptions' => [
                                        'depends'=>['_curriculum_search', '_education_year_search', '_semester_search'],
                                        'url'=>Url::to(['/ajax/get-group-semesters']),
                                        //'required' => true
                                    ],
                                ])->label(false);?>
                            </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>


                <?//php if($searchModel->_group): ?>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'showFooter' => true,
                    'footerRowOptions'=>['style'=>'font-weight:bold;',],
                    //'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'name',
                            'label' => __('Student'),
                           // 'footer' => __('Summary'),
                        ],
                        [
                            'attribute' => 'group',
                            'label' => __('Group'),
                            //'footer' => EPublicationAuthorMeta::getTotal($dataProvider->models, 'mark'),

                        ],
                        [
                            'attribute' => 'education_year',
                            'label' => __('Education Year'),
                        ],
                        [
                            'attribute' => 'semester',
                            'label' => __('Semester'),
                        ],
                        [
                            'attribute' => 'subject',
                            'label' => __('Subject Name'),
                        ],

                        /*[
                            'attribute' => '_education_year',
                            'value' => function ($data) {
                                return $data->educationYear->name;
                            },
                        ],*/




                    ],
                ]); ?>
                <?//php endif; ?>
                <? /*
                <?php if(isset($debtor_list)) : ?>
                    <div class="table-responsive">

                        <table class="table table-bordered">
                            <tr>
                                <th style="text-align:center; vertical-align:middle;"><?= __('№');?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= __('Fullname of Student');?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= __('Group');?></th>

                                <th style="text-align:center; vertical-align:middle;"><?= __('Subject');?></th>
                               
                            </tr>

                            <?php
                            $i=1;
                            $s1=0;
                            $sz1=0;
                            foreach($debtor_list as $item){
                                foreach($item as $item2){?>
                                    <tr>
                                        <td><?php echo $i++;?></td>
                                        <td><?php echo @$item2['name'];?></td>
                                        <td><?php echo @$item2['group'];?></td>

                                        <td><?php echo @$item2['subject'];?></td>
                                    </tr>
                                  <?php } ?>
                            <?php } ?>

                        </table>

                    </div>
                <?php endif;?>*/ ?>

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
