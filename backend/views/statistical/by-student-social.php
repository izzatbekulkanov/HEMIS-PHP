<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\SocialCategory;
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
use common\models\curriculum\Semester;

//$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['url' => ['curriculum/subject'], 'label' => __('List Subject')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>


<div class="row">

    <div class="col col-md-12 col-lg-3" id="sidebar">
        <?php $form = ActiveForm::begin(['method' => 'get', 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>

        <div class="box box-default ">
            <div class="box-body">


                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),
                    'disabled' => true,
                    'readonly' => true,
                    'options' => [
                        'id' => '_education_year_search',
                        'required' => true,
                    ]
                ])->label(false); ?>
                <?= $form->field($searchModel, '_semester_type')->widget(Select2Default::class, [
                    'data' => SemestrType::getClassifierOptions(),
                    'disabled' => true,
                    'readonly' => true,
                    'options' => [
                        'id' => '_semester_type_search',
                        'required' => true
                    ]
                ])->label(false); ?>
                <?= $form->field($searchModel, '_faculty')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'allowClear' => true,
                    'disabled' => $this->_user()->role->isDeanOrTutorRole(),
                    'options' => [
                        'id' => '_faculty_search',
                    ],
                ])->label(false) ?>
                <?= $form->field($searchModel, '_social_category')->widget(Select2Default::class, [
                    'data' => SocialCategory::getClassifierOptions(),
                    'allowClear' => false,
                    'options' => [
                        'id' => '_social_category_search',
                        'required' => true
                    ]
                ])->label(false) ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-9 col-lg-9">
        <div class="box box-default ">
            <div class="box-body no-padding">
                <?php if($searchModel->_education_year && $searchModel->_semester_type && $searchModel->_social_category): ?>
                    <br>
                    <div class="col-md-12">
                        <div class="col col-md-4">
                            <p><i><?php echo __('Year');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo EducationYear::findOne($searchModel->_education_year)->name;?></b></p>
                        </div>
                        <div class="col col-md-4">
                            <p><i><?php echo __('Semestr Type');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo SemestrType::findOne($searchModel->_semester_type)->name;?></b></p>
                        </div>
                        <div class="col col-md-4">
                            <p><i><?php echo __('Social Category');?></i>: &nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo SocialCategory::findOne($searchModel->_social_category)->name;?></b></p>
                        </div>
                    </div>

                    <br/>

                    <?= GridView::widget([
                        'id' => 'data-grid',
                        'showFooter' => true,
                        'footerRowOptions'=>['style'=>'font-weight:bold;',],
                        //'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'e_student.second_name',
                                'value' => function ($data) {
                                    return $data->student->fullName;
                                },
                                'label' => __('Student'),
                            ],
                            [
                                'attribute' => '_department',
                                'value' => function ($data) {
                                    return $data->department ? $data->department->name : "";
                                },
                                'label' => __('Structure Faculty'),
                            ],
                            [
                                'attribute' => '_education_type',
                                'value' => function ($data) {
                                    return $data->educationType ? $data->educationType->name : "";
                                },
                                'label' => __('Education Type'),
                            ],
                            [
                                'attribute' => '_education_form',
                                'value' => function ($data) {
                                    return $data->educationForm ? $data->educationForm->name : "";
                                },
                                'label' => __('Education Form'),
                            ],
                            [
                                'attribute' => '_semestr',
                                'value' => function ($data) {
                                    if(Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr) != null)
                                        return Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr)->name;
                                    elseif($data->semester)
                                        return $data->semester->name;
                                    else
                                        return \common\models\system\classifier\Semester::findOne($data->_semestr)->name;
                                },
                            ],
                        ],
                    ]); ?>
                <?php endif; ?>


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
