<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use common\models\system\classifier\SemestrType;
use common\models\science\EPublicationAuthorMeta;
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
                    <div class="col col-md-2">
                        <div class="form-group">
                            <?php
                                echo Html::a('<i class="fa fa-plus-circle"></i> ' . __('Import'), '#', [
                                    'class' => 'showModalButton btn btn-success btn-flat',
                                    'modal-class' => 'modal-lg',
                                    'title' =>  __('Import'),
                                    'value' => Url::current(['import' => 1]),
                                    'data-pjax' => 0
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="col col-md-5">
                        <?//= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => \common\models\curriculum\EducationYear::getEducationYears(),
                            'allowClear' => true,
                            'options' => [
                                'id' => '_education_year',
                            ]
                        ])->label(false);; ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($searchModel, '_publication_type_table')->widget(Select2Default::classname(), [
                            'data' => EPublicationAuthorMeta::getPublicationTypeOptions(),
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
                        [
                            'attribute' => '_education_year',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->educationYear->name, Url::current(['code' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => '_publication_type_table',
                            'value' => function ($data) {
                                return EPublicationAuthorMeta::getPublicationTypeOptions()[$data->_publication_type_table];
                            },
                        ],
                        [
                            'attribute' => '_publication_methodical_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $label = "";
                                if($data->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL)
                                    $label = $data->publicationMethodicalType->name;
                                elseif($data->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC)
                                    $label = $data->publicationScientificType->name;
                                elseif($data->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY)
                                    $label = $data->publicationPropertyType->name;

                                    return Html::a($label, Url::current(['code' => $data->id]), []);
                            },
                        ],

                        '_in_publication_database:boolean',
                        'exist_certificate:boolean',
                        'mark_value',
                        [
                            'attribute' => 'updated_at',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            },
                        ]
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

                <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                    'data' => \common\models\curriculum\EducationYear::getEducationYears(),
                    'hideSearch' => true,
                    'allowClear' => false,
                ]) ?>
                <?= $form->field($model, '_publication_type_table')->widget(Select2Default::classname(), [
                    'data' => EPublicationAuthorMeta::getPublicationTypeOptions(),
                    'hideSearch' => true,
                    'allowClear' => false,
                    'options' => [
                        'id' => '_publication_type_table',
                    ],
                ]) ?>
                <?php
                $publications = array();
                if ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
                    $publications = MethodicalPublicationType::getClassifierOptions();
                } elseif ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
                    $publications = ScientificPublicationType::getClassifierOptions();
                } elseif ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
                    $publications = PatientType::getClassifierOptions();
                }
                ?>
                <?= $form->field($model, '_publication_methodical_type')->widget(DepDrop::classname(), [
                    'data' => $publications,
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options' => ['pluginOptions' => ['allowClear' => true, 'required' => true,], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'id' => '_publication_methodical_type',
                        'placeholder' => __('-Choose-'),
                        'required' => true,
                    ],
                    'pluginOptions' => [
                        'depends' => ['_publication_type_table'],
                        'required' => true,
                        'url' => Url::to(['/ajax/get-publication-types']),
                    ],
                ])->label(__('Publication Type')); ?>
                <?= $form->field($model, '_in_publication_database')->checkbox(['class' => 'icheckbox']) ?>
                <?= $form->field($model, 'exist_certificate')->checkbox(['class' => 'icheckbox']) ?>
                <?= $form->field($model, 'mark_value')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['science/publication-criteria'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['science/publication-criteria', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
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
