<?php

use backend\widgets\Select2Default;
use common\models\academic\EDecree;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\AdminRole;
use common\models\system\classifier\DecreeType;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationYear;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Manage Graduate Qualifying Work');
$this->params['breadcrumbs'][] = ['url' => ['archive/graduate-work'], 'label' => __('Graduate Qualifying Work')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
$disabled = false;
$disabledDepartment = false;
if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
    $disabled = true;
    $model->_faculty = $this->_user()->employee->deanFaculties->id;
} elseif ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
    $disabled = true;
    $disabledDepartment = true;
    $model->_faculty = $this->_user()->employee->headDepartments->parent;
    $model->_department = $this->_user()->employee->headDepartments->id;
}
Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'enablePushState' => false]
) ?>
<div class="row">
    <div class="col col-md-12 col-lg-12">
        <?php
        $form = ActiveForm::begin(); ?>
        <div class="box box-default">
            <div class="box-body">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-6">
                        <?= $form->field($model, '_faculty')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => true,
                                'placeholder' => __('-Choose Faculty-'),
                                'options' => [
                                    'id' => '_faculty',
                                ],
                                'disabled' => $disabled
                            ]
                        )->label(); ?>
                        <?= $form->field($model, '_education_type')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EducationType::getHighers(),
                                'allowClear' => true,
                                'placeholder' => __('-Choose Education Type-'),
                                'options' => [
                                    'id' => '_education_type',
                                ],
                            ]
                        )->label(); ?>

                        <?= $form->field($model, '_department')->widget(
                            Select2Default::classname(),
                            [
                                'data' => ArrayHelper::map(EDepartment::getDepartmentList(), 'id', 'name'),
                                'disabled' => $this->_user()->role->code === AdminRole::CODE_DEPARTMENT
                            ]
                        )->label(); ?>

                        <?php
                        $specialties = [];
                        if (in_array(
                            $model->_education_type,
                            [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER],
                            true
                        )) {
                            $specialties = ESpecialty::getHigherSpecialtyByType($model->_education_type, $model->_faculty);
                        } elseif (in_array(
                            $model->_education_type,
                            [EducationType::EDUCATION_TYPE_DSC, EducationType::EDUCATION_TYPE_PHD],
                            true
                        )) {
                            $specialties = ESpecialty::getDoctorateSpecialtyList();
                        }
                        ?>
                        <?= $form->field($model, '_specialty')->widget(
                            DepDrop::classname(),
                            [
                                'data' => $specialties,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => '_specialty',
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_faculty', '_education_type'],
                                    'url' => Url::to(['/ajax/get-specialties-by-faculty']),
                                ],
                            ]
                        ) ?>
                        <?= $form->field($model, '_group')->widget(
                            DepDrop::classname(),
                            [
                                'data' => ($model->isNewRecord && $model->_specialty === null) ? [] : EGroup::getOptionsBySpecialty($model->_specialty),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => '_group',
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_specialty'],
                                    'url' => Url::to(['/ajax/get-specialty-groups']),
                                ],
                            ]
                        )->label(); ?>
                        <?= $form->field($model, '_student')->widget(
                            DepDrop::classname(),
                            [
                                'data' => ($model->isNewRecord && $model->_group === null) ? [] : EGroup::getGroupStudentsOptions($model->_group),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => '_student',
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_group'],
                                    'url' => Url::to(['/ajax/get-group-students']),
                                ],
                            ]
                        )->label(); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'work_name')->textInput(['maxlength' => true]); ?>
                        <?= $form->field($model, 'supervisor_name')->textInput(['maxlength' => true]); ?>
                        <?= $form->field($model, 'supervisor_work')->textInput(['maxlength' => true]); ?>
                        <?= $form->field($model, 'advisor_name')->textInput(['maxlength' => true]); ?>
                        <?= $form->field($model, 'advisor_work')->textInput(['maxlength' => true]); ?>
                        <div class="row">
                            <div class="col col-md-7">
                                <?= $form->field($model, '_decree')->widget(Select2Default::class, [
                                    'data' => EDecree::getOptions($model->_faculty, DecreeType::TYPE_GRADUATE_WORK),
                                    'hideSearch' => false,
                                ]); ?>
                            </div>
                            <div class="col col-md-5">
                                <?= $form->field($model, '_education_year')->widget(
                                    Select2Default::classname(),
                                    [
                                        'data' => EducationYear::getClassifierOptions(),
                                        'allowClear' => true,
                                        'options' => [
                                            'id' => '_education_year',
                                        ],
                                        'placeholder' => __('-Choose Education Year-'),
                                    ]
                                )->label(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord): ?>
                        <?= $this->getResourceLink(
                            __('Delete'),
                            ['archive/graduate-work-edit', 'id' => $model->id, 'delete' => 1],
                            ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]
                        ) ?>
                    <?php endif; ?>
                    <?= Html::submitButton(
                        '<i class="fa fa-check"></i> ' . __('Save'),
                        ['class' => 'btn btn-primary btn-flat']
                    ) ?>
                </div>
            </div>
            <?php
            ActiveForm::end(); ?>
        </div>
    </div>
    <?php
    $this->registerJs(
        '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
    )
    ?>

    <?php
    Pjax::end() ?>
