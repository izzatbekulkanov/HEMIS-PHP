<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationYear;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Admission Quota');
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Admission Quota'),
                        ['student/admission-quota-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_year')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationYear::getClassifierOptions(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Education Year-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationType::getHighers(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Education Type-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_form')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationForm::getClassifierOptions(),
                        'allowClear' => true,
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
            'toggleAttribute' => 'active',
            'dataProvider' => $dataProvider,
            'columns' => [

                [
                    'attribute' => 'specialty.code',
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
                    'header' => __('Specialty Name'),
                    'format' => 'raw',
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
                [
                    'attribute' => 'students.count',
                    'header' => __('Students count'),
                    'value' => function ($data) {
                        return $data->getStudents()->count();
                    },
                ],
                [
                    'attribute' => 'difference',
                    'header' => __('Farq'),
                    'value' => function ($data) {
                        return $data->admission_quota - $data->getStudents()->count();
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
