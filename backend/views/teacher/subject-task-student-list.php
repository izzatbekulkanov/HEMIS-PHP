<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
use common\models\system\classifier\TrainingType;
use common\models\performance\EPerformance;

use common\models\curriculum\Semester;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\datetime\DateTimePicker;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

//$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => $this->title];
//$this->params['breadcrumbs'][] = $subject->curriculum->name;
//$this->params['breadcrumbs'][] = $subject->semester->name;
//$this->params['breadcrumbs'][] = $subject->subject->name;
$training = TrainingType::findOne($training_type)->name;
$this->title = "{$subject->subject->name} ($training | {$subject->semester->name} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => $this->title];

$this->params['breadcrumbs'][] = $model->name;
?>
<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class='box-body no-padding'>



            <?php
                $new_students = array();
                @$semestr_start_date = Semester::getByCurriculumSemester($model->_curriculum, $model->_semester);
                $students = EStudentSubject::getStudentsByYearSemesterGroups($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $gr_ids);
                if (count($taskDataProvider->getModels()) > 0){
                    foreach ($students as $st) {
                            $student = ESubjectTaskStudent::findOne([
                                '_student' => $st->_student,
                                '_subject_task' => $model->id,
                                '_curriculum' => $model->_curriculum,
                                '_subject' => $model->_subject,
                                //'active' => ESubjectTaskStudent::STATUS_DISABLE,
                            ]);
                            if ($student === null) {
                                $new_students [$st->id] = $st->id;
                            }
                    }
                }

            ?>


            <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th>
                                <?php
                                /*echo CheckBo::widget([
                                    'name'      => "all",
                                    //'attribute' => $st->_student,
                                    'type'  => CheckBo::TYPE_CHECKBOX,
                                    'id'=>'ckbCheckAll',
                            //        'js'=>"onclick:toggle(this);"
                                ]);*/

                                echo Html::checkbox('all', false, ['id' => 'ckbCheckAll', 'onclick'=>"toggle(this);"])
                                ?>
                            </th>
                            <th><?= __('Student');?></th>
                            <th><?= __('Group');?></th>
                            <th><?= __('Task Status');?></th>
                            <th><?= __('Final Exam Type');?></th>
                            <th><?= __('Deadline');?></th>
                            <th><?= __('Date');?></th>
                        </tr>
                    </thead>

                    <tbody>
                    <?= Html::beginForm();?>
                    <?= Html::hiddenInput( 'curriculum', @$model->_curriculum,  ['id' => 'curriculum']);?>
                    <?= Html::hiddenInput( 'semester', @$model->_semester,  ['id' => 'semester']);?>
                    <?= Html::hiddenInput( 'subject', @$model->_subject,  ['id' => 'subject']);?>
                    <?= Html::hiddenInput( 'training_type', @$model->_training_type,  ['id' => 'training_type']);?>
                    <?= Html::hiddenInput( 'education_lang', @$model->_language,  ['id' => 'education_lang']);?>
                    <?= Html::hiddenInput( 'code', $model->id,  ['id' => 'code']);?>

                    <?php $i=1;?>
                    <?php if (count($taskDataProvider->getModels()) == 0){ ?>
                        <?php
                            foreach ($students as $st) {
                                ?>
                                <tr>
                                    <td><?= $i++;?></td>
                                    <td>

                                        <?php
                                            /*echo CheckBo::widget([
                                                'name'      => "student[$st->_student]",
                                            //    'attribute' => $st->_student,
                                                'type'  => CheckBo::TYPE_CHECKBOX,
                                                'id'=>'student'.$st->_student,
                                                'class' => 'checkBoxClass'
                                            ]);*/
                                        echo Html::checkbox("student[$st->_student]", false, ['id' => 'student'.$st->_student]);
                                        ?>

                                    </td>
                                    <td><?= $st->student->fullName?></td>
                                    <td><?= $st->group->name?></td>
                                    <td><?= __('Task no assignment'); ?></td>
                                    <td>
                                        <?php
                                            echo Select2::widget([
                                                'name' => '_final_exam_type[' . $st->_student . ']',
                                                'data' => $final_exam_types,
                                                'theme' => Select2::THEME_DEFAULT,

                                                'options' => [
                                                    'multiple' => false,

                                                ],
                                                'pluginOptions' => [
                                                    'allowClear' => false,

                                                ],
                                            ]);
                                        ?>
                                    <td>
                                        <?php
                                            echo DateTimePicker::widget([
                                                'name' => 'deadline['.$st->_student.']',
                                                'value' => Yii::$app->formatter->asDatetime($model->deadline->getTimestamp(), 'php:Y-m-d H:i'),
                                              //  'convertFormat' => true,
                                                'readonly' => true,
                                                'layout' => '{picker}{input}',
                                                'pluginOptions' => [
                                                    'autoclose'=>true,
                                                    'format' => 'yyyy-mm-dd hh:i',
                                                    'placeholder' => __('YYYY-MM-DD H:i'),
                                                    'startDate'=> Yii::$app->formatter->asDatetime(@$semestr_start_date->start_date->getTimestamp(), 'php:Y-m-d H:i'),
                                                   // 'endDate'=> Yii::$app->formatter->asDatetime($model->deadline->getTimestamp(), 'php:Y-m-d H:i'),
                                                    'todayHighlight' => true,
                                                ],

                                            ]);
                                         ?>
                                    </td>
                                    <td>-</td>

                                </tr>
                                <?php
                            }
                        ?>
                        <?php } ?>
                        <?php if(count($new_students) > 0){ ?>
                                <?php
                                    foreach ($new_students as $new_st) {
                                    $new = EStudentSubject::findOne($new_st);
                                    //$i=1;
                                ?>
                                    <tr>
                                        <td><?= $i++;?></td>
                                        <td>
                                            <?php
                                               /* echo CheckBo::widget([
                                                    'name'      => "student[$new->_student]",
                                                //    'attribute' => $new->_student,
                                                    'type'  => CheckBo::TYPE_CHECKBOX,
                                                    'id'=>'student'.$new->_student,
                                                    'class' => 'checkBoxClass'
                                                ]);*/
                                            echo Html::checkbox("student[$new->_student]", false, ['id' => 'student'.$new->_student]);
                                            ?>
                                        </td>
                                        <td><?= $new->student->fullName?></td>
                                        <td><?= $new->group->name?></td>
                                        <td><?= __('Task no assignment'); ?></td>
                                        <td>
                                            <?php
                                            echo Select2::widget([
                                                'name' => '_final_exam_type[' . $new->_student . ']',
                                                'data' => $final_exam_types,
                                                'theme' => Select2::THEME_DEFAULT,

                                                'options' => [
                                                    'multiple' => false,

                                                ],
                                                'pluginOptions' => [
                                                    'allowClear' => false,

                                                ],
                                            ]);
                                            ?>
                                        </td>

                                        <td><?php
                                            echo DateTimePicker::widget([
                                                'name' => 'deadline['.$new->_student.']',
                                                'value' => Yii::$app->formatter->asDatetime($model->deadline->getTimestamp(), 'php:Y-m-d H:i'),
                                               // 'convertFormat' => true,
                                                'readonly' => true,
                                                'layout' => '{picker}{input}',
                                                'pluginOptions' => [
                                                    'autoclose'=>true,
                                                    'format' => 'yyyy-mm-dd hh:i',
                                                    'placeholder' => __('YYYY-MM-DD H:i'),
                                                    'startDate'=> Yii::$app->formatter->asDatetime(@$semestr_start_date->start_date->getTimestamp(), 'php:Y-m-d H:i'),
                                                  //  'endDate'=> Yii::$app->formatter->asDatetime($model->deadline->getTimestamp(), 'php:Y-m-d H:i'),

                                                    'todayHighlight' => true
                                                ],

                                            ]);
                                            ?></td>
                                        <td>-</td>
                                    </tr>
                                    <?php
                                }
                            ?>
                        <?php } ?>

                <?php if (count($taskDataProvider->getModels()) > 0){ ?>
                <?php
                foreach ($taskDataProvider->getModels() as $st) {
                    $marked = 0;
                    @$marked = EStudentTaskActivity::getMarkBySubjectTaskStudent($st->_subject_task, $st->_student)->mark;
                    $overall_ball = EPerformance::getPassedStudentByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st->_student);

                    if (/*$st->_task_status != ESubjectTaskStudent::TASK_STATUS_PASSED && */($marked < $min_border) && (!isset($overall_ball->_student))) {
                    ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td>
                            <?php
                            if (/*$st->_task_status != ESubjectTaskStudent::TASK_STATUS_PASSED &&*/ ($marked < $min_border) && (!isset($overall_ball->_student))){
                                /*echo CheckBo::widget([
                                    'name'      => "student[$st->_student]",
                                   // 'attribute' => $st->_student,
                                    'type'  => CheckBo::TYPE_CHECKBOX,
                                    'id'=>'student'.$st->_student,
                                    'class' => 'checkBoxClass'
                                ]);*/
                                echo Html::checkbox("student[$st->_student]", false, ['id' => 'student'.$st->_student]);
                            }
                            ?>
                        </td>
                        <td><?= $st->student->fullName ?></td>
                        <td><?= $st->group->name ?></td>
                        <td>
                            <?php
                            if ($st->_task_status == ESubjectTaskStudent::TASK_STATUS_GIVEN)
                                echo $st->taskStatusOptions[$st->_task_status];
                            else
                                echo Html::a($st->taskStatusOptions[$st->_task_status], ['answer-list',
                                    'subject_task' => $st->_subject_task,
                                    'student' => $st->_student,
                                ], ['data-pjax' => 0]
                                );
                            ?>
                        </td>
                        <td>
                            <?php
                                if (/*$st->_task_status == ESubjectTaskStudent::TASK_STATUS_PASSED || */($marked > $min_border) ||  (isset($overall_ball->_student))){
                                      echo $st->finalExamType->name;
                                }
                                else {
                                echo Select2::widget([
                                    'name' => '_final_exam_type[' . $st->_student . ']',
                                    'data' => $final_exam_types,
                                    'value' => $st->_final_exam_type,
                                    'theme' => Select2::THEME_DEFAULT,

                                    'options' => [
                                        'multiple' => false,

                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => false,

                                    ],
                                ]);
                            } ?>
                        </td>
                        <td>
                            <?php
                            if (/*$st->_task_status == ESubjectTaskStudent::TASK_STATUS_PASSED ||*/ ($marked > $min_border) ||  (isset($overall_ball->_student))){
                                echo Yii::$app->formatter->asDatetime($st->deadline->getTimestamp(), 'php:d.m.Y H:i');
                            }
                            else{
                                echo DateTimePicker::widget([
                                    'name' => 'deadline['.$st->_student.']',
                                    'value' => Yii::$app->formatter->asDatetime($st->deadline->getTimestamp(), 'php:Y-m-d H:i'),
                                    'convertFormat' => true,
                                    'readonly' => true,
                                    'layout' => '{picker}{input}',
                                    'pluginOptions' => [
                                        'autoclose'=>true,
                                        'format' => 'php:Y-m-d H:i',
                                        'placeholder' => __('YYYY-MM-DD H:i'),
//                                        'startDate' => $model->deadline,
                                        'startDate'=> Yii::$app->formatter->asDatetime(@$semestr_start_date->start_date->getTimestamp(), 'php:Y-m-d H:i'),
                                       // 'endDate'=> Yii::$app->formatter->asDatetime($model->deadline->getTimestamp(), 'php:Y-m-d H:i'),
                                        'todayHighlight' => true
                                    ],

                                ]);
                            } ?>
                        </td>
                        <td>
                            <?php
                            if($st->_task_status == ESubjectTaskStudent::TASK_STATUS_GIVEN) {
                                if(!empty($st->created_at))
                                    echo  Yii::$app->formatter->asDate($st->created_at->getTimestamp(), 'php:d.m.Y H:i:s');
                            }
                            elseif($st->_task_status == ESubjectTaskStudent::TASK_STATUS_PASSED) {
                                @$activity = EStudentTaskActivity::getLastBySubjectTask($st->id);
                                if(!empty($activity->send_date))
                                    echo Yii::$app->formatter->asDate(@$activity->send_date->getTimestamp(), 'php:d.m.Y H:i:s');
                            }
                            elseif($st->_task_status == ESubjectTaskStudent::TASK_STATUS_RATED) {
                                @$activity = EStudentTaskActivity::getMarkBySubjectTask($st->id);
                                if(!empty($activity->marked_date))
                                    echo Yii::$app->formatter->asDate(@$activity->marked_date->getTimestamp(), 'php:d.m.Y H:i:s');
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    }
                }
                ?>
            <?php } ?>

                    <?= Html::endForm();?>
        </tbody>

        </table>
    </div>
    </div>
    <div class='box-footer '>
        <div class="text-right">

            <?php
                $label = "";
                $label = ($model->active) ? __("Unpublish") : __("Publish");
            ?>
            <?= Html::button('<i class="fa fa-check"></i> ' . __("Publish"), ['class' => 'btn btn-success btn-flat', 'id' => 'assign', 'type' => 'button']) ?>

            <?/*= $this->getResourceLink($label, ['teacher/subject-task-list',
                'curriculum' => $model->_curriculum,
                'semester' => $model->_semester,
                'subject' => $model->_subject,
                'training_type' => $model->_training_type,
                'education_lang' => $model->_language,
                'code' => $model->id,
                //'active' => 1,
                'send' => 1,
           ],
                ['class' => 'btn btn-success btn-flat', 'data-pjax' => 0]) */?>
        </div>
    </div>
    </div>
    </div>

    <?php
    $script = <<< JS
  	$("#assign").click(function(){
	    var final_exam_type = $('[name*=\'_final_exam_type\']').serialize();
	    var deadline = $('[name*=\'deadline\']').serialize();
	    var student = $('[name*=\'student\']').serialize();
	    
	    var curriculum =  $('#curriculum').val();
		var semester =  $('#semester').val();
		var subject =  $('#subject').val();
		var training_type =  $('#training_type').val();
		var education_lang =  $('#education_lang').val();
		var code =  $('#code').val();
		var send =  1;
		if(final_exam_type.length&&deadline.length&&student.length&&curriculum&&semester&&subject&&training_type&&education_lang&&code&&send)
		$.post({
           url: '/teacher/subject-task-send',
           data: {curriculum: curriculum, semester: semester, subject: subject, training_type: training_type, education_lang: education_lang, code: code, send:send, student:student, final_exam_type:final_exam_type, deadline:deadline},
           dataType:"json",
        });
	});
JS;
    $this->registerJs($script);
    ?>
    <script type="text/javascript">
        function toggle(source) {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }
    </script>
    <?//php Pjax::end() ?>


