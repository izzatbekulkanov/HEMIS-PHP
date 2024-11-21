<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\finance\EStudentContractInvoiceMeta;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\finance\EStudentContractInvoiceMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$disabled = false;
if ($this->_user()->role->code === \common\models\system\AdminRole::CODE_DEAN) {
    $disabled = true;
}
if ($department != "") {
    $searchModel->_department = $department;
}
$this->params['breadcrumbs'][] = ['url' => ['finance/contract-invoice'], 'label' => __('Finance Contract Invoice Edit')];
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
                        <?= $form->field($searchModel, '_level')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getLevelItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN / Contract Number')])->label(false); ?>
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
                        'value' => function (EStudentContractInvoiceMeta $data) {
                            return Html::a(
                                    sprintf('%s<p class="text-muted">%s</p>',
                                        $data->student->getFullName(),
                                        $data->student->student_id_number
                                    ),
                                    linkTo(['contract-invoice-edit', 'contract' => $data->id]), ['data-pjax' => 0]);
                        },
                    ],

                    [
                        'attribute' => '_education_type',
                        'format' => 'raw',
                        'value' => function (EStudentContractInvoiceMeta $data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                        },
                    ],
                    [
                        'attribute' => '_specialty',
                        'format' => 'raw',
                        'value' => function (EStudentContractInvoiceMeta $data) {
                            return sprintf('%s<p class="text-muted"></p>',  $data->specialty->code);
                        },
                    ],
                    [
                        'attribute' => '_education_year',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return sprintf("%s<p class='text-muted'> %s</p>", $data->educationYear->name, @$data->level->name);
                        },
                    ],
                    [
                        'attribute' => '_group',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return sprintf('%s<p class="text-muted">%s</p>', $data->group->name, $data->department->name);
                        },
                    ],
                    [
                        'attribute' => '_student_contract',
                        'format' => 'raw',
                        'value' => function (EStudentContractInvoiceMeta $data) {
                            return sprintf("%s / %s<p class='text-muted'> %s </p>", $data->number, Yii::$app->formatter->asDate($data->date, 'php:d.m.Y'), $data->summa !==null ? Yii::$app->formatter->asCurrency($data->summa) : '-');
                        },
                    ],
                    [
                        'format' => 'raw',
                        'value' => function (EStudentContractInvoiceMeta $data) {
                            return Html::a(__('Add Invoice'), linkTo(['contract-invoice-edit', 'contract' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
<?php Pjax::end() ?>
