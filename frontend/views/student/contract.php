<?php

use common\models\finance\EStudentContractType;
use common\models\finance\EStudentContract;
use common\models\curriculum\EducationYear;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = __('Student Contract');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?php
                    echo Html::a(__("To Order"), '#', [
                        'class' => 'showModalButton btn btn-success btn-flat',
                        'modal-class' => 'modal-small',
                        'title' => __("To Order"),
                        'value' => Url::current(['set' => 1]),
                        'data-pjax' => 1
                    ]);
                    ?>
                </div>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <? //= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Number')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'mobile' => true,
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::className()
            ],
            [
                'attribute' => '_education_year',
                'value' => 'educationYear.name',
            ],
            [
                'attribute' => '_contract_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s / %s </p>", $data->contractType ? $data->contractType->name : '', EStudentContractType::getContractFormOptions()[@$data->contract_form_type], $data->contractSummaType ? $data->contractSummaType->name : '');
                },
            ],
        //    'number',
            [
                'attribute' => 'number',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf(
                            "%s / %s <p class='text-muted'> %s</p>",
                            $data->number ? $data->number : '-',
                               $data->date ? Yii::$app->formatter->asDate($data->date->getTimestamp()) : '-',
                                $data->summa !== null ? Yii::$app->formatter->asCurrency($data->summa) : '-'
                    );
                },
            ],
            /*[
                'attribute' => 'summa',
                'value' => function ($data) {
                    return $data->summa !== null ? Yii::$app->formatter->asCurrency($data->summa) : '-';
                },
            ],*/
            [
                'attribute' => 'id',
                'header' => __('Paid Contract Fee'),
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->contract_status === EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED)
                        return Yii::$app->formatter->asCurrency(EStudentContract::getTotal($data->paidContractFee, 'summa'));
                    else
                        return '-';
                },
            ],
            [
                'attribute' => 'contract_status',
                'format' => 'raw',
                'value' => function ($data) {
                    $color = "black";
                    if ($data->contract_status == EStudentContractType::CONTRACT_REQUEST_STATUS_SEND)
                        $color = "red";
                    elseif ($data->contract_status == EStudentContractType::CONTRACT_REQUEST_STATUS_PROCESS)
                        $color = "blue";
                    elseif ($data->contract_status == EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED)
                        $color = "green";
                    return '<span style="color:' . $color . '">' . EStudentContractType::getContractStatusOptions()[@$data->contract_status] . '</span>';
                },
            ],
            [
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->contract_status == EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED)
                        return Html::a("<i class='fa fa-download'></i> " . __('Download'), ['student/contract', 'file' => $data->id], ['data-pjax' => 0]);
                    else
                        return '-';
                },
            ],
            [
                'format' => 'raw',
                'header' => __('Ready Invoices'),
                'value' => function ($data) {
                    $res = "";
                    foreach ($data->contractInvoice as $item){
                        $res .= Html::a("<i class='fa fa-download'></i> " . $item->invoice_number, ['student/contract', 'invoice' => $item->id], ['data-pjax' => 0]) .' &nbsp;&nbsp;';

                    }
                    return $res;
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
