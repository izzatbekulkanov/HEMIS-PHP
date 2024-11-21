<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\science\EPublicationAuthorMeta;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\ScientificPublicationType;
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
                    ])->label(false); ?>

                </div>
                <div class="col col-md-3">
                    <?= $form->field($searchModel, '_scientific_publication_type')->widget(Select2Default::classname(), [
                        'data' => ScientificPublicationType::getClassifierOptions(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'placeholder' => __('Scientific Publication Type'),
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
                    'attribute' => 'publicationScientific.name',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a($data->publicationScientific->name, '#', [
                            'class' => 'showModalButton ',
                            'modal-class' => 'modal-lg',
                            'title' => $data->publicationScientific->name,
                            'value' => Url::to(['science/publication-scientifical-check',
                                'id' => $data->_publication_scientific
                            ]),
                            'data-pjax' => 0
                        ]);
                    },
                ],
                [
                    'attribute' => 'publicationScientific.authors',
                    'value' => function ($data) {
                        return $data->publicationScientific->authors;
                    },
                ],
                [
                    'attribute' => 'publicationScientific.issue_year',
                    'value' => function ($data) {
                        return $data->publicationScientific->issue_year;
                    },
                ],
                [
                    'attribute' => 'publicationScientific._scientific_publication_type',
                    'value' => function ($data) {
                        return $data->publicationScientific->scientificPublicationType->name;
                    },
                ],
                [
                    'attribute' => '_employee',
                    'value' => function ($data) {
                        return $data->employee->fullName;
                    },
                ],
                [
                    'attribute' => 'publicationScientific.filename',
                    'format' => 'raw',
                    'value' => function ($data) {
                        if ($data->publicationScientific->filename) {
                            return Html::a($data->publicationScientific->filename['name'], $data->publicationScientific->filename['base_url'] . '/' . $data->publicationScientific->filename['path'], ['target'=>'_blank', 'data-pjax'=>0]);
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
                    'attribute' => 'publicationScientific.is_checked',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return CheckBo::widget([
                            'type' => 'switch',
                            'options' => [
                                'onclick' => "changeAttribute('$data->_publication_scientific', 'is_checked')",
                            ],
                            'name' => $data->_publication_scientific,
                            'value' => $data->publicationScientific->is_checked,
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
            $.get('<?= Url::to(['science/publication-scientifical-check'])?>', data, function (resp) {

            })
        }
    </script>
<?php Pjax::end() ?>