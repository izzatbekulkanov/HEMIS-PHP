<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\StudentType;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create'),
                        ['finance/contract-price-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-4">
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
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationType::getHighers(),
                        'allowClear' => true,
                        'hideSearch' => false,
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_form')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationForm::getClassifierOptions(),
                        'allowClear' => true,
                        'hideSearch' => false,
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_student_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => StudentType::getClassifierSpecialOptions(StudentType::STUDENT_TYPE_SUPER_CONTRACT),
                        'allowClear' => true,
                        'hideSearch' => false,
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
                    '__class' => SerialColumn::class,
                ],
                [
                    'attribute' => '_department',
                    'format' => 'raw',
                    /*'contentOptions' => [
                        'class' => 'nowrap',
                    ],*/
                    'value' => function ($data) {
                        return Html::a(
                            $data->department->name,
                            ['finance/contract-price-edit', 'id' => $data->id],
                            ['data-pjax' => 0]
                        );
                    },
                ],
                [
                    'attribute' => '_specialty',
                    'format' => 'raw',
                    'contentOptions' => [
                        //'class' => 'nowrap',
                        'style' => 'width:35%',

                    ],
                    'value' => function ($data) {
                        return Html::a(sprintf("%s<p class='text-muted'></p>", $data->specialty->fullName), ['finance/contract-price-edit', 'id' => $data->id], ['data-pjax' => 0]);
                    },
                ],
                [
                    'attribute' => '_education_type',
                    'value' => 'educationType.name',
                ],
                [
                    'attribute' => '_education_form',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'>%s</p>", $data->educationForm->name, $data->studentType->name);
                    },
                ],
                [
                    'attribute' => 'coefficient',

                ],
                [
                    'attribute' => 'summa',
                    'value' => function ($data) {
                        return sprintf("%s", $data->summa !==null ? Yii::$app->formatter->asCurrency($data->summa) : '-');
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
