<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/**
 * @var $itemModel \common\models\system\classifier\_BaseClassifier
 * @var $model \common\models\system\SystemClassifier
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['url' => ['system/classifier'], 'label' => __('System Classifier')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-chevron-left"></i>&nbsp;&nbsp;' . __('System Classifier'),
                                ['system/classifier'],
                                ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                            ) ?>
                            <?php if (HEMIS_INTEGRATION): ?>
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Sync from HEMIS API'),
                                    ['system/classifier', 'classifier' => $model->classifier, 'sync' => 1],
                                    ['class' => 'btn btn-flat btn-danger', 'data-pjax' => 0]
                                ) ?>
                            <?php else: ?>
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Import Data'),
                                    ['system/classifier', 'classifier' => $model->classifier, 'import' => 1],
                                    ['class' => 'btn btn-flat btn-danger', 'data-pjax' => 0]
                                ) ?>
                            <?php endif ?>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-file"></i>',
                                ['system/classifier', 'classifier' => $model->classifier, 'download' => 1],
                                ['class' => 'btn btn-flat btn-default', 'data-pjax' => 0]
                            ) ?>

                        </div>
                    </div>

                </div>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sortable' => true,
                    'toggleAll' => true,
                    'toggleAttribute' => 'active',
                    'sticky' => '#sidebar',
                    'rowOptions' => function ($item) use ($itemModel) {
                        return [
                            'class' => $itemModel->code == $item->code ? 'selected-row' : '',
                        ];
                    },
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, ['system/classifier', 'classifier' => $model->classifier, 'code' => $data->primaryKey], []);
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->getShortName(), ['system/classifier', 'classifier' => $model->classifier, 'code' => $data->primaryKey], []);
                            },
                        ],
                        [
                            'attribute' => 'version',
                            'format' => 'raw',
                            'value' => function (\common\models\system\classifier\_BaseClassifier $data) {
                                $version = $data->getOptionValue('version');
                                return $version === null ? '0' : $version;
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
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <?php if ($parents = $itemModel->getParentClassifierOptions()): ?>
                    <?= $form->field($itemModel, '_parent')->widget(Select2::classname(), [
                        'data' => $parents,
                        'options' => ['class' => 'select2'],
                        'theme' => Select2::THEME_DEFAULT,
                        'hideSearch' => false,
                        'pluginLoading' => false,
                        'disabled' => HEMIS_INTEGRATION,
                        'pluginOptions' => [
                            'allowClear' => true,
                            'placeholder' => __('Choose Parent Classifier'),
                        ],
                    ])->label(__('Parent Classifier')) ?>
                <?php endif; ?>
                <?= $form->field($itemModel, 'name')->textInput(['maxlength' => true, 'disabled' => HEMIS_INTEGRATION]) ?>
                <?= $form->field($itemModel, 'code')->textInput(['maxlength' => true, 'disabled' => !$itemModel->isNewRecord])->label() ?>

                <?php
                $file = Yii::getAlias('@backend/views/system/classifier/' . $model->classifier . '.php');
                ?>
                <?php if (file_exists($file)): ?>
                    <?= $this->renderFile($file, ['form' => $form, 'itemModel' => $itemModel]) ?>
                <?php endif; ?>
            </div>
            <?php if (HEMIS_INTEGRATION == false): ?>
                <div class="box-footer text-right">
                    <?php if (!$itemModel->isNewRecord): ?>
                        <?= $this->getResourceLink(__('Cancel'), ['system/classifier', 'classifier' => $model->classifier], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                        <?= $this->getResourceLink(__('Delete'), ['system/classifier', 'classifier' => $model->classifier, 'code' => $itemModel->primaryKey, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php else: ?>
                    <?php endif; ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Update'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php Pjax::end() ?>
