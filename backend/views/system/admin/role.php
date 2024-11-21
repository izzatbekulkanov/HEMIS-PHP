<?php

use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('System Role');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6">
                <div class="form-group">
                    <?php $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Role'),
                        ['system/role-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                    <div class="btn-group ">
                        <?= $this->getResourceLink(
                            '<i class="fa fa-user-circle"></i> ' . __('System Resource'),
                            ['system/resource'],
                            ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                        ) ?>
                        <button type="button" class="btn btn-default btn-flat dropdown-toggle"
                                data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a data-pjax="0"
                                   href="<?= Url::current(['download' => 1]) ?>">
                                    <?= __('Download Configuration') ?>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Role Name / Code')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'sortable' => true,
        'columns' => [

            [
                'attribute' => 'code',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->code, ['system/role-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'name',
            ],
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
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
