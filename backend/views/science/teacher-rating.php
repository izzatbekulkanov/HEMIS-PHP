<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\science\EPublicationAuthorMeta;
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

$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <?php $form = ActiveForm::begin(['method' => 'get', 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>

        <div class="box box-default ">

            <div class="box-body">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),

                    'options' => [
                        'id' => '_education_year_search',
                        'required' => true,
                    ]
                ])->label(false); ?>
                <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN && $this->_user()->role->code != AdminRole::CODE_DEPARTMENT) { ?>
                    <?= $form->field($searchModel, '_faculty')->widget(Select2Default::classname(), [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'options' => [
                            'id' => '_faculty',
                        ],
                    ])->label(false) ?>
                <?php } ?>

                <?php if ($this->_user()->role->code != AdminRole::CODE_DEPARTMENT) { ?>
                <?php
                    $departments = array();
                    if($faculty) {
                        $departments = EDepartment::getDepartmentList($faculty);
                    }
                ?>
                <?= $form->field($searchModel, '_department')->widget(DepDrop::classname(), [
                    'data' =>  ArrayHelper::map($departments, 'id','name'),
                    'language' => 'en',
                    'type' => DepDrop::TYPE_SELECT2,
                    'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'placeholder' => __('-Choose Department-'),
                        'id' => '_department',

                    ],
                    'pluginOptions' => [
                        'depends'=>['_faculty'],
                        'placeholder' => __('-Choose-'),
                        'url'=>Url::to(['/ajax/get-departments']),

                    ],
                ])->label(false)?>
                <?php } ?>

            </div>
            <div class="box-footer text-right">
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-body no-padding">
                <?php if($searchModel->_education_year): ?>

                    <?= GridView::widget([
                        'id' => 'data-grid',
                        //'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                        'dataProvider' => $dataProvider,
                        'showFooter' => true,
                        'footerRowOptions'=>['style'=>'font-weight:bold;',],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'employee',
                                'label' => __('Employee'),
                                'format' => 'raw',
                                'value' => function ($data) use ($searchModel) {
                                    return Html::a($data['employee'], '#', [
                                        'class' => 'showModalButton ',
                                        'modal-class' => 'modal-lg',
                                        'title' => $data['employee'],
                                        'value' => Url::to(['science/teacher-rating',
                                            'FilterForm[_education_year]' => $searchModel->_education_year,
                                            'FilterForm[_faculty]' => $searchModel->_faculty,
                                            'FilterForm[_department]' => $searchModel->_department,
                                            '_employee' => $data['_employee']
                                        ]),
                                        'data-pjax' => 0
                                    ]);
                                },
                                'footer' => __('Summary'),
                            ],
                            [
                                'attribute' => 'mark',
                                'label' => __('Mark'),
                                'footer' => EPublicationAuthorMeta::getTotal($dataProvider->models, 'mark'),

                            ],


                            /*[
                                'attribute' => '_education_year',
                                'value' => function ($data) {
                                    return $data->educationYear->name;
                                },
                            ],*/




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
