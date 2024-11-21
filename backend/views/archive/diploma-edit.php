<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\archive\EDiplomaBlank;
use common\models\curriculum\RatingGrade;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\PaymentForm;
use common\models\student\EStudentMeta;
use backend\widgets\Select2Default;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\archive\EStudentDiploma
 * @var $university \common\models\structure\EUniversity
 */
$this->title = __('Manage Diploma');
$this->params['breadcrumbs'][] = ['label' => __('Archive Diploma'), 'url' => ['diploma']];
$this->params['breadcrumbs'][] = $this->title;

$disabled = $model->accepted;
$graduation_work_required = $model->student && $model->student->meta->getSubjects()
    ->joinWith('curriculumSubject')
    ->andWhere(['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE])
    ->exists();
?>
<?php
Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-12" id="sidebar">
        <?php
        $form = ActiveForm::begin(
            ['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]
        ); ?>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Student information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->errorSummary($model, ['showAllErrors' => true]); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'student_name')->textInput(['disabled' => $disabled || Config::getLanguageCode() !== Config::LANGUAGE_ENGLISH_CODE]) ?>
                        <?= $form->field($model, 'student_birthday')->widget(
                            DatePickerDefault::classname(),
                            [
                                'options' => [
                                    'disabled' => $disabled,
                                    'placeholder' => __('Enter date'),
                                ],
                            ]
                        ); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'student_id_number')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'group_name')->textInput(['disabled' => $disabled]) ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('HEI information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'university_name')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'rector_fullname')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'given_hei_information')->textInput(['disabled' => $disabled]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'post_address')->textarea(
                            ['class' => 'no-resize', 'rows' => 3, 'disabled' => $disabled]
                        ) ?>
                        <?= $form->field($model, 'university_accreditation')->textarea(
                            ['class' => 'no-resize', 'rows' => 3, 'disabled' => $disabled]
                        ) ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Specialty information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'department_name')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'specialty_code')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'specialty_name')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'qualification_name')->textInput(['disabled' => $disabled]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'qualification_data')->textarea(
                            ['class' => 'no-resize e_diploma-qualification_data', 'rows' => 12, 'disabled' => $disabled]
                        ) ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Education information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'education_type_name')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'education_form_name')->textInput(['disabled' => $disabled]) ?>
                        <?= $form->field($model, 'graduate_qualifying_work')->textarea(['disabled' => $disabled, 'data-required' => $graduation_work_required ? 'true' : 'false', 'maxlength' => true]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'education_language')->textInput(['disabled' => $disabled]) ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'education_year_name')->textInput(['disabled' => $disabled]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'education_period')->textInput(['disabled' => $disabled]) ?>
                            </div>
                        </div>
                        <?= $form->field($model, 'moved_hei')->textarea(['disabled' => $disabled]) ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Additional information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'last_education')->textInput(['disabled' => $disabled, 'maxlength' => true]) ?>
                        <?= $form->field($model, 'marking_system')->textarea(
                            ['class' => 'no-resize e_diploma-marking_system', 'disabled' => $disabled, 'maxlength' => true]
                        ) ?>
                        <?= $form->field($model, 'additional_info')->textarea(
                            ['class' => 'no-resize e_diploma-additional_info', 'disabled' => $disabled, 'maxlength' => true]
                        ) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'admission_information')->textarea(
                            ['class' => 'no-resize', 'disabled' => $disabled, 'maxlength' => true]
                        ) ?>
                        <?= $form->field($model, 'next_edu_information')->textarea(
                            ['class' => 'no-resize', 'disabled' => $disabled, 'maxlength' => true]
                        ) ?>
                        <?= $form->field($model, 'professional_activity')->textarea(
                            ['class' => 'no-resize', 'disabled' => $disabled, 'maxlength' => true]
                        ) ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <h3 class="box-title"><?= __('Diploma information') ?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'given_city')->textInput(['disabled' => $disabled]) ?>

                        <?= $form->field($model, 'diploma_category')->widget(
                            Select2Default::className(),
                            [
                                'data' => EDiplomaBlank::getCategoryOptions(),
                                'options' => ['id' => 'diploma_category'],
                                'disabled' => $disabled
                            ]
                        ); ?>

                        <?= $form->field($model, 'diploma_number')->widget(
                            DepDrop::classname(),
                            [
                                'data' => ($model->isNewRecord) ? [] : array_merge(
                                    [$model->diploma_number => $model->diploma_number],
                                    EDiplomaBlank::getSelectOptions(
                                        $model->_education_type === EducationType::EDUCATION_TYPE_BACHELOR ? EDiplomaBlank::TYPE_BACHELOR : EDiplomaBlank::TYPE_MASTER,
                                        $model->diploma_category
                                    )
                                ),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => 'diploma_number',
                                    'disabled' => $disabled,
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['diploma_category'],
                                    'url' => Url::to(
                                        [
                                            '/ajax/get-diploma-blank',
                                            'type' => $model->_education_type === EducationType::EDUCATION_TYPE_BACHELOR ? EDiplomaBlank::TYPE_BACHELOR : EDiplomaBlank::TYPE_MASTER
                                        ]
                                    ),
                                ],
                            ]
                        ) ?>

                    </div>
                    <div class="col col-md-6">

                        <?= $form->field($model, 'register_number')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>
                        <?= $form->field($model, 'register_date')->widget(
                            DatePickerDefault::classname(),
                            [
                                'options' => [
                                    'placeholder' => __('Enter date'),
                                    'disabled' => $disabled,
                                ],
                            ]
                        ); ?>
                        <?= $form->field($model, 'order_date')->widget(
                            DatePickerDefault::classname(),
                            [
                                'options' => [
                                    'placeholder' => __('Enter date'),
                                    'disabled' => $disabled
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord && !$model->accepted): ?>
                    <?= $this->getResourceLink(
                        __('Diploma'),
                        ['archive/diploma-print', 'id' => $model->_student],
                        [
                            'class' => 'btn btn-default btn-flat btn-confirm',
                            'data-message' => __('Are you sure you are perform this action?'),
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]
                    ) ?>
                    <?= $this->getResourceLink(
                        __('Supplement'),
                        ['archive/diploma-application-print', 'id' => $model->_student],
                        [
                            'class' => 'btn btn-default btn-flat btn-confirm',
                            'data-message' => __('Are you sure you are perform this action?'),
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]
                    ) ?>
                    <?= $this->getResourceLink(
                        __('Delete'),
                        ['archive/diploma-edit', 'id' => $model->_student, 'delete' => 1],
                        ['class' => 'btn btn-danger btn-flat btn-delete'],
                        'archive/diploma-delete'
                    ) ?>
                <?php elseif ($model->isNewRecord): ?>
                    <?php if (Yii::$app->request->get('fill', false)): ?>
                        <?= $this->getResourceLink(
                            __('Cancel'),
                            ['archive/diploma-edit', 'id' => $model->_student],
                            ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]
                        ) ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-info btn-confirm"
                                data-action="<?= Url::current(['fill' => 1]) ?>"
                                data-message="<?= __(
                                    'Talaba {name}ning diplom ma\'lumotlarini to\'ldirishni istaysizmi?',
                                    ['name' => $model->student_name]
                                ) ?>"
                        >
                            <?= __('Fill Data') ?>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($model->isNewRecord || !$model->accepted): ?>
                <?= Html::submitButton(
                    '<i class="fa fa-check"></i> ' . __('Save'),
                    ['class' => 'btn btn-primary btn-flat']
                ) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        ActiveForm::end(); ?>
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
</div>
<?php
Pjax::end() ?>
