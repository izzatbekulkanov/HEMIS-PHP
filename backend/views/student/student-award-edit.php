<?php

use backend\components\View;
use backend\widgets\Select2Default;
use common\models\curriculum\ECurriculum;
use common\models\student\EGroup;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\StudentSuccess;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this View
 */
$this->title = $model->isNewRecord ? __('Create Award') : __('Manage Award');
$this->params['breadcrumbs'][] = ['url' => ['student/student-award'], 'label' => __('Student Award')];
$this->params['breadcrumbs'][] = $this->title;
if ($this->_user()->role->isDeanRole() && Yii::$app->user->identity->employee->deanFaculties) {
    $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
} else {
    $faculty = "";
}
?>
<?php
$form = ActiveForm::begin(['enableAjaxValidation' => $model->isNewRecord, 'options' => ['data-pjax' => 0]]); ?>

<div class="row">
    <div class="col col-md-12" id="sidebar">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-6">
                        <div class="row">
                            <div class="col col-md-6">
                                <?= $form->field($model, '_education_type')->widget(
                                    Select2Default::classname(),
                                    [
                                        'data' => EducationType::getHighers(),
                                        'allowClear' => true,
                                        'options' => [
                                            'id' => '_education_type'
                                        ]
                                    ]
                                ) ?>
                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($model, '_education_form')->widget(
                                    Select2Default::classname(),
                                    [
                                        'data' => EducationForm::getClassifierOptions(),
                                        'allowClear' => true,
                                        'options' => [
                                            'id' => '_education_form'
                                        ]
                                    ]
                                ) ?>
                            </div>
                        </div>
                        <?= $form->field($model, '_curriculum')->widget(
                            DepDrop::classname(),
                            [
                                'data' => $model->isNewRecord ? [] : ECurriculum::getOptions($faculty),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => '_curriculum',
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_education_type', '_education_form'],
                                    'url' => Url::to(['/ajax/get-curriculums']),
                                ],
                            ]
                        )->label(__('Curriculum Curriculum')); ?>
                        <div class="row">
                            <div class="col col-md-6">
                                <?= $form->field($model, '_student_level')->widget(
                                    Select2Default::classname(),
                                    [
                                        'data' => Course::getClassifierOptions(),
                                        'allowClear' => true,
                                        'options' => [
                                        ]
                                    ]
                                ); ?>
                            </div>
                            <div class="col col-md-6">
                                <?= $form->field($model, '_student_group')->widget(
                                    DepDrop::classname(),
                                    [
                                        'data' => $model->isNewRecord ? [] : EGroup::getOptions($model->_curriculum),
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
                                            'depends' => ['_curriculum'/*, '_education_type', '_education_form'*/],
                                            'url' => Url::to(['/ajax/get-groups-by-curriculum']),
                                        ],
                                    ]
                                )->label(__('Student group')) ?>
                            </div>
                        </div>
                        <?= $form->field($model, '_student')->widget(
                            DepDrop::classname(),
                            [
                                'data' => $model->isNewRecord ? [] : EGroup::getGroupStudentsOptions(
                                    $model->_student_group
                                ),
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
                        ); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, '_award_group')->widget(
                            Select2Default::classname(),
                            [
                                'data' => StudentSuccess::getParentClassifierOptions(),
                                'allowClear' => true,
                                'options' => [
                                    'id' => '_award_group'
                                ]
                            ]
                        ); ?>
                        <?= $form->field($model, '_award_category')->widget(
                            DepDrop::classname(),
                            [
                                'data' => $model->isNewRecord ? [] : ArrayHelper::map(
                                    StudentSuccess::getChildrenOption($model->_award_group),
                                    'code',
                                    'name'
                                ),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => '_award',
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_award_group'],
                                    'url' => Url::to(['/ajax/get-award']),
                                ],
                            ]
                        ); ?>
                        <?= $form->field($model, 'award_document')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'award_year')->input('text', ['maxlength' => 4, 'pattern' => '\d+']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer text-right">
            <?php
            if (!$model->isNewRecord): ?>
                <?= $this->getResourceLink(
                    __('Delete'),
                    ['student/student-award-edit', 'id' => $model->id, 'delete' => 1],
                    ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]
                ) ?>
            <?php
            endif; ?>
            <?= Html::submitButton(
                '<i class="fa fa-check"></i> ' . __('Save'),
                ['class' => 'btn btn-primary btn-flat']
            ) ?>
        </div>
    </div>
</div>

</div>
<?php
ActiveForm::end(); ?>
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
