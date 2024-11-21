<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\attendance\EAttendanceControl;
use common\models\curriculum\Semester;
use common\models\curriculum\EStudentSubject;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use kartik\date\DatePicker;

\kartik\date\DatePickerAsset::registerBundle($this, '3.x');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Дарс жадваллари'), 'url' => ['time_tables']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin([]) ?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'enableClientValidation' => true, 'validateOnSubmit' => true, 'options' => ['data-pjax' => true]]); ?>

<?php
echo $form->errorSummary($model);
?>
<?php
    $disabled = false;
if (!$model->isNewRecord) {
    // echo $model->week->fullName;
    $attendance_control = EAttendanceControl::findOne(['_subject_schedule' => $model->id, '_employee' => $model->_employee]);
    $disabled = $attendance_control ? true : false;

} ?>
<?php echo $form->field($model, '_curriculum')->hiddenInput(['value' => $curriculum, 'id' => '_curriculum'])->label(false); ?>
<?php echo $form->field($model, '_education_year')->hiddenInput(['value' => $education_year, 'id' => '_education_year'])->label(false); ?>
<?php echo $form->field($model, '_semester')->hiddenInput(['value' => $semester, 'id' => '_semester'])->label(false); ?>
<?php echo $form->field($model, '_group')->hiddenInput(['value' => @$group, 'id' => '_group'])->label(false); ?>

    <div class="row">
        <div class="col col-md-12" id="sidebar">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col col-md-12">
                            <div class="row">
                                <div class="col col-md-6">
                                    <?= $form->field($model, 'lesson_date')->widget(DatePickerDefault::classname(), [
                                        'options' => [
                                            'placeholder' => __('YYYY-MM-DD'),
                                            'id' => 'lesson_date',
                                            'disabled' => $disabled,
                                        ],
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'daysOfWeekDisabled' => [0, 7],
                                            'weekStart' => '1',
                                        ]
                                    ]); ?>

                                    <?= $form->field($model, '_subject')->widget(Select2Default::classname(), [
                                        'data' => ArrayHelper::map($subjects, '_subject', 'subject.name'),
                                        'allowClear' => false,
                                        'hideSearch' => false,
                                        'disabled' => $disabled,
                                        'options' => [
                                            'id' => '_subject',

                                        ],
                                    ]) ?>
                                    <div class="row">
                                        <div class="col col-md-6">
                                            <?php
                                            $trainings = array();
                                            if ($model->_subject) {
                                                $trainings = ECurriculumSubjectDetail::getTrainingByCurriculumSemesterSubject($curriculum, $semester, $model->_subject);
                                            }
                                            ?>
                                            <?= $form->field($model, '_training_type')->widget(DepDrop::classname(), [
                                                'data' => ArrayHelper::map($trainings, '_training_type', 'trainingType.name'),
                                                'language' => 'en',
                                                'type' => DepDrop::TYPE_SELECT2,
                                                'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                                                'options' => [
                                                    'placeholder' => __('-Choose-'),
                                                    'id' => '_training_type',
                                                    'disabled' => $disabled,
                                                ],
                                                'pluginOptions' => [
                                                    'depends' => ['_curriculum', '_semester', '_subject'],
                                                    'placeholder' => __('-Choose-'),
                                                    'url' => Url::to(['/ajax/get-curriculum-subject-training']),
                                                ],
                                            ]) ?>
                                        </div>
                                        <div class="col col-md-6">
                                            <?= $form->field($model, 'additional')->textInput(['maxlength' => true, 'disabled' => $disabled,]) ?>
                                        </div>
                                    </div>


                                    <?php
                                    /*$topics = array();
                                    if($model->_subject && $model->_training_type){
                                        $topics = ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($curriculum, $semester, $model->_subject, $model->_training_type);
                                    }
                                    ?>
                                    <?= $form->field($model, '_subject_topic')->widget(DepDrop::classname(), [
                                        'data' =>  ArrayHelper::map($topics, 'id','name'),
                                        'language' => 'en',
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                                        'options' => [
                                            'placeholder' => __('-Choose-'),
                                            'id' => '_subject_topic',
                                        ],
                                        'pluginOptions' => [
                                            'depends'=>['_curriculum', '_semester', '_subject', '_training_type'],
                                            'placeholder' => __('-Choose-'),
                                            'url'=>Url::to(['/ajax/get-curriculum-subject-topic']),
                                        ],
                                    ])*/ ?>
                                </div>
                                <div class="col col-md-6">
                                    <div class="row">
                                        <div class="col col-md-6">
                                            <?= $form->field($model, '_lesson_pair')->widget(Select2Default::classname(), [
                                                'data' => ArrayHelper::map($pairs, 'code', 'fullName'),
                                                'allowClear' => false,
                                                'hideSearch' => false,
                                                'disabled' => $disabled,
                                            ]) ?>
                                        </div>
                                        <div class="col col-md-6">
                                            <?= $form->field($model, '_auditorium')->widget(Select2Default::classname(), [
                                                'data' => ArrayHelper::map($auditoriums, 'code', 'name'),
                                                'allowClear' => false,
                                                'hideSearch' => false,
                                                'disabled' => $disabled,
                                            ]) ?>
                                        </div>
                                    </div>
                                    <?= $form->field($model, '_employee')->widget(Select2Default::classname(), [
                                        'data' => $teachers,
                                        'allowClear' => false,
                                        'hideSearch' => false,
                                        'disabled' => $disabled,
                                    ]) ?>

                                    <?php
                                    $groups = array();
                                    if ($model->_subject) {
                                        $groups = EStudentSubject::getGroupsByCurriculumSemesterSubject($curriculum, $semester, $model->_subject);
                                    }
                                    ?>
                                    <?= $form->field($model, 'groups')->widget(DepDrop::classname(), [
                                        'data' => ArrayHelper::map($groups, '_group', 'group.name'),
                                        'language' => 'en',
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                                        'options' => [
                                            'placeholder' => __('-Choose-'),
                                            'id' => 'groups',
                                            'multiple' => ($model->isNewRecord),
                                            'disabled' => $disabled,
                                        ],
                                        'pluginOptions' => [
                                            'depends' => ['_curriculum', '_semester', '_subject', '_group'],
                                            'placeholder' => __('-Choose-'),
                                            'url' => Url::to(['/ajax/get-curriculum-semester-subject-groups']),
                                        ],
                                    ])->label(__('Group')) ?>

                                    <? /*= $form->field($model, 'groups')->widget(Select2Default::classname(), [
                                    'data' => ArrayHelper::map($groups, '_group','group.name'),
                                    'allowClear' => false,
                                    'options' => [
                                        'multiple' => ($model->isNewRecord),
                                    ],
                                ]) */ ?>


                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord): ?>
                        <?= Html::submitButton(__('Cancel'), ['class' => 'btn btn-default btn-flat', 'data-dismiss' => 'modal']) ?>
                        <?php if (!$disabled): ?>
                            <?= $this->getResourceLink(__('Delete'), ['curriculum/schedule-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat', 'data-confirm' => __('Are you sure to delete?'), 'data-pjax' => '0',]) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!$disabled): ?>
                        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
<?php ActiveForm::end(); ?>
<?php Pjax::end() ?>