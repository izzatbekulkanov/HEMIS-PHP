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
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Get Reference'),
                        ['student/reference', 'get'=>1],
                        [
                            'class' => 'btn btn-success btn-flat btn-delete',
                            'data-message' => __('Do you really get a reference?'),
                            // 'class' => 'btn btn-flat btn-success ',
                             'data-pjax' => 0
                        ]
                    ) ?>

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
                'attribute' => 'reference_number',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf(
                        "%s",
                        $data->reference_number ? $data->reference_number : '-'
                    );
                },
            ],
            [
                'attribute' => 'reference_date',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf(
                        "%s",
                        $data->reference_date ? Yii::$app->formatter->asDate($data->reference_date->getTimestamp()) : '-'
                    );
                },
            ],
            [
                'attribute' => '_education_year',
                'value' => 'educationYear.name',
            ],

            [
                'attribute' => '_level',
                'value' => 'level.name',
            ],
            [
                'attribute' => '_semester',
                'value' => 'semester.name',
            ],
            [
                'attribute' => 'filename',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(
                        '<i class="fa fa-download"></i>',
                        linkTo(['reference', 'file' => $data->id]),
                        [
                            'class' => 'btn btn-default btn-flat',
                            'data-pjax' => 0
                        ]);
                },
             ]

/*
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
             ],*/
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
