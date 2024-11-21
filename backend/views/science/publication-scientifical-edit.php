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
use common\models\science\EPublicationAuthorMeta;
use common\models\system\classifier\Language;
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
use backend\widgets\GridView;
use backend\widgets\checkbo\CheckBo;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployee */

$this->title = $model->isNewRecord ? __('Insert Scientifical Publication') : __('Edit Scientifical Publication');
$this->params['breadcrumbs'][] = ['url' => ['science/publication-scientifical'], 'label' => __('Scientifical Publication')];
$this->params['breadcrumbs'][] = ['url' => ['science/publication-scientifical', 'id' => $model->id], 'label' => $model->name];
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
                                <div class="col-md-6">
                                    <?php if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT): ?>
                                        <div class="form-group">
                                            <label class="control-label" for=""><?= __('Employee');?></label>
                                            <div class="form-control">
                                                <?php echo Yii::$app->user->identity->employee->fullName;?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php
                                        $members = array();
                                        $members = EEmployee::getEmployees();
                                        ?>
                                        <?= $form->field($model, '_employee')->widget(Select2Default::classname(), [
                                            'data' => $members,
                                            'allowClear' => false,
                                            'placeholder' => false,
                                            'hideSearch' => false,
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, '_scientific_publication_type')->widget(Select2Default::classname(), [
                                        'data' => ScientificPublicationType::getClassifierOptions(),
                                        'allowClear' => false,
                                        'placeholder' => false,
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, '_language')->widget(Select2Default::classname(), [
                                        'data' => Language::getClassifierOptions(),
                                        'allowClear' => true,
                                        'placeholder' => false,
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'id' => 'name']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $form->field($model, 'keywords')->textInput(['maxlength' => true, 'id' => 'keywords']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <?= $form->field($model, 'author_counts')->textInput(['maxlength' => true, 'id' => 'author_counts']) ?>
                                </div>
                                <div class="col-md-10">
                                    <?= $form->field($model, 'authors')->textInput(['maxlength' => true, 'id' => 'authors'])->hint(__('For example: Samadov, Faxriddin Hakimovich; Baxtiyorov, Laziz Nematovich')) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $form->field($model, 'doi')->textInput(['maxlength' => true, 'id' => 'publisher']) ?>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <?= $form->field($model, 'source_name')->textInput(['maxlength' => true, 'id' => 'source_name']) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 'parameter')->textInput(['maxlength' => true, 'id' => 'parameter']) ?>
                                </div>


                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <?= $form->field($model, '_publication_database')->widget(Select2Default::classname(), [
                                        'data' => PublicationDatabase::getClassifierOptions(),
                                        'allowClear' => false,
                                        'placeholder' => false,
                                        'options' => [
                                            // 'prompt' => __('Choose'),
                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 'issue_year')->widget(Select2Default::classname(), [
                                        'data' => EPublicationScientific::getYearOptions(),
                                        'allowClear' => false,
                                        'hideSearch' => false,
                                        'options' => [

                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, '_education_year')->widget(Select2Default::classname(), [
                                        'data' => \common\models\curriculum\EducationYear::getEducationYears(),
                                        'allowClear' => false,
                                        'hideSearch' => false,
                                        'options' => [

                                        ],
                                    ]) ?>
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

                <?php if($mainAuthor = EPublicationAuthorMeta::getMainAuthor(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $model->id, Yii::$app->user->identity->_employee)):?>
                    <?php if($mainAuthor->_publication_scientific == $model->id):?>
                        <div class="box-header bg-gray">
                            <div class="row" id="data-grid-filters">
                                <div class="col col-md-8">
                                    <div class="form-group">
                                        <h3 class="box-title"><?= __('Author Information'); ?></h3>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="box-body">
                            <?= GridView::widget([
                                'id' => 'data-grid',
                                'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                                'dataProvider' => (new EPublicationAuthorMeta())->searchForScientifical($model),
                                'toggleAttribute' => 'is_checked_by_author',
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    [
                                        'attribute' => '_employee',
                                        'format' => 'raw',
                                        'value' => function ($data) {
                                            return $data->employee->fullName;
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
                                        'class' => 'yii\grid\ActionColumn',

                                        'template' => '{delete}',
                                        'buttons' => [
                                            'delete' => function ($url, $model) {
                                                return Html::a('<span class="fa fa-trash"></span>', $url, [
                                                    'title' => __('Delete'),
                                                    'data-confirm' => __('	Are you sure to delete?'),
                                                    'data-pjax' => '0',
                                                ]);
                                            }
                                        ],
                                        'urlCreator' => function ($action, $model, $key, $index) {
                                            if ($action === 'delete') {
                                                $url = Url::to(['science/publication-scientifical-list', 'id' => $model->id, 'delete' => 1]);
                                                return $url;
                                            }
                                        },
                                     //   'visible' => $data->is_main_author === 1 ? false : true,
                                    ],
                                ],
                            ]); ?>
                        </div>
                    <?php endif;?>
                <?php endif;?>

                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord && !$model->is_checked): ?>
                        <?= $this->getResourceLink(__('Delete'), ['science/publication-scientifical-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
        <div class="col col-md-4">
            <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>