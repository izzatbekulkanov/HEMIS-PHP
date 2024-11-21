<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\archive\EStudentEmploymentMeta;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\student\EGroup;
use common\models\system\classifier\Course;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\archive\EStudentEmploymentMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */

$disabled = false;
if ($this->_user()->role->code === \common\models\system\AdminRole::CODE_DEAN) {
    $disabled = true;
}
if ($department != "") {
    $searchModel->_department = $department;
}
$this->params['breadcrumbs'][] = ['url' => ['archive/employment'], 'label' => __('Archive Employment Edit')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php $form = ActiveForm::begin(); ?>
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => true,
                            'hideSearch' => false,

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
                    </div>

                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'employment_registration')->widget(Select2Default::classname(), [
                            'data' =>EStudentEmploymentMeta::getRegistrationOptions(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <?= GridView::widget([
                'id' => 'data-grid',
                'sticky' => '#sidebar',
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_student',
                        'format' => 'raw',
                        'value' => function (EStudentEmploymentMeta $data) {
                            return Html::a(sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number), linkTo(['employment-edit', 'student' => $data->id]), ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => '_specialty_id',
                        'format' => 'raw',
                        'value' => function (EStudentEmploymentMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->specialty->mainSpecialty->code, $data->group->name);
                        },
                    ],
                    [
                        'attribute' => '_education_type',
                        'format' => 'raw',
                        'value' => function (EStudentEmploymentMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                        },
                    ],
                    [
                        'attribute'=>'_payment_form',
                        'format' => 'raw',
                        'value' => function (EStudentEmploymentMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->paymentForm->name, $data->educationYear->name);
                        },

                    ],
                    [
                        'attribute'=>'employment_registration',
                        'value' => function ($data) {
                            return @$data->getEmploymentRegistrationLabel();
                            //return @$data->registrationOptions[@$data->employment_registration];
                        },

                    ],
                    [
                        'format' => 'raw',
                        'value' => function (EStudentEmploymentMeta $data) {
                            return Html::a(__('Add Employment'), linkTo(['employment-edit', 'student' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<?php Pjax::end() ?>
