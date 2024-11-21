<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\ESubjectSchedule;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\classifier\TrainingType;
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
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'options' => ['data-pjax' => true]]); ?>
        <?//php $form = ActiveForm::begin([ 'options' => ['data-pjax' => true]]); ?>
        <?php echo $form->errorSummary($model);?>
        <div class='box-body'>
            <div class="row">
                <div class="col col-md-12">
                    <?= $form->field($model, '_training_type')->widget(Select2Default::classname(), [
                        'data' => TrainingType::getClassifierOptions(),
                        'allowClear' => false,
                        'hideSearch' => false,
                        'disabled' => (count(ESubjectSchedule::getTeachersByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $model->_training_type)) > 0 || count(ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $model->_training_type)) > 0),
                    ]) ?>

                    <?= $form->field($model, 'academic_load')->textInput(['maxlength' => true, 'id' => 'academic_load']) ?>

                </div>
            </div>
        </div>

        <div class='box-footer text-right'>
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>
            <?php if(!$model->isNewRecord): ?>
                <?= $this->getResourceLink(__('Delete'), ['curriculum/curriculum-subject-edit', 'id' => $curriculum_subject->id, 'detail' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
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