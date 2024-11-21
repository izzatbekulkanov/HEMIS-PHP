<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\ScientificPlatform;
use backend\widgets\Select2Default;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'activity-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

    <div class="box box-default ">
        <div class="box-header bg-gray">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-md-3">
                    <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                        'data' => EducationYear::getEducationYears(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'placeholder' => __('Education Year'),
                    ])->label(false); ?>

                </div>
                <div class="col col-md-3">
                    <?= $form->field($searchModel, '_scientific_platform')->widget(Select2Default::classname(), [
                        'data' => ScientificPlatform::getClassifierOptions(),
                        'hideSearch' => true,
                        'allowClear' => true,
                        'options' => [
                            'id' => '_scientific_platform_search',
                        ],
                    ])->label(false); ?>

                </div>
                <div class="col col-md-6">
                    <? //= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?= GridView::widget([
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => '_scientific_platform',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return $data->scientificPlatform->name;
                    },
                ],
                'profile_link',
                'h_index',
                'publication_work_count',
                'citation_count',
                [
                    'attribute' => '_employee',
                    'value' => function ($data) {
                        return $data->employee->fullName;
                    },
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                    },
                ],
                [
                    'attribute' => 'is_checked',
                    'format' => 'raw',
                    //'header' => __('Is Checked'),
                    'value' => function ($data) {
                        return CheckBo::widget([
                            'type' => 'switch',
                            'options' => [
                                'onclick' => "changeAttribute('$data->id',  'is_checked')",
                            ],
                            'name' => $data->id,
                            'value' => $data->is_checked,
                        ]);
                    },
                ],

            ],
        ]); ?>
    </div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
    <script>
        function changeAttribute(id, att) {
            var data = {};
            data.publication = id;
            data.attribute = att;
            $.get('<?= Url::to(['science/scientific-activity-check'])?>', data, function (resp) {
                $.pjax.reload('#activity-grid');
            })
        }
    </script>
<?php Pjax::end() ?>