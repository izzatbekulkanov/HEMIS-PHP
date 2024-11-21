<?php
use backend\widgets\DatePickerDefault;
use backend\widgets\filekit\Upload;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\system\AdminRole;
use common\models\employee\EEmployee;
use common\models\system\classifier\ScientificPublicationType;
use common\models\system\classifier\PublicationDatabase;
use common\models\science\EPublicationScientific;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;


/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployee */

$this->title = $model->isNewRecord ? __('Insert Dissertation Defense') : __('Edit Dissertation Defense');
$this->params['breadcrumbs'][] = ['url' => ['science/doctorate-student'], 'label' => __('Doctorate Student')];
$this->params['breadcrumbs'][] = ['url' => ['science/doctorate-student', 'id'=>$doctorate->id], 'label' => $doctorate->getFullName()];
//$this->params['breadcrumbs'][] = ['url' => ['science/publication-scientifical', 'id' => $model->id], 'label' => $model->name];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();


\yii\widgets\MaskedInputAsset::register($this);
?>


<? //php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data' => ['pjax' => false]]]); ?>
    <div class="row">
        <div class="col col-md-12">
            <div class="box box-default ">
                <div class="box-body">
                    <div class="row">
                        <div class="col col-md-10">
                            <div class="row">
                                <div class="col-md-3">
                                    <?= $form->field($model, 'defense_date')->widget(DatePickerDefault::classname(), [
                                        'options' => [
                                            'placeholder' => __('YYYY-MM-DD'),
                                            'id' => 'defense_date',
                                        ],
                                    ]); ?>
                                </div>
                                <div class="col-md-5">
                                    <?= $form->field($model, 'defense_place')->textInput(['maxlength' => true, 'id' => 'defense_place']) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 'scientific_council')->textInput(['maxlength' => true, 'id' => 'scientific_council']) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <?= $form->field($model, 'diploma_given_by_whom')->textInput(['maxlength' => true, 'id' => 'diploma_given_by_whom']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <?= $form->field($model, 'diploma_number')->widget(MaskedInputDefault::className(), [
                                        'mask' => '09 № 999999',
                                        'options' => [
                                            'id' => 'diploma_number',
                                            'class' => 'form-control',

                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-5">
                                    <?= $form->field($model, 'approved_date')->widget(DatePickerDefault::classname(), [
                                        'options' => [
                                            'placeholder' => __('YYYY-MM-DD'),
                                            'id' => 'approved_date',
                                        ],
                                    ]); ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'register_number')->textInput(['maxlength' => true, 'id' => 'register_number']) ?>
                                    <?/*= $form->field($model, 'register_number')->widget(MaskedInputDefault::className(), [
                                        'mask' => '9999',
                                        'options' => [
                                            'id' => 'register_number',
                                            'class' => 'form-control',

                                        ],
                                    ]) */?>
                                </div>
                            </div>

                        </div>
                        <div class="col col-md-2">
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $form->field($model, 'filename')->widget(
                                        \backend\widgets\UploadDefault::class,
                                        [
                                            'url' => ['dashboard/file-upload', 'type' => 'attachment'],
                                            'acceptFileTypes' => new JsExpression(
                                                '/(\.|\/)(pdf)$/i'
                                            ),
                                            'maxFileSize' => 100 * 1024 * 1024, // 10 MiB
                                            'multiple' => false,
                                            'sortable' => true,
                                            //'maxNumberOfFiles' => 4,
                                            'clientOptions' => [

                                            ],
                                            'accept' => 'application/pdf',
                                            // 'language' => Yii::$app->language,
                                            // 'languages' => array_keys(\common\components\Config::getAllLanguagesWithLabels()),
                                            // 'useCaption' => false,
                                            'options' => ['class' => 'file'],
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord): ?>
                        <?= $this->getResourceLink(__('Delete'), ['science/dissertation-defense-edit', 'id' => $doctorate->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>