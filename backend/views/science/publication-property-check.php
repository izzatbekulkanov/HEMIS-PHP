<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\science\EPublicationAuthorMeta;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\PatientType;
use backend\widgets\Select2Default;
/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

    <div class="box box-default ">
        <div class="box-header bg-gray">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-md-3">
                    <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                        'data' => EducationYear::getEducationYears(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'placeholder' => __('Education Year'),
                        'options' => [
                            // 'prompt' => __('Education Year'),
                        ],
                    ])->label(false); ?>

                </div>
                <div class="col col-md-3">
                    <?= $form->field($searchModel, '_patient_type')->widget(Select2Default::classname(), [
                        'data' =>PatientType::getClassifierOptions(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'placeholder' => __('Patient Type'),
                    ])->label(false); ?>

                </div>
                <div class="col col-md-6">
                    <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?= GridView::widget([
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'publicationProperty.name',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a($data->publicationProperty->name, '#', [
                            'class' => 'showModalButton ',
                            'modal-class' => 'modal-lg',
                            'title' => $data->publicationProperty->name,
                            'value' => Url::to(['science/publication-property-check',
                                'id' => $data->_publication_property
                            ]),
                            'data-pjax' => 0
                        ]);
                    },
                ],
                [
                    'attribute' => 'publicationProperty.authors',
                    'value' => function ($data) {
                        return $data->publicationProperty->authors;
                    },
                ],
                [
                    'attribute' => 'publicationProperty.numbers',
                    'value' => function ($data) {
                        return $data->publicationProperty->numbers;
                    },
                ],
                [
                    'attribute' => 'publicationProperty._patient_type',
                    'value' => function ($data) {
                        return $data->publicationProperty->patientType->name;
                    },
                    //'header' => __('Structure Department'),
                ],
                [
                    'attribute' => '_employee',
                    'value' => function ($data) {
                        return $data->employee->fullName;
                    },
                ],
                [
                    'attribute' => 'publicationProperty.filename',
                    'format' => 'raw',
                    'value' => function ($data) {
                        if ($data->publicationProperty->filename) {
                            return Html::a($data->publicationProperty->filename['name'], $data->publicationProperty->filename['base_url'] . '/' . $data->publicationProperty->filename['path'], ['target'=>'_blank', 'data-pjax'=>0]);
                        }
                    },
                ],
                [
                    'attribute' => 'is_checked_by_author',
                    'header' => __('Is Checked By Author'),
                    'format' => 'raw',
                    'value' => function (EPublicationAuthorMeta $data) {
                        $color = ($data->is_checked_by_author === EPublicationAuthorMeta::STATUS_ENABLE ? "green" : "red");
                        return '<span class="text text-'.$color.'">'.$data->aprovedAuthorOptions[$data->is_checked_by_author].'</span>';
                    },
                    // 'value' => 'employee.fullName',
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                    },
                ],
                [
                    'attribute' => 'publicationProperty.is_checked',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return CheckBo::widget([
                            'type' => 'switch',
                            'options' => [
                                'onclick' => "changeAttribute('$data->_publication_property', 'is_checked')",
                            ],
                            'name' => $data->_publication_property,
                            'value' => $data->publicationProperty->is_checked,
                        ]);
                    },
                ],
            ],
        ]); ?>
    </div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
    <script>
        function changeAttribute(id, att) {
            var data = {};
            data.publication = id;
            data.attribute = att;
            $.get('<?= Url::to(['science/publication-property-check'])?>', data, function (resp) {

            })
        }
    </script>
<?php Pjax::end() ?>