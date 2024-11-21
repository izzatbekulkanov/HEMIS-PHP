<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Qualification');
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
            <div class="col col-md-3">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Qualification'),
                        ['student/qualification-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_type')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EducationType::getHighers(),
                        'allowClear' => true,
                        'hideSearch' => false,
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, '_specialty')->widget(
                    Select2Default::classname(),
                    [
                        'data' => ArrayHelper::map(
                            $dataProvider->getModels(),
                            '_specialty',
                            'specialty.fullName'
                        ),
                        'allowClear' => true,
                        'hideSearch' => false,
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
                ['class' => SerialColumn::class],
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'contentOptions' => [
                        'class' => 'nowrap'
                    ],
                    'value' => function ($data) {
                        return Html::a(
                            $data->name,
                            ['student/qualification-edit', 'id' => $data->id],
                            ['data-pjax' => 0]
                        );
                    },
                ],
                [
                    'attribute' => '_specialty',
                    'value' => 'specialty.fullName',
                ],
                [
                    'attribute' => '_education_type',
                    'value' => 'specialty.educationType.name',
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

<?php
Pjax::end() ?>
