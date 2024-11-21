<?php

use common\models\curriculum\ESubject;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\student\EStudent;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this \backend\components\View
 * @var $student EStudent
 * @var $subject ESubject
 */
$this->title = __('Rating subject: {subject}', ['subject' => $subject->subject->name]);
$max = GradeType::getGradeByCode($subject->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border;
$min = GradeType::getGradeByCode($subject->curriculum->_marking_system, GradeType::GRADE_TYPE_THREE)->min_border;

?>

<?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col col-md-12 col-lg-12">
            <div class="box box-info ">
                <div class="box-body">
                    <div class="row">
                        <div class="col col-md-12">
                            <?= $form->field($model, 'subject_name')->textInput()->label() ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($model, 'total_acload')->textInput(['maxlength' => true, 'required' => true])->label() ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($model, 'credit')->textInput(['maxlength' => true, 'required' => true])->label() ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($model, 'total_point')->input('number', ['min' => $min, 'max' => $max, 'step' => 0.01, 'required' => true])->label() ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>