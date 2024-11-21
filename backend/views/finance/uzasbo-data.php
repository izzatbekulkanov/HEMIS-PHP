<?php
use backend\widgets\DatePickerDefault;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\PaymentForm;
use common\models\student\EStudentMeta;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use common\models\student\ESpecialty;
use common\models\student\EGroup;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;
$uploadForm = new \backend\models\FormUploadUzasbo();
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row">
                <div class="col-md-12 translation-form">
                        <?php $form1 = ActiveForm::begin([
                            'action' => ['finance/import-uzasbo'],
                            'options' => [
                                'data-pjax' => false,
                                'method' => 'post',
                                'id' => 'upload_form',
                                'enctype' => 'multipart/form-data',
                            ]
                        ]); ?>

                        <a data-pjax="0"
                           onclick="$('#formuploaduzasbo-file').click();return false"
                           href="#" class="btn btn-success btn-flat">
                            <?= __('Upload Usasbo Id') ?>
                        </a>
                        <?=
                        Html::a(__('Download Template'),
                            [
                                'finance/import-uzasbo',
                                'download' => 1
                            ], ['class' => 'btn btn-default btn-flat','data-pjax' => 0]);
                        ?>
                        <div class="file-wrapper">
                            <?= $form1->field($uploadForm, 'file', [
                                'template' => '{input}',
                            ])->fileInput([
                                    'onchange' => 'if(confirm("' . htmlentities(__('Are your sure upload all Uzasbo Id?')) . '"))$("#upload_form").submit()',
                                    'acceptFileTypes' => new JsExpression('/(\.|\/)(xls?s)$/i'),
                                    'accept' => '.xls, .xlsx',
                            ]) ?>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
                </div>

                <?php if ($this->_user()->role->code !== "teacher"){ ?>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                                'data' =>\common\models\structure\EDepartment::getFaculties(),
                                'disabled' => $faculty != null,
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?php
                            $specialties = array();
                            if ($searchModel->_department) {
                                $specialties = ESpecialty::getHigherSpecialty($searchModel->_department);
                            }
                            if ($faculty) {
                                $specialties = ESpecialty::getHigherSpecialty($faculty);
                            }
                            ?>
                            <?= $form->field($searchModel, '_specialty_id')->widget(Select2Default::classname(), [
                                'data' =>$specialties,
                                'disabled' => $searchModel->_department == null,
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                                'data' =>EducationForm::getClassifierOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?php
                            $groups = array();
                            if ($searchModel->_department && $searchModel->_specialty_id && $searchModel->_education_form) {
                                $groups = EGroup::getOptionsByFacultyEduForm($searchModel->_department, $searchModel->_specialty_id, $searchModel->_education_form);
                            }
                            ?>
                            <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                                'data' =>$groups,
                                'disabled' => ($searchModel->_department == null || $searchModel->_specialty_id == null || $searchModel->_education_form == null),
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
                            <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by student fullName / Pasport / PIN / Code')])->label(false) ?>
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
                            'attribute' => '_student',
                            'value' => 'student.fullName',
                            // 'enableSorting' => true,
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->student->fullName, ['finance/uzasbo-data', 'id' => $data->_student], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute'=>'_specialty_id',
                            'value' => 'specialty.code',
                        ],
                        [
                            'attribute'=>'_education_type',
                            'value' => 'educationType.name',
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
                            'attribute'=>'e_student.uzasbo_id_number',
                            //'value' => 'student.uzasbo_id_number',
                            'value' => function ($data) {
                                return $data->student->uzasbo_id_number;
                            },
                            'label' => __('E Student Uzasbo Id Number'),

                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <?//php if (Yii::$app->request->get('id')): ?>
        <div class="col col-md-4" id="sidebar">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
            <div class="box box-default ">
                <div class="box-body">
                    <?php if (Yii::$app->request->get('id')): ?>
                        <?= $form->errorSummary($model);?>
                        <?= $form->field($model, '_student')->textInput(['value' => $student->student->fullName, 'disabled'=>true])->label(__('Fullname of Student')); ?>
                    <?php else: ?>
                        <?= $form->field($model, 'id')->textInput(['disabled'=>true])->label(__('Fullname of Student')); ?>
                    <?php endif; ?>
                    <?= $form->field($model, 'uzasbo_id_number')->textInput(['maxlength' => true]) ?>

                </div>

                <div class="box-footer text-right">
                    <?php if (Yii::$app->request->get('id')): ?>
                        <?= $this->getResourceLink(__('Cancel'), ['finance/uzasbo-data'], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    <?//php endif; ?>
</div>
<?php Pjax::end() ?>
