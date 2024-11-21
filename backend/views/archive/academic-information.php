<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\models\archive\EStudentDiploma;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\archive\EAcademicInformation;
use common\models\student\EStudentMeta;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php
Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php
                if ($this->_user()->role->code !== "teacher") { ?>
                    <div class="row" id="data-grid-filters">
                        <?php
                        $form = ActiveForm::begin(); ?>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_education_type')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => EducationType::getHighers(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_education_form')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => EducationForm::getClassifierOptions(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_specialty_id')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        $dataProvider->query->all(),
                                        '_specialty_id',
                                        'specialty.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_group')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map($dataProvider->query->all(), '_group', 'group.name'),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_payment_form')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => PaymentForm::getClassifierOptions(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?//= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Diploma Number / Register Number')])->label(false); ?>
                        </div>
                        <?php
                        ActiveForm::end(); ?>
                    </div>
                    <?php
                } ?>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'sticky' => '#sidebar',
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'class' => \yii\grid\SerialColumn::class
                            ],
                            [
                                'attribute' => '_student',
                                // 'enableSorting' => true,
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        $data->student->fullName,
                                        ['archive/academic-information-edit', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                },
                            ],
                            [
                                'attribute' => '_education_type',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return sprintf("%s<br>%s", $data->educationType->name, $data->educationForm->name);
                                },
                            ],
                            [
                                'attribute' => '_specialty_id',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return sprintf("%s<br>%s<br>%s", $data->specialty->code, $data->group->name, $data->curriculum->markingSystem->name);
                                },
                            ],


                            [
                                'attribute' => '_payment_form',
                                'value' => 'paymentForm.name',
                            ],

                            [
                                'attribute' => 'id',
                                'header' => __('View Contract'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    if(@$data->studentAcademic->academic_status == EAcademicInformation::ACADEMIC_INFORMATION_STATUS_PROCESS){
                                        return Html::a(__('View'),
                                            [
                                                'archive/academic-information',
                                                'code' => $data->id,
                                                'generate-pdf' => 1
                                            ], ['data-pjax' => 0]);
                                    }
                                    else if(@$data->studentAcademic->academic_status == EAcademicInformation::ACADEMIC_INFORMATION_STATUS_GENERATED){
                                        return Html::a(__('Download'),
                                            [
                                                'archive/academic-information',
                                                'code' => $data->id,
                                                'download' => 1
                                            ], ['data-pjax' => 0]);
                                    } else {
                                        return Html::a(__('Create'),
                                            [
                                                'archive/academic-information-edit',
                                                'id' => $data->id,
                                            ], ['data-pjax' => 0]);
                                    }
                                },
                            ],
                            [
                                'attribute' => 'id',
                                'header' => __('View Contract'),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    //if($data->studentAcademic->academic_status == EAcademicInformation::ACADEMIC_INFORMATION_STATUS_PROCESS){
                                        return Html::a(__('View'),
                                            [
                                                'archive/academic-information',
                                                'code' => $data->id,
                                                'generate-pdf' => 1
                                            ], ['data-pjax' => 0]);
                                    //}
                                    /*else if($data->studentAcademic->academic_status == EAcademicInformation::ACADEMIC_INFORMATION_STATUS_GENERATED){
                                        return Html::a(__('Download'),
                                            [
                                                'archive/academic-information',
                                                'code' => $data->id,
                                                'download' => 1
                                            ], ['data-pjax' => 0]);
                                    }*/
                                },
                            ],

                        ],
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>
<?php
Pjax::end() ?>
