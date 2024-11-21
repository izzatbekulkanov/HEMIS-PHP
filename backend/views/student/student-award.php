<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\classifier\StudentSuccess;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Student Award');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php
            $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Award'),
                        ['student/student-award-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-2"></div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, 'award_year')->widget(
                    Select2Default::classname(),
                    [
                        'data' => ArrayHelper::map($dataProvider->getModels(), 'award_year', 'award_year'),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Year-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_award_category')->widget(
                    Select2Default::classname(),
                    [
                        'data' => StudentSuccess::getClassifierOptions(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Award Category-'),
                    ]
                )->label(false); ?>
            </div>

            <?php
            ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'toggleAttribute' => 'active',
            'dataProvider' => $dataProvider,
            'columns' => [

                [
                    'attribute' => '_student',
                    'format' => 'raw',
                    'contentOptions' => [
                        'class' => 'nowrap'
                    ],
                    'value' => function ($data) {
                        return Html::a(
                            $data->student->fullName,
                            ['student/student-award-edit', 'id' => $data->id],
                            ['data-pjax' => 0]
                        );
                    },
                ],
                [
                    'attribute' => '_group',
                    'header' => __('Group'),
                    'value' => 'studentGroup.name',
                ],
                [
                    'attribute' => '_education_type',
                    'header' => __('Education Type'),
                    'value' => 'student.meta.educationType.name',
                ],
                [
                    'attribute' => '_education_form',
                    'header' => __('Education Form'),
                    'value' => 'student.meta.educationForm.name',
                ],
                [
                    'attribute' => '_specialty',
                    'header' => __('Specialty Code'),
                    'value' => 'student.meta.specialty.code',
                ],
                [
                    'attribute' => '_award_category',
                    'value' => 'awardCategory.name',
                ],
                'award_year'
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

<?php
Pjax::end() ?>
