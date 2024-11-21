<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\student\ESpecialty;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationYear;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\PaymentForm;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Manage Admission Quota');
$this->params['breadcrumbs'][] = ['url' => ['student/admission-quota'], 'label' => __('Admission Quota')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'enablePushState' => false]
) ?>
<div class="row">
    <div class="col col-md-9 col-lg-9">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_year')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EducationYear::getClassifierOptions(),
                                'allowClear' => true,
                                'options' => ['id' => 'education_year'],
                                'placeholder' => __('-Choose Education Year-'),
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_type')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EducationType::getHighers(),
                                'allowClear' => true,
                                'options' => ['id' => 'education_type'],
                                'placeholder' => __('-Choose Education Type-'),
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_education_form')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EducationForm::getClassifierOptions(),
                                'allowClear' => true,
                                'options' => ['id' => 'education_form'],
                                'placeholder' => __('-Choose Education Form-'),
                            ]
                        )->label(false); ?>
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
                            'attribute' => 'specialty.code',
                            'header' => __('Specialty Code'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a(
                                    $data->specialty->code,
                                    ['student/admission-quota-edit', 'id' => $data->id],
                                    ['data-pjax' => 0]
                                );
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'header' => __('Specialty Name'),
                            'value' => function ($data) {
                                return Html::a(
                                    $data->specialty->name,
                                    ['student/admission-quota-edit', 'id' => $data->id],
                                    ['data-pjax' => 0]
                                );
                            },
                        ],
                        [
                            'attribute' => '_education_year',
                            'value' => 'educationYear.name',
                        ],
                        [
                            'attribute' => '_education_type',
                            'value' => 'educationType.name',
                        ],
                        [
                            'attribute' => '_education_form',
                            'value' => 'educationForm.name',
                        ],
                        [
                            'attribute' => '_quota_type',
                            'value' => 'quotaType.name',
                        ],
                        'admission_quota',
                    ],
                ]
            ); ?>
        </div>
    </div>
    <div class="col col-md-3" id="sidebar">
        <div class="box box-default ">
            <div class="box-body">
                <?php $form2 = ActiveForm::begin(); ?>
                <?= $form2->field($model, '_education_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationType::getHighers(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Education Type-'),
                        'options' => [
                            'id' => '_education_type',
                        ],
                    ]
                )->label(); ?>
                <?php
                $specialties = [];
                if (in_array($model->_education_type, [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER], true)) {
                    $specialties = ESpecialty::getHigherSpecialtyByType($model->_education_type);
                } elseif (in_array($model->_education_type, [EducationType::EDUCATION_TYPE_DSC, EducationType::EDUCATION_TYPE_PHD], true)) {
                    $specialties = ESpecialty::getDoctorateSpecialtyList();
                }
                ?>
                <?= $form2->field($model, '_specialty')->widget(
                    DepDrop::classname(),
                    [
                        'data' => $specialties,
                        'type' => DepDrop::TYPE_SELECT2,
                        'pluginLoading' => false,
                        'select2Options' => [
                            'pluginOptions' => ['allowClear' => true,],
                            'theme' => Select2::THEME_DEFAULT,
                        ],
                        'options' => [
                            'id' => 'code',
                            'placeholder' => __('-Choose-'),
                        ],
                        'pluginOptions' => [
                            'depends' => ['_education_type'],
                            'url' => Url::to(['/ajax/get-specialties']),
                        ],
                    ]
                ) ?>
                <?= $form2->field($model, '_education_form')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationForm::getClassifierOptions(),
                        'allowClear' => true,
                        'options' => [
                            'id' => '_education_form',
                        ],
                        'placeholder' => __('-Choose Education Form-'),
                    ]
                )->label(); ?>
                <?= $form2->field($model, '_education_year')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationYear::getClassifierOptions(),
                        'allowClear' => true,
                        'options' => [
                            'id' => '_education_year',
                        ],
                        'placeholder' => __('-Choose Education Year-'),
                    ]
                )->label(); ?>
                <?= $form2->field($model, '_quota_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => PaymentForm::getClassifierOptions(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Quota Type-'),
                    ]
                )->label(); ?>
                <?= $form2->field($model, 'admission_quota')->input('number', ['min' => 1]) ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(
                        __('Delete'),
                        ['student/admission-quota-edit', 'id' => $model->id, 'delete' => 1],
                        ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]
                    ) ?>
                <?php endif; ?>
                <?= Html::submitButton(
                    '<i class="fa fa-check"></i> ' . __('Save'),
                    ['class' => 'btn btn-primary btn-flat']
                ) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
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
