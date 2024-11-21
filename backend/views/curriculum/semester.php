<?php

use common\models\curriculum\ECurriculum;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Curriculum Semester');
$this->params['breadcrumbs'][] = $this->title;


?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Semester'),
                        ['curriculum/semester-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                    'data' => ECurriculum::getOptions($faculty),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'placeholder' => __('-Choose Curriculum-'),
                    'options' => [
                    ]
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by semester Name / Code')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [

            [
                'attribute' => 'code',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->code, ['curriculum/semester-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->name, ['curriculum/semester-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_level',
                'value' => function ($data) {
                    return $data->level ? $data->level->name : '';
                },
            ],
            [
                'attribute' => '_curriculum',
                'value' => 'curriculum.name',
            ],
            [
                'attribute' => '_education_year',
                'value' => 'educationYear.name',
            ],
            [
                'attribute' => 'start_date',
                'header' => __('Period'),
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(Yii::$app->formatter->asDate($data->start_date) . ' - ' . Yii::$app->formatter->asDate($data->end_date), ['curriculum/semester-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'accepted',
                'format' => 'raw',
                'value' => function ($data) {
                    return CheckBo::widget([
                        'type' => 'switch',
                        'options' => [
                            'onclick' => "changeAttribute('$data->id', 'accepted')",
                        ],
                        'name' => $data->id,
                        'value' => $data->accepted
                    ]);
                },
            ],
            /*[
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                },
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],*/

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
        data.semester = id;
        data.attribute = att;
        $.get('<?= Url::to(['curriculum/semester'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>
