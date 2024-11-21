<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\performance\estudentptt;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\archive\EStudentEmployment;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\archive\EStudentEmployment */
/* @var $dataProvider yii\data\ActiveDataProvider */

$disabled = false;
if ($this->_user()->role->code === \common\models\system\AdminRole::CODE_DEAN) {
    $disabled = true;
}
if ($department != "") {
    $searchModel->_department = $department;
}

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row" id="data-grid-filters">
            <div class="col col-md-4">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        __('Add'),
                        ['archive/employment-edit'],
                        ['class' => 'btn btn-flat btn-success', 'data-pjax' => 0]
                    ) ?>
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i> ' . __('Export to Excel'),
                        [
                            'archive/employment',
                            'education_year' => $searchModel->_education_year,
                            'download' => 1
                        ],
                        ['class' => 'btn btn-flat btn-success btn-primary', 'data-pjax' => 0]
                    ) ?>
                    <?/*= $this->getResourceLink(
                        '<i class="fa fa-download"></i> ' . __('Special Export'),
                        [
                            'archive/employment',
                            'education_year' => $searchModel->_education_year,
                            'export' => 1
                        ],
                        ['class' => 'btn btn-flat btn-success btn-primary', 'data-pjax' => 0]
                    ) */?>

                </div>
            </div>

            <div class="col col-md-8">
                    <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
            </div>

            <div class="col col-md-12">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_education_year',
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationTypeItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationFormItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_payment_form')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getPaymentFormItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                        <?/*= $form->field($searchModel, '_specialty')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSpecialtyItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); */?>
                    </div>

                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_employment_status')->widget(Select2Default::classname(), [
                            'data' => EStudentEmployment::getEmploymentStatusOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>


                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function (EStudentEmployment $data) {
                    return Html::a(sprintf('%s<p class="text-muted">%s / %s</p>',
                        $data->student,
                        $data->educationYear ? $data->educationYear->name : '',
                        $data->paymentForm ? $data->paymentForm->name : ''
                    ),
                        linkTo(['employment-edit', 'employment' => $data->id],
                        ['data-pjax' => 0])
                    );
                },
            ],
            [
                'format' => 'raw',
                'attribute'=>'_specialty',
                'value' => function (EStudentEmployment $data) {
                    return sprintf('%s<p class="text-muted">%s</p>',
                        @$data->specialty->mainSpecialty->code,
                        $data->group ? $data->group->name : ''
                    );
                },
            ],

            [
                'attribute'=>'_education_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s </p>",
                        @$data->educationType ? @$data->educationType->name : '',
                        @$data->educationForm ? @$data->educationForm->name : ''

                    );
                },
            ],

            [
                'attribute'=>'company_name',
                'value' => function ($data) {
                    return sprintf("%s",
                        $data->getShortCompanyName()
                    );
                },
            ],
            [
                'attribute'=>'_graduate_fields_type',
                'format' => 'raw',
                'value' => function (EStudentEmployment $data) {
                    return sprintf("%s<p class='text-muted'> %s </p>",
                        $data->graduateFieldsType ? $data->graduateFieldsType->name : '',
                        $data->workplace_compatibility ? EStudentEmployment::getWorkplaceCompatibilityStatusOptions()[$data->workplace_compatibility] : ''

                    );
                },
            ],

            [
                'attribute' => 'employment_doc_number',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s / %s<p class='text-muted'> %s</p>",
                        $data->employment_doc_number ? $data->employment_doc_number : '-',
                        $data->employment_doc_date ? Yii::$app->formatter->asDate($data->employment_doc_date, 'php:d.m.Y') : '-',
                        $data->_employment_status ? EStudentEmployment::getEmploymentStatusOptions()[$data->_employment_status] : '-');
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
