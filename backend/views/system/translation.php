<?php

use backend\components\View;
use backend\models\FormUploadTrans;
use backend\widgets\GridView;
use common\components\Config;
use common\models\system\SystemMessage;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $searchModel SystemMessage */
/* @var $message SystemMessage */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
$uploadForm = new FormUploadTrans();
$user = $this->_user();
$canRunBatchActions = $user->canAccessToResource('system/translation-batch-actions');
?>
<?php Pjax::begin(['id' => 'translation-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row">

        <div class="col col-md-8">
            <div class="box box-default">
                <div class="box-header bg-gray">
                    <div class="row">
                        <?php $form = ActiveForm::begin(['options' => ['id' => "data-grid-filters"]]); ?>
                        <div class="col-md-3">
                            <?= $form->field($searchModel, 'language')->widget(Select2::classname(), [
                                'data' => Config::getLanguageOptions(),
                                'options' => ['class' => 'select2'],
                                'theme' => Select2::THEME_DEFAULT,
                                'hideSearch' => true,
                                'pluginLoading' => false,
                                'pluginOptions' => [
                                    'allowClear' => false,
                                    'placeholder' => __('Choose Language'),
                                ],
                            ])->label(false) ?>
                        </div>

                        <div class="col-md-<?= $canRunBatchActions ? 6 : 9 ?>">
                            <?= $form->field($searchModel, 'search', [
                                'labelOptions' => ['class' => 'invisible']
                            ])->textInput(['autofocus' => false, 'placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                        <?php if ($canRunBatchActions): ?>
                            <div class="col-md-3 translation-form">

                                <?php $form = ActiveForm::begin([
                                    'action' => ['system/translation', 'upload' => 1],
                                    'options' => [
                                        'data-pjax' => false,
                                        'method' => 'post',
                                        'id' => 'upload_form',
                                        'enctype' => 'multipart/form-data',
                                    ]
                                ]); ?>
                                <div class="form-group text-right">
                                    <div class="btn-group ">
                                        <a data-pjax="0" class="btn btn-default btn-flat "
                                           href="<?= Url::to(['system/translation', 'download' => 1]) ?>">
                                            <i class="fa fa-download"></i> <?= __('Download') ?>
                                        </a>
                                        <button type="button" class="btn btn-default btn-flat dropdown-toggle"
                                                data-toggle="dropdown">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li>
                                                <?php if (Config::isLatinCyrill()): ?>
                                                    <a onclick="return confirm('<?= htmlentities(__('Are you sure to transliterate all messages?')) ?>')"
                                                       data-pjax="0"
                                                       href="<?= Url::to(['system/translation', 'convert' => 1]) ?>">
                                                        <?= __('Transliterate Uzbek') ?>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <a data-pjax="0"
                                                   href="<?= Url::to(['system/translation', 'download' => 1]) ?>">
                                                    <?= __('Download Translation') ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a data-pjax="0"
                                                   onclick="$('#formuploadtrans-file').click();return false"
                                                   href="#">
                                                    <?= __('Upload Translation') ?>
                                                </a>
                                            </li>
                                            <?php if ($user->canAccessToResource('system/translation-clear')): ?>
                                                <li>
                                                    <a onclick="return confirm('<?= htmlentities(__('Are you sure delete all messages?')) ?>')"
                                                       data-pjax="0"
                                                       href="<?= Url::to(['system/translation', 'clear' => 1]) ?>">
                                                        <?= __('Clear All Messages') ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>

                                <div class="file-wrapper">
                                    <?= $form->field($uploadForm, 'file', [
                                        'template' => '{input}'
                                    ])->fileInput(['onchange' => 'if(confirm("' . htmlentities(__('Are your sure upload all translations?')) . '"))$("#upload_form").submit()']) ?>
                                </div>
                                <?php ActiveForm::end(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'sticky' => '#sidebar',
                    'columns' => [
                        'category',
                        [
                            'attribute' => 'message',
                            'format' => 'raw',
                            'value' => function (SystemMessage $data) {
                                return Html::a($data->message, Url::current(['id' => $data->id]), [
                                ]);
                            },
                        ],

                        [
                            'attribute' => 'translation',
                            'format' => 'raw',
                            'value' => function (SystemMessage $data) {
                                return $data->translation;
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <?php
            $langs = \common\components\Config::getAllLanguagesWithLabels();
            ?>
            <div class="col col-md-4" id="sidebar">
                <?php $form = ActiveForm::begin(['options' => ['data-pjax' => 1], 'action' => Url::current(['id' => $message->id])]); ?>
                <div class="box box-default">
                    <div class="box-body">
                        <?= $form->field($message, 'message')->textarea(['maxlength' => true, 'readonly' => true, 'rows' => 5])->label() ?>
                        <?php foreach ($langs as $lang => $label): ?>
                            <?php if (Config::isLanguageEnable($lang)): ?>
                                <?= $form->field($message, "lang[$lang]")->textarea(['maxlength' => true, 'readonly' => false, 'rows' => 1])->label($label) ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="box-footer text-right">
                        <?php if ($message->id): ?>
                            <?= Html::a(__('Delete'), [
                                'system/translation',
                                'id' => $message->id,
                                'delete' => 1
                            ], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                        <?php endif; ?>
                        <?= Html::submitButton(__('Update'), ['class' => 'btn btn-primary btn-flat ']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        <?php endif; ?>
    </div>
<?php Pjax::end() ?>