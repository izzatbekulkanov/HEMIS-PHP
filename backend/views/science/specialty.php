<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use common\models\system\AdminRole;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Doctorate Specialty');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Doctorate Specialty'),
                        ['science/specialty-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-3">

            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Doctorate Specialty Name / Code')])->label(false) ?>
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
                    return Html::a($data->code, ['science/specialty-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->mainSpecialty ? $data->mainSpecialty->name : '', ['science/specialty-edit', 'id' => $data->id], ['data-pjax' => 0]);
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

<?php Pjax::end() ?>
