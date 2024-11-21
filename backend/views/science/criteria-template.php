<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\science\ECriteriaTemplate;
use common\models\science\EScientificPlatformCriteria;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use common\models\system\classifier\MethodicalPublicationType;
use common\models\system\classifier\ScientificPublicationType;
use common\models\system\classifier\PatientType;
use kartik\depdrop\DepDrop;
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-6">
                        <?//= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                    </div>
                    <div class="col col-md-6">

                        <?= $form->field($searchModel, '_publication_type_table')->widget(Select2Default::classname(), [
                            'data' => ECriteriaTemplate::getPublicationTypeOptions(),
                            'hideSearch' => true,
                            'allowClear' => true,
                            'options' => [
                                'id' => '_publication_type_table_search',
                            ],
                        ])->label(false); ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'toggleAttribute' => 'active',

                    'dataProvider' => $dataProvider,
                    'columns' => [
                        /*[
                            'attribute' => '_education_year',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->educationYear->name, ['science/publication-criteria', 'code' => $data->id], []);
                            },
                        ],*/
                        [
                            'attribute' => '_publication_type_table',
                            'value' => function ($data) {
                                return ECriteriaTemplate::getPublicationTypeOptions()[$data->_publication_type_table];
                            },
                        ],
                        [
                            'attribute' => '_publication_methodical_type',
                            'value' => function ($data) {
                                if($data->_publication_type_table == ECriteriaTemplate::PUBLICATION_TYPE_METHODICAL)
                                    return $data->publicationMethodicalType->name;
                                elseif($data->_publication_type_table == ECriteriaTemplate::PUBLICATION_TYPE_SCIENTIFIC)
                                    return $data->publicationScientificType->name;
                                elseif($data->_publication_type_table == ECriteriaTemplate::PUBLICATION_TYPE_PROPERTY)
                                    return $data->publicationPropertyType->name;
                                elseif($data->_publication_type_table == ECriteriaTemplate::PUBLICATION_TYPE_ACTIVITY)
                                    return $data->scientificPlatform->name;
                            },
                        ],

                        '_in_publication_database:boolean',
                        'exist_certificate:boolean',
                        [
                            'attribute' => '_criteria_type',
                            'value' => function ($data) {
                                if (@$data->_criteria_type)
                                    return EScientificPlatformCriteria::getCriteriaTypeOptions()[@$data->_criteria_type];
                            },
                        ],
                        'mark_value',

                    ],
                ]); ?>
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
