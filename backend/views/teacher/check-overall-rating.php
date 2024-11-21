<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
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
use common\models\system\classifier\ExamType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\GradeType;
use common\models\system\classifier\FinalExamType;

$this->params['breadcrumbs'][] = ['url' => ['teacher/final-exam-table']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col col-md-9 col-lg-9">
        <?= Html::beginForm(); ?>
        <?php
/*
        $min_border = 0;
        $limit = $model->group->curriculum->markingSystem->minimum_limit;
        $limit_ball = "";
        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
            $min_border = GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border;
            $limit_ball = $overall_ball;
        }
        else{
            $limit_ball = $overall_ball - $max_ball;
        }*/
        ?>
        <?= Html::hiddenInput(
            'minimum_limit',
            $minimum_procent,
            [
                'class' => 'form-control',
                'id' => 'minimum_limit',
            ]
        )
        ?>
        <?= Html::hiddenInput(
            'limit',
            $limit_ball,
            [
                'class' => 'form-control',
                'id' => 'limit',
            ]
        )
        ?>
        <?= Html::hiddenInput(
            'final',
            ($max_ball),
            [
                'class' => 'form-control',
                'id' => 'max_ball',
            ]
        )
        ?>
        <?= Html::hiddenInput(
            'marking_system',
            ($model->curriculum->_marking_system),
            [
                'class' => 'form-control',
                'id' => 'marking_system',
            ]
        )
        ?>
        <?= Html::hiddenInput(
            'min_border',
            ($min_border),
            [
                'class' => 'form-control',
                'id' => 'min_border',
            ]
        )
        ?>
        <?php

        ?>
        <div class="box box-default ">
            <table class="table table-bordered custom">
                <tr>
                    <?php
                    $colspan = 1;
                    if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST){
                        $colspan = 1;
                    }
                    elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND){
                        $colspan = 2;
                    }
                    elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD){
                        $colspan = 3;
                    }
                    ?>
                    <th rowspan="2"><?= __('№'); ?></th>
                    <th rowspan="2"><?= __('Fullname of Student'); ?></th>

                    <th colspan="<?=$colspan?>"><?= __('JN'); ?></th>
                    <th colspan="<?=$colspan?>"><?= __('ON'); ?></th>
                    <th rowspan="2"><?= __('JN+ON') . ' [' . $minimum_procent . '% ]'; ?></th>
                    <th colspan="<?=$colspan?>"><?= __('YN') . ' [' . ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_FINAL)->max_ball . ']'; ?></th>
                    <th rowspan="2"><?= __('Umumiy'); ?></th>

                </tr>

                <tr>
                        <?php if($colspan==1){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                        <?php } elseif($colspan==2){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_SECOND];?></th>
                        <?php } elseif($colspan==3){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_SECOND];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_THIRD];?></th>
                        <?php } ?>

                        <?php if($colspan==1){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                        <?php } elseif($colspan==2){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_SECOND];?></th>
                        <?php } elseif($colspan==3){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_SECOND];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_THIRD];?></th>
                        <?php } ?>

                        <?php if($colspan==1){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                        <?php } elseif($colspan==2){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_SECOND];?></th>
                        <?php } elseif($colspan==3){?>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_FIRST];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_SECOND];?></th>
                            <th><?= FinalExamType::getFinalExamTypeIds()[FinalExamType::FINAL_EXAM_TYPE_THIRD];?></th>
                        <?php } ?>
                </tr>

                <?php
                $i = 1;
                foreach ($rated_students as $item) {
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td style="text-align: left !important;vertical-align: middle !important;"><?php echo $item['name']; ?></td>
                        <?php if($colspan==1){?>
                        <td style="width:5%">
                            <?= Html::hiddenInput(
                                'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_CURRENT . ']',
                                (@$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT]),
                                [
                                    'class' => 'form-control',
                                    'readonly' => true,
                                    'disabled' => (!in_array(ExamType::EXAM_TYPE_CURRENT, $exams)),
                                    'id' => 'current_' . $item['id'].($current_active ? 'n' :'')
                                ]
                            )
                            ?>
                            <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT];?>
                    </td>
                        <?php } elseif($colspan==2){?>
                            <td style="width:5%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td style="width:5%">
                                <?php if (@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border):?>
                                <?= Html::hiddenInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_CURRENT . ']',
                                    (@$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT]),
                                    [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                        'disabled' => (!in_array(ExamType::EXAM_TYPE_CURRENT, $exams)),
                                        'id' => 'current_' . $item['id'].($current_active ? 'n' :'')
                                    ]
                                )
                                ?>
                                <?php endif; ?>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT];?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td style="width:5%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td style="width:5%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];?>
                            </td>
                            <td style="width:5%">
                            <?php if (@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border && @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < $min_border):?>
                                <?= Html::hiddenInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_CURRENT . ']',
                                    (@$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT]),
                                    [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                        'disabled' => (!in_array(ExamType::EXAM_TYPE_CURRENT, $exams)),
                                        'id' => 'current_' . $item['id'].($current_active ? 'n' :'')
                                    ]
                                )
                                ?>
                                <?php endif;?>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT];?>
                            </td>
                        <?php } ?>
                        <?php if($colspan==1){?>
                        <td style="width:5%">
                            <?= Html::hiddenInput(
                                'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_MIDTERM . ']',
                                (@$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM]),
                                [
                                    'class' => 'form-control',
                                    'readonly' => true,
                                    'disabled' => (!in_array(ExamType::EXAM_TYPE_MIDTERM, $exams)),
                                    'id' => 'midterm_' . $item['id'].($midterm_active ? 'n' :'')
                                ]
                            )
                            ?>
                            <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM];?>
                        </td>
                        <?php } elseif($colspan==2){?>
                            <td style="width:5%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td style="width:5%">
                            <?php if (@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border):?>
                                <?= Html::hiddenInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_MIDTERM . ']',
                                    (@$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM]),
                                    [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                        'disabled' => (!in_array(ExamType::EXAM_TYPE_MIDTERM, $exams)),
                                        'id' => 'midterm_' . $item['id'].($midterm_active ? 'n' :'')
                                    ]
                                )
                                ?>
                                <?php endif;?>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM];?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td style="width:5%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td style="width:5%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]; ?>
                            </td>
                            <td style="width:5%">
                                <?php if (@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border && @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < $min_border):?>
                                <?= Html::hiddenInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_MIDTERM . ']',
                                    (@$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM]),
                                    [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                        'disabled' => (!in_array(ExamType::EXAM_TYPE_MIDTERM, $exams)),
                                        'id' => 'midterm_' . $item['id'].($midterm_active ? 'n' :'')
                                    ]
                                )
                                ?>
                                <?php endif;?>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM];?>
                            </td>
                        <?php } ?>
                        <td style="width:8%">
                            <?php
                                if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST){
                            ?>
                                <?= Html::hiddenInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_LIMIT . ']',
                                    (@$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT]),
                                    [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                        'id' => 'limit_' . $item['id']
                                    ]
                                )
                                ?>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT];?>

                                <?php } elseif($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                    if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] >= ($minimum_procent * $max_ball/100)) {
                                        echo @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                                ?>
                                <?php } else  { ?>
                                    <?= Html::hiddenInput(
                                        'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_LIMIT . ']',
                                        (@$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT]),
                                        [
                                            'class' => 'form-control',
                                            'readonly' => true,
                                            'id' => 'limit_' . $item['id']
                                        ]
                                    )
                                    ?>
                                    <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT];?>
                                <?php } ?>
                                <?php } elseif($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                    if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] >= ($minimum_procent * $max_ball/100)) {
                                        echo @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                                    }
                                    elseif(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] >= ($minimum_procent * $max_ball/100)) {
                                        echo @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                        ?>
                                    <?php } else  { ?>
                                        <?= Html::hiddenInput(
                                            'student_id[' . $item['id']. '][' . ExamType::EXAM_TYPE_LIMIT . ']',
                                            (@$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT]),
                                            [
                                                'class' => 'form-control',
                                                'readonly' => true,
                                                'id' => 'limit_' . $item['id']
                                            ]
                                        )
                                        ?>
                                        <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT];?>

                                    <?php } ?>
                                <?php } ?>
                        </td>
                        <?php if($colspan==1){?>
                        <td style="width:10%">
                            <?php if(isset($ratings_passed_list_for_view[$item['id']])):
                                echo @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                            else:
                                ?>
                            <?= Html::textInput(
                                'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_FINAL . ']', (
                            @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL]
                            ),
                                [
                                    'class' => 'form-control',
                                    'type' => 'number',
                                    'min' => 0,
                                    'max' => $max_ball,
                                    'step' => 1,
                                    'id' => 'final_' . $item['id'],
                                    'required' => true,
                                ]
                            )
                            ?>
                            <?php endif;?>

                        </td>
                        <?php } elseif($colspan==2){?>
                            <td style="width:10%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td style="width:10%">
                                <?php
                                    if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < ($minimum_procent * $max_ball/100)){
                                ?>
                                    <?php if(isset($ratings_passed_list_for_view[$item['id']])):
                                        echo @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                                    else:
                                        ?>
                                        <?= Html::textInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_FINAL . ']', (
                                @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL]
                                ),
                                    [
                                        'class' => 'form-control',
                                        'type' => 'number',
                                        'min' => 0,
                                        'max' => $max_ball,
                                        'step' => 1,
                                        'id' => 'final_' . $item['id'],
                                        'required' => true,
                                    ]
                                )
                                ?>
                                    <?php endif;?>
                                <?php } ?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td style="width:10%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td style="width:10%">
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];?>
                            </td>
                            <td style="width:10%">
                                <?php
                                    if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < ($minimum_procent * $max_ball/100) && @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < ($minimum_procent * $max_ball/100)){
                                ?>
                                    <?= Html::textInput(
                                        'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_FINAL . ']', (
                                    @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL]
                                    ),
                                        [
                                            'class' => 'form-control',
                                            'type' => 'number',
                                            'min' => 0,
                                            'max' => $max_ball,
                                            'step' => 1,
                                            'id' => 'final_' . $item['id'],
                                            'required' => true,
                                        ]
                                    )
                                    ?>
                                <?php } ?>
                            </td>
                        <?php } ?>
                        <td style="width:5%">
                            <?php
                                if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST){
                            ?>
                                    <?php if(isset($ratings_passed_list_for_view[$item['id']])):
                                        echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                                    else:
                                        ?>
                                    <?= Html::hiddenInput(
                                        'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_OVERALL . ']',
                                        (@$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL]),
                                        [
                                            'class' => 'form-control',
                                            'readonly' => true,
                                        ]
                                    )
                                    ?>
                                        <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                                    <?php endif;?>

                            <?php } elseif($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] >= ($minimum_procent * $max_ball/100)) {
                                    echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                                ?>
                            <?php } else  { ?>

                                <?= Html::hiddenInput(
                                    'student_id[' . $item['id'] . '][' . ExamType::EXAM_TYPE_OVERALL . ']',
                                    (@$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL]),
                                    [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                    ]
                                )
                                ?>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                            <?php } ?>

                            <?php } elseif($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                if(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] >= ($minimum_procent * $max_ball/100)) {
                                    echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                                } elseif(@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] >= ($minimum_procent * $max_ball/100)) {
                                    echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND];
                            ?>
                            <?php } else  { ?>

                            <?= Html::hiddenInput(
                                'student_id[' .$item['id'] . '][' . ExamType::EXAM_TYPE_OVERALL . ']',
                                (@$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL]),
                                [
                                    'class' => 'form-control',
                                    'readonly' => true,
                                ]
                            )
                            ?>
                            <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                            <?php } ?>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>
            </table>
            <div class="box-footer">
                <div class="text-right">
                    <?= $this->getResourceLink(__('Print Access'), ['teacher/check-overall-rating', 'id' => $model->id, 'access' => 1], ['class' => 'btn btn-info btn-flat', 'data-pjax' => '0', ]) ?>
                    <?= $this->getResourceLink(__('Print'), ['teacher/print-rating', 'education_year' => $model->_education_year, 'semester' => $model->_semester, 'group' => $model->_group, 'subject' => $model->_subject, 'final_exam_type' => $model->final_exam_type,], ['class' => 'btn btn-success btn-flat', 'data-pjax' => '0', 'style' => 'color:white;']) ?>
                    <?= Html::button(__('Fill From Exams'), [
                        'class' => 'btn btn-default btn-flat showModalButton',
                        'modal-class' => "modal-big",
                        'value' => currentTo(['choose_exam' => 1]),
                        'title' => __('Select Exam'),
                        'type'=>'button'
                    ]) ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat', 'name' => 'btn', 'id'=>'submit']) ?>

                </div>

            </div>
        </div>

        <?= Html::endForm(); ?>
    </div>

    <div class="col col-md-3 col-lg-3" id="sidebar">
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
                                return $data->examType ? strtoupper($data->examType->name) . '<b> [' . ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($data->_curriculum, $data->_semester, $data->_subject, $data->_exam_type)->max_ball . '] </b>' : '';
                            }
                        ],
                        [
                            'attribute' => 'final_exam_type',
                            'format' => 'raw',
                            'value' => function (ESubjectExamSchedule $data) {
                                return $data->finalExamType ? strtoupper($data->finalExamType->name) : '';
                            }
                        ],
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
                                return $data->group ? '<b>' . $data->group->curriculum->markingSystem->name . '</b>' : '';
                            }
                        ],
                    ],
                ]) ?>
            </div>

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
<?php
$script = <<< JS
    $(function() {
        var limit = $("#limit").val();
        var minimum_limit = $("#minimum_limit").val();
        var max_ball = $("#max_ball").val();
        var marking_system = $("#marking_system").val();
        var min_border = $("#min_border").val();
        $("[id*='final_']").on('change', function () {
            var final_id = $(this).attr("id");
            var final = $(this).val();
            var id = final_id.split("_");
            //var id = final_id.substr(final_id.length - 2);
            
            var current = $("#limit_"+id[1]).val();
            if(marking_system != 12){
                if(current < (minimum_limit * limit/100)){
                    $(this).parent().addClass('has-error');
                    $("#limit_"+id).parent().addClass('has-error');
                    $(this).val('0');
                    
                }
                else{
                    $(this).parent().removeClass('has-error');
                    
                }
            }
           if(marking_system != 11){
                 if(final < (minimum_limit * max_ball/100)){
                    $(this).parent().addClass('has-error');
                    $("#limit_"+id).parent().addClass('has-error');
                    $(this).val('0');
                }
                else{
                    $(this).parent().removeClass('has-error');
                }
            }
            
            if(marking_system == 12){
                if($("#current_"+id[1]).val() < min_border){
                    $(this).parent().addClass('has-error');
                    $("#limit_"+id).parent().addClass('has-error');
                    $(this).val('0');
                }
                else{
                    $(this).parent().removeClass('has-error');
                }
                if($("#midterm_"+id[1]).val() < min_border){
                    $(this).parent().addClass('has-error');
                    $("#limit_"+id).parent().addClass('has-error');
                    $(this).val('0');
                }
                else{
                    $(this).parent().removeClass('has-error');
                }
            }
            
        });
    });
              
JS;
$this->registerJs($script);
?>
<?php
$script = <<< JS
	$("form").submit(function( event ) {
            var limit = $("#limit").val();
            var minimum_limit = $("#minimum_limit").val();
            var max_ball = $("#max_ball").val();
            var marking_system = $("#marking_system").val();
            var min_border = $("#min_border").val();
            $("[id*='final_']").each(function (i, ob) {
                var final_id = $(ob).attr("id");
                var final = $(ob).val();
                var id = final_id.split("_");
                var current = $("#limit_"+id[1]).val();
                if(marking_system != 12){
                    if(current < (minimum_limit * limit/100)){
                        $(ob).parent().addClass('has-error');
                        $("#limit_"+id).parent().addClass('has-error');
                        $(ob).val('0');
                    }
                    else{
                        $(ob).parent().removeClass('has-error');
                    }
                }
                if(marking_system != 11){
                    if(final < (minimum_limit * max_ball/100)){
                        $(ob).parent().addClass('has-error');
                        $("#limit_"+id).parent().addClass('has-error');
                        $(ob).val('0');
                    }
                    else{
                        $(ob).parent().removeClass('has-error');
                    }
                }
                if(marking_system == 12){
                    if($("#current_"+id[1]).val() < min_border){
                        $(ob).parent().addClass('has-error');
                        $("#limit_"+id).parent().addClass('has-error');
                        $(ob).val('0');
                    }
                    else{
                        $(ob).parent().removeClass('has-error');
                    }
                    if($("#midterm_"+id[1]).val() < min_border){
                        $(ob).parent().addClass('has-error');
                        $("#limit_"+id).parent().addClass('has-error');
                        $(ob).val('0');
                    }
                    else{
                        $(ob).parent().removeClass('has-error');
                    }
                }
                
            });
        });
JS;
$this->registerJs($script);
?>

