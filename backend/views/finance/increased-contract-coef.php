<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use backend\widgets\DatePickerDefault;
use common\models\system\classifier\StipendRate;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\EducationType;

use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
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
                        <?= $form->field($searchModel, '_department')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'placeholder' => __('-Choose Faculty-'),
                            ]
                        )->label(false); ?>
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
                            'attribute' => '_department',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->department->name, ['finance/increased-contract-coef', 'code' => $data->id], []);
                            },
                        ],
                        [
                            'attribute' => '_specialty',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a(sprintf("%s<p class='text-muted'></p>", $data->specialty->fullName), ['finance/increased-contract-coef', 'code' => $data->id], []);
                            },
                        ],
                        /*[
                            'attribute' => '_education_year',
                            'value' => 'educationYear.name',
                        ],*/
                        [
                            'attribute' => 'coefficient',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->coefficient, ['finance/increased-contract-coef', 'code' => $data->id], []);
                            },
                        ],

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
                <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'allowClear' => false,
                    'hideSearch' => false,
                  //  'disabled' => $faculty != null,
                    'options' => [
                        'id' => '_department',

                    ],
                ]) ?>
                <?php
                $specialties = array();
                if ($model->_department) {
                    $specialties = ESpecialty::getHigherSpecialty($model->_department);
                }

                ?>
                <?= $form->field($model, '_specialty')->widget(DepDrop::classname(), [
                    'data' => $specialties,
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'id' => '_specialty',
                        'placeholder' => __('-Choose Specialty-'),
                    ],
                    'pluginOptions' => [
                        'depends' => ['_department'],
                        'url' => Url::to(['/ajax/get_specialty']),
                        'placeholder' => __('-Choose Specialty-'),
                    ],
                ]); ?>
                <?/*= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => true,
                    'options' => [
                        'placeholder' => true
                    ],
                ]) */?>
                <?= $form->field($model, 'coefficient')->textInput(['maxlength' => true]) ?>
                <?//= $form->field($model, 'coef')->textInput(['maxlength' => true]) ?>
                <?//= $form->field($model, 'current_status')->checkbox(['class' => 'icheckbox']) ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['finance/increased-contract-coef'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['finance/increased-contract-coef', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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
