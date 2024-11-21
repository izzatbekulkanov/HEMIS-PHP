<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\science\ECriteriaTemplate;
use common\models\science\EScientificPlatformCriteria;
use common\models\science\EPublicationAuthorMeta;
use common\models\curriculum\EducationYear;
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
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-6">
                        <?//= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                    </div>
                    <div class="col col-md-6">


                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',

                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                        ],
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

                        [
                            'attribute' => '_criteria_type',
                            'value' => function ($data) {
                                return EScientificPlatformCriteria::getCriteriaTypeOptions()[$data->_criteria_type];
                            },
                        ],
                        'mark_value',

                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <?php //echo $form->errorSummary($model)?>
            <div class="box-body">

                <?= $form->field($searchModelFix, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'hideSearch' => true,
                    'allowClear' => false,
                    'options' => [
                        'id' => '_education_year',
                    ],
                ]) ?>


            </div>
            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Assignment'), ['class' => 'btn btn-primary btn-flat', 'id' => 'assign', 'type' => 'button']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>

<?php
$script = <<< JS
	$("#assign").click(function(){
		var keys = $('#data-grid').yiiGridView('getSelectedRows');
		var _education_year =  $('#_education_year').val();
		if(keys.length&&_education_year)
		$.post({
           url: '/science/to-import-activity',
           data: {selection: keys, education_year: _education_year, '_csrf-backend':$('input[name=""]').val() },
           dataType:"json",
        });
	});
JS;
$this->registerJs($script);
?>

<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
