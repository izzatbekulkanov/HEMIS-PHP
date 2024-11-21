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
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Group');
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Group'),
                        ['student/group-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-4">
                <?php $faculty = $this->_user()->role->code == AdminRole::CODE_DEAN ? Yii::$app->user->identity->employee->deanFaculties->id : ""; ?>
                <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                    'data' => ECurriculum::getOptions($faculty),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'placeholder' => __('-Choose Curriculum-'),
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
        'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [

            [
                'attribute' => 'name',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'nowrap'
                ],
                'value' => function ($data) {
                    return Html::a($data->name, ['student/group-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_department',
                'value' => 'department.name',
                'visible' => ($this->_user()->role->code != AdminRole::CODE_DEAN)
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
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
