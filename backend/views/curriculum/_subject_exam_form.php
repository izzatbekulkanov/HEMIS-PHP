<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ESubjectExamSchedule;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\GradeType;
use common\models\system\classifier\ExamType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

\kartik\date\DatePickerAsset::registerBundle($this, '3.x');
?>
<?//php Pjax::begin([]) ?>
    <div class="row">
        <?//php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'enableClientValidation' => true, 'validateOnSubmit' => true, 'options' => ['data-pjax' => false]]); ?>
        <?//php $form = ActiveForm::begin(['enableAjaxValidation' => false,  'validateOnSubmit' => true, 'options' => ['data-pjax' => true]]); ?>

        <?php
            if($curriculum_subject->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
                $min_border = round(GradeType::getGradeByCode($curriculum_subject->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border, 0);
                $max_border = round(GradeType::getGradeByCode($curriculum_subject->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border, 0);
            }
            else{
                $max_border = GradeType::getGradeByCode($curriculum_subject->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border;
                $min_border = 0;
            }
        ?>
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'options' => ['data-pjax' => true]]); ?>
        <?php echo $form->errorSummary($model);?>
        <div class='box-body'>
            <div class="row">
                <div class="col col-md-12">
                    <?= $form->field($model, '_exam_type')->widget(Select2Default::classname(), [
                        'data' =>$exam_type_list,
                        'allowClear' => false,
                        'hideSearch' => false,
                        'disabled' => (count(ESubjectExamSchedule::getTeachersByCurriculumSemesterSubjectExam($model->_curriculum, $model->_semester, $model->_subject, $model->_exam_type)) > 0),

                    ]) ?>

                    <?= $form->field($model, 'max_ball')->textInput(
                            [
                                'type' =>'number',
                                'maxlength' => true,
                                'id' => 'max_ball',
                                'min'=>$min_border,
                                'max'=>$max_border,
                                'step'=>1,
                            ]) ?>

                </div>
            </div>
        </div>

        <div class='box-footer text-right'>
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>
            <?php if(!$model->isNewRecord): ?>
                <?= $this->getResourceLink(__('Delete'), ['curriculum/curriculum-subject-edit', 'id' => $curriculum_subject->id, 'exam' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
            <?php endif;?>
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>

        </div>


        <?php ActiveForm::end(); ?>

    </div>

<?//php Pjax::end() ?>

<?php

$script = <<< JS
    setDeleteButton();
   
JS;
$this->registerJs($script);
?>
