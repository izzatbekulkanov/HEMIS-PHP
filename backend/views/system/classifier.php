<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\AdminResource;
use common\models\system\SystemClassifier;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-sm-4">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-rotate-right"></i>&nbsp;&nbsp;' . __('Sync Classifiers'),
                                ['system/classifier', 'sync' => HEMIS_INTEGRATION ? 'api' : 'code'],
                                ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <div class="col col-sm-8">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Classifier')])->label(false) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <?= GridView::widget([
                'id' => 'data-grid',
                'dataProvider' => $dataProvider,
                'sortable' => false,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::a($data->name, ['system/classifier', 'classifier' => $data->classifier], ['data-pjax' => 0]);
                        },
                    ],
                    [
                        'attribute' => 'classifier',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::a($data->classifier, ['system/classifier', 'classifier' => $data->classifier], ['data-pjax' => 0]);
                        },
                    ],
                    'version',
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
    </div>
</div>
<?php Pjax::end() ?>
