<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\curriculum\ECurriculum;
use common\models\system\AdminRole;

/* @var $this \backend\components\View */
/* @var $model \common\models\system\Admin */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->getFullname();
$this->params['breadcrumbs'][] = ['url' => ["employee/tutor-group"]];
$this->params['breadcrumbs'][] = $this->title . " (" . $model->employee->getStaffPositionsLabel() . ")";
$link = currentTo(['toggle' => 1]);

?>

<?php Pjax::begin(['id' => 'admin-group-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">

            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\EducationType::getClassifierOptions(),
                    'allowClear' => true,
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\EducationForm::getClassifierOptions(),
                    'allowClear' => true,
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by group Name / Code')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'format' => 'raw',
                'value' => function ($data) use ($link, $model) {
                    return CheckBo::widget(
                        [
                            'type' => 'switch',
                            'labelClass' => 'switch switch-xs switch-row',
                            'options' => [
                                'onclick' => "$.get('$link',{'group':'{$data->id}'})",
                            ],
                            'name' => 'active',
                            'value' => isset($model->tutorGroups[$data->id]),
                        ]
                    );
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'nowrap'
                ],
                'value' => function ($data) {
                    return $data->name;
                },
            ],
            [
                'attribute' => '_department',
                'value' => 'department.name',
            ],
            [
                'attribute' => '_specialty_id',
                'value' => 'specialty.code',
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
                'attribute' => '_education_lang',
                'value' => 'educationLang.name',
            ],

        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
