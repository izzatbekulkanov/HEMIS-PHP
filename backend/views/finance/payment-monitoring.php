<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use common\models\system\classifier\EducationForm;
use common\models\curriculum\EducationYear;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\finance\EStudentContractType;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use common\models\finance\EStudentContract;


/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">

        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-"></i> ' . __('Monitoring'),
                        ['finance/payment-monitoring-group'],
                        ['class' => 'btn btn-flat btn-primary', 'data-pjax' => 0]
                    ) ?>
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i> ' . __('Export to Excel'),
                        [
                            'finance/payment-monitoring',
                            'education_year' => $searchModel->_education_year,
                            'download' => 1
                        ],
                        ['class' => 'btn btn-flat btn-success btn-primary', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>

            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_education_year',
                    ]
                ])->label(false);; ?>
            </div>
            <div class="col col-md-7">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by student fullName / Pasport / PIN / Code / Contract Number')])->label(false) ?>
            </div>


            <?php ActiveForm::end(); ?>

        </div>

    </div>
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    '__class' => SerialColumn::class,
                ],

                [
                    'attribute' => '_student',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->student->fullName, $data->student->student_id_number);
                    },
                ],
                [
                    'attribute' => '_education_year',
                    'value' => 'educationYear.name',
                ],
                [
                    'attribute' => 'number',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'> %s</p>", $data->number, Yii::$app->formatter->asDate($data->date, 'php:d.m.Y'));
                    },
                ],
                [
                    'attribute' => 'summa',
                    'value' => function ($data) {
                        return $data->summa !==null ? Yii::$app->formatter->asCurrency($data->summa) : '-';
                    },
                ],
                [
                    'attribute' => 'id',
                    'header' => __('Paid Contract Fee'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Yii::$app->formatter->asCurrency(EStudentContract::getTotal($data->paidContractFee, 'summa'));
                    },
                ],
                [
                    'attribute' => 'id',
                    'header' => __('Contract Indebtedness'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        $color = "black";
                        if($data->different >= 0)
                            $color = "red";
                        elseif($data->different < 0)
                            $color = "blue";
                        if($data->different != 0){
                            return sprintf("%s<p class='text-muted'> %s</p>", Yii::$app->formatter->asCurrency(@$data->different), '<span style="color:'.$color.'">'.EStudentContract::getDifferentOptions()[@$data->different_status].'</span>');
                        } else if($data->different == 0) {
                            return '-';
                        }
                        else
                            return sprintf("%s<p class='text-muted'> %s</p>", $data->summa !== null ? Yii::$app->formatter->asCurrency($data->summa) : '-', '<span style="color:' . $color . '">' . EStudentContract::getDifferentOptions()[EStudentContract::DIFFERENT_DEBTOR_STATUS] . '</span>');

                    },
                ],
                [
                    'attribute' => 'id',
                    'header' => __('Insert Fee'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a('<i class="fa fa-plus-square-o" aria-hidden="true"></i> '.__('Insert'),
                            [
                                'finance/paid-contract-fee',
                                'contract' => $data->id,
                            ], ['class' => 'btn btn-default btn-block',]);
                    },
                ],

            ],
        ]
    ); ?>
</div>
<?php
$this->registerJs(
    '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
)
?>

<?php Pjax::end() ?>
