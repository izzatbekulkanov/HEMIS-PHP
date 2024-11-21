<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\MarkingSystem;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\ExamType;
use common\models\curriculum\GradeType;

$this->params['breadcrumbs'][] = ['url' => ['teacher/midterm-exam-table']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">

    <div class="col col-md-8 col-lg-8">
        <?= Html::beginForm();?>
        <?= Html::hiddenInput(
            'max_ball',
            @$max_ball,
            [
                'class' => 'form-control'
            ]
        );

        $min_border = 0;
        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
            $min_border = GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border;
        }
        else{
            $minimum_limit = $model->curriculum->markingSystem->minimum_limit;
           // $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject,  $model->_exam_type)->max_ball;
            //$overall_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_OVERALL)->max_ball;
            //$limit_ball = $overall_ball - $max_ball;
            $min_border = $minimum_limit * $max_ball/100;

        }
        ?>
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?//php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-2">
                        <div class="form-group">
                        </div>
                    </div>
                    <div class="col col-md-10">

                    </div>
                </div>
            </div>
            <table class="table table-striped table-bordered">
                <tr>
                    <th><?= __('№');?></th>
                    <th><?= __('Fullname of Student');?></th>
                    <?php if($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                        <th><?= __('Tasks Mark');?></th>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST):?>
                        <th>
                            <?= __('Mark').' ['.(@$max_ball).']';?>
                        </th>
                        <?php endif; ?>

                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND):?>
                            <th>
                                <?= __('Mark').' ['.(@$max_ball).']';?>
                            </th>
                            <th>
                                <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type].' ['.(@$max_ball).']';?>
                            </th>
                        <?php endif; ?>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD):?>
                            <th>
                                <?= __('Mark').' ['.(@$max_ball).']';?>
                            </th>
                            <th>
                                <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type-1].' ['.(@$max_ball).']';?>
                            </th>
                            <th>
                                <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type].' ['.(@$max_ball).']';?>
                            </th>
                        <?php endif; ?>
                    <?php else: ?>
                        <th><?= __('Tasks Mark').' ['.@$tasks->mark.']';?></th>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST):?>
                            <th><?= __('Mark').' ['.(@$max_ball-@$tasks->mark).']';?></th>
                        <?php endif; ?>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND):?>
                            <th><?= __('Mark').' ['.@$max_ball.']';?></th>
                            <th>
                                <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type].' ['.(@$max_ball-@$tasks->mark).']';?>
                            </th>
                        <?php endif; ?>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD):?>
                            <th><?= __('Mark').' ['.@$max_ball.']';?></th>
                            <th>
                                <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type-1].' ['.(@$max_ball).']';?>
                            </th>
                            <th>
                                <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type].' ['.(@$max_ball-@$tasks->mark).']';?>
                            </th>
                        <?php endif; ?>
                    <?php endif; ?>

                </tr>
                <?php
                $i=1;
                foreach($rated_students as $item){?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo $item['name'];?></td>

                        <td style="width:15%; text-align:center;">
                            <?php
                            $label = "";
                            @$label = @$ball[$item['id']][0]!=0 ? @$ball[$item['id']][0] .' / '.@$ball[$item['id']][3] : '';
                            echo Html::a($label, '#', [
                                    'class' => 'showModalButton ',
                                    'modal-class' => 'modal-lg',
                                    'title' => $model->subject->name,
                                    'value' => Url::to(['teacher/task-rating-info',
                                        'id' => $model->id,
                                        'student' => $item['id'],
                                    ]),
                                    'data-pjax' => 0
                                ]);
                            ?>
                        </td>
                        <td style="width:15%; text-align:center;">
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || $model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD):?>
                                <?php echo @$ball[$item['id']][1];?>
                            <?php else:?>
                                <?php
                                if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL] > 0 && @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL]==1):
                                       echo @$ball[$item['id']][1];
                                ?>
                                <?php else:?>
                            <?= Html::textInput(
                                'student_id['.$item['id'].']', (
                                //'student_id[' . $item->_student . '][' . ExamType::EXAM_TYPE_CURRENT . ']', (
                            @$ball[$item['id']][1]
                            ),
                                [
                                    'class' => 'form-control',
                                    'type' =>'number',
                                    'min'=>0,
                                    'max'=>($max_ball-@$tasks->mark),
                                    'step'=>1,
                                    'required'=>true,
                                    //'oninvalid' => "setCustomValidity('The value entered does not correspond to the maximum limit')"
                                ]
                            )
                            ?>
                                <?php endif;?>
                                <?php endif;?>
                        </td>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND):?>
                            <td style="width:15%; text-align:center;">
                                <?php

                                @$ball[$item['id']][1] ;
                                    /*if(@$ball[$item['id']][1]>=$min_border):
                                        echo @$ball[$item['id']][12] = @$ball[$item['id']][12] != 0 ? @$ball[$item['id']][12] : '';*/
                                    if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL] > 0 && @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL]==1):
                                        echo @$ball[$item['id']][12];
                                ?>
                                <?php else:?>

                                    <?= Html::textInput(
                                        'student_id['.$item['id'].']', (
                                    @$ball[$item['id']][12]
                                    ),
                                        [
                                            'class' => 'form-control',
                                            'type' =>'number',
                                            'min'=>0,
                                            'max'=>($max_ball-@$tasks->mark),
                                            'min'=> $model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE ? @$ball[$item['id']][1] : 0,
                                            'step'=>1,
                                            'required'=>true,
                                            //'oninvalid' => "setCustomValidity('The value entered does not correspond to the maximum limit')"
                                        ]
                                    )
                                    ?>
                                <?php endif;?>
                            </td>
                        <?php endif;?>

                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD):?>
                            <td style="width:15%; text-align:center;">
                            <?php
                                echo @$ball[$item['id']][12];
                                ?>
                            </td>

                            <td style="width:15%; text-align:center;">
                            <?php
                                @$ball[$item['id']][12];
                                //if(@$ball[$item['id']][1]>=$min_border || @$ball[$item['id']][12]>=$min_border):
                                //    echo @$ball[$item['id']][13] = @$ball[$item['id']][13] != 0 ? @$ball[$item['id']][13] : '';
                                if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL] > 0 && @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL]==1):
                                        echo @$ball[$item['id']][13];
                                    ?>
                                <?php else:?>

                                    <?= Html::textInput(
                                        'student_id['.$item['id'].']', (
                                    @$ball[$item['id']][13]
                                    ),
                                        [
                                            'class' => 'form-control',
                                            'type' =>'number',
                                            'min'=>0,
                                            'max'=>($max_ball-@$tasks->mark),
                                            'min'=> $model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE ? @$ball[$item['id']][12] : 0,

                                            'step'=>1,
                                            'required'=>true,
                                            //'oninvalid' => "setCustomValidity('The value entered does not correspond to the maximum limit')"
                                        ]
                                    )
                                    ?>
                                <?php endif;?>

                            </td>
                        <?php endif;?>

                    </tr>
                <?php } ?>
            </table>
            <div class="box-footer">
                <div class="text-right">
                    <?php if($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                        <?php
                        if($midterm):
                            $param = $test_done_students;
                        //else
                        //    $param = $task_done_students;
                        ?>
                        <?= Html::button(__('Fill From Tasks'), [
                            'class' => 'btn btn-default btn-flat showModalButton',
                            //'modal-class' => "modal-big",
                            'value' => currentTo(['get_tasks' => 1, 'count_task'=>$param]),
                            'title' => __('Get Marks From Tasks'),
                            'type'=>'button'
                        ]) ?>
                    <?php endif;?>
                    <?php endif;?>
                        <?php
                        if(!$midterm && $model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                echo Html::button(__('Fill from Current'), [
                                    'class' => 'btn btn-default btn-flat showModalButton',
                                    //'modal-class' => "modal-big",
                                    'modal-class' => 'modal-lg',
                                    'value' => currentTo(['get_current' => 1, 'grade_count'=>$grades_students]),
                                    'title' => __('Get Grades From Current'),
                                    'type' => 'button'
                                ])
                        ?>
                            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat', 'name' => 'btn']) ?>
                </div>
            </div>
        </div>
         <?= Html::endForm();?>
    </div>

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_subject',
                            'value' => function (ESubjectExamSchedule $data) {
                                return $data->subject ? $data->subject->name : '';
                            }
                        ],
                        [
                            'attribute' => '_exam_type',
                            'format' => 'raw',
                            'value' => function (ESubjectExamSchedule $data) {
                                return $data->examType ? strtoupper($data->examType->name). '<b> ['.@ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($data->_curriculum, $data->_semester, $data->_subject, $data->_exam_type)->max_ball.'] </b>' : '';
                            }
                        ],
                        [
                            'attribute' => 'final_exam_type',
                            'format' => 'raw',
                            'value' => function (ESubjectExamSchedule $data) {
                                return $data->finalExamType ? strtoupper($data->finalExamType->name) : '';
                            }
                        ],
                        /* [
                             'attribute' => 'exam_name',
                         ],*/
                        [
                            'attribute' => 'exam_date',
                            'value' => function (ESubjectExamSchedule $data) {
                                return Yii::$app->formatter->asDate($data->exam_date, 'dd-MM-Y');
                            }
                        ],
                        [
                            'attribute' => '_group',
                            'value' => function (ESubjectExamSchedule $data) {
                                return $data->group ? $data->group->name : '';
                            }
                        ],
                        [
                            'attribute' => '_group',
                            'format' => 'raw',
                            'label' => __('Marking System'),
                            'value' => function (ESubjectExamSchedule $data) {
                                return $data->group ? '<b>'.$data->group->curriculum->markingSystem->name.'</b>' : '';
                            }
                        ],
                    ],
                ]) ?>
            </div>
            <br/>

        </div>
    </div>

</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
