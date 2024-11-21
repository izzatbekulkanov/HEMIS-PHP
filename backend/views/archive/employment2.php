<?php
use backend\widgets\DatePickerDefault;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\EducationForm;
use common\models\student\EStudentMeta;
use common\models\archive\EStudentEmployment;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\SerialColumn;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;
use common\models\curriculum\EducationYear;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-3">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-download"></i> ' . __('Export to Excel'),
                                [
                                    'archive/employment',
                                    'education_year' => $searchModel->_education_year,
                                    'download' => 1
                                ],
                                ['class' => 'btn btn-flat btn-success btn-primary', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                </div>

                <?php if ($this->_user()->role->code !== "teacher"){ ?>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-12">
                            <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by student fullName / Pasport / PIN / Code')])->label(false) ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                                'data' => EducationYear::getEducationYears(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_education_year',
                                ]
                            ])->label(false);; ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                                'data' =>\common\models\system\classifier\EducationForm::getClassifierOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>

                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                                'data' =>\common\models\structure\EDepartment::getFaculties(),
                                'disabled' => $faculty != null,
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                                'data' =>EducationType::getHighers(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_payment_form')->widget(Select2Default::classname(), [
                                'data' =>PaymentForm::getClassifierOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, 'employment_registration')->widget(Select2Default::classname(), [
                                'data' =>EStudentMeta::getRegistrationOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                <?php } ?>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sticky' => '#sidebar',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            '__class' => SerialColumn::class,
                        ],
                        [
                            'attribute' => '_student',
                            'value' => 'student.fullName',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->student->fullName,
                                    ['archive/employment',
                                        'id' => $data->_student
                                    ],
                                    [
                                         'data-pjax' => 0
                                    ]);
                            },
                        ],
                        [
                            'attribute'=>'_specialty_id',
                            'value' => 'specialty.code',
                        ],
                        [
                            'attribute' => 'employment_doc_number',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return sprintf("%s / %s<p class='text-muted'> %s</p>",
                                    $data->studentEmployment ? $data->studentEmployment->employment_doc_number : '-',
                                    $data->studentEmployment ? Yii::$app->formatter->asDate($data->studentEmployment->employment_doc_date, 'php:d.m.Y') : '-',
                                    $data->studentEmployment ? EStudentEmployment::getEmploymentStatusOptions()[$data->studentEmployment->_employment_status] : '-');
                            },
                        ],
                        [
                            'attribute'=>'_education_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return sprintf("%s<p class='text-muted'> %s</p>", $data->educationType->name, $data->educationForm->name);
                            },
                        ],
                        [
                            'attribute'=>'_group',
                            'value' => 'group.name',
                        ],
                        [
                            'attribute'=>'_payment_form',
                            'value' => 'paymentForm.name',
                        ],
                        [
                            'attribute'=>'employment_registration',
                            'value' => function ($data) {
                                return @$data->registrationOptions[@$data->employment_registration];
                            },

                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php Pjax::end() ?>
