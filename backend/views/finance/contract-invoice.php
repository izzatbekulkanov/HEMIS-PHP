<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\curriculum\ECurriculum;
use common\models\finance\EStudentContractInvoice;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\finance\EStudentContractInvoice */
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
        <div class="row">
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        __('Add'),
                        ['finance/contract-invoice-edit'],
                        ['class' => 'btn btn-flat btn-success btn-block ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-10">
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
                        <?= $form->field($searchModel, '_specialty')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSpecialtyItems(),
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
                'value' => function (EStudentContractInvoice $data) {
                    return Html::a(
                        sprintf("%s<p class='text-muted'> %s / %s</p>",
                            $data->student->getFullName(),
                            $data->student->student_id_number,
                            $data->educationForm->name
                        ),
                        //linkTo(['contract-invoice-edit', 'invoice' => $data->id], ['data-pjax' => 0]));
                        linkTo(['contract-invoice-edit', 'contract' => $data->_student_contract], ['data-pjax' => 0]));
                },
            ],

            [
                'attribute' => '_specialty',
                'format' => 'raw',
                'value' => function (EStudentContractInvoice $data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->educationType->name, $data->specialty->code);
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
                'value' => function (EStudentContractInvoice $data) {
                    return sprintf("%s / %s <p class='text-muted'>%s</p>", $data->studentContract->number, Yii::$app->formatter->asDate($data->studentContract->date, 'php:d.m.Y'), $data->studentContract->summa !==null ? Yii::$app->formatter->asCurrency($data->studentContract->summa) : '-');
                },
            ],
            [
                'attribute' => 'invoice_number',
                'format' => 'raw',
                'value' => function (EStudentContractInvoice $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->invoice_number, Yii::$app->formatter->asDate($data->invoice_date->getTimestamp()));
                },
            ],
            [
                'format' => 'raw',
                'value' => function (EStudentContractInvoice $data) {
                    //return Html::a(__('View'), linkTo(['contract-invoice-edit', 'invoice' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                    return Html::a(__('View'), linkTo(['contract-invoice-edit', 'contract' => $data->_student_contract]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
