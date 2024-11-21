<?php

use backend\widgets\GridView;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-4">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Admin'),
                        ['system/admin-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_role')->widget(Select2::classname(), [
                    'data' => AdminRole::getAllOptionsArray(),
                    'options' => ['class' => 'select2'],
                    'theme' => Select2::THEME_DEFAULT,
                    'pluginLoading' => false,
                    'hideSearch' => false,
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => __('Choose Role'),
                    ],
                ])->label(false) ?>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Full Name / Login / Email')])->label(false) ?>
            </div>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Import Admins'),
                        ['system/admin', 'download' => 1],
                        ['class' => 'btn btn-flat btn-success  btn-block', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'login',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(sprintf("%s <p class='text-muted'>%s</p>", $data->login, $data->full_name), ['system/admin-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_role',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s <p class='text-muted'>%s</p>", @$data->role->name, count($data->roles) > 1 ? implode(', ', \yii\helpers\ArrayHelper::getColumn($data->roles, 'name')) : '');
                },
            ],
            'email',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->getStatusLabel();
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                },
            ],

        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
