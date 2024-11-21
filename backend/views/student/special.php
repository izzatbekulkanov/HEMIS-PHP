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
use common\models\structure\EDepartment;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Special');
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
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Special'),
                        ['student/special-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>

                <div class="col-md-3">
                    <?php if (!$faculty): ?>
                    <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'options' => [
                            'id' => '_department'
                        ],
                    ])->label(false); ?>
                    <?php endif; ?>
                </div>

            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                    'data' => EducationType::getHighers(),
                    'allowClear' => true,
                    'placeholder' => __('-Choose Education Type'),
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by speciality Name / Code')])->label(false) ?>
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
                    return Html::a($data->code, ['student/special-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->mainSpecialty ? $data->mainSpecialty->name : '', ['student/special-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_department',
                'value' => 'department.name',
                'visible' => ($this->_user()->role->code != AdminRole::CODE_DEAN)
            ],
            [
                'attribute' => '_education_type',
                'value' => 'educationType.name',
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
