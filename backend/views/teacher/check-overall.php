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
use common\models\system\classifier\FinalExamType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\GradeType;

if($model->curriculumSubject->_rating_grade == \common\models\curriculum\RatingGrade::RATING_GRADE_SUBJECT_FINAL)
    $this->params['breadcrumbs'][] = ['url' => ['teacher/final-exam-table']];
else
    $this->params['breadcrumbs'][] = ['url' => ['teacher/other-exam-table']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">

    <div class="col col-md-8 col-lg-8">
        <?= Html::beginForm();?>
        <?php $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $model->_exam_type)->max_ball;?>
        <?

        $min_border = 0;
        $minimum_limit = $model->curriculum->markingSystem->minimum_limit;
        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
            $min_border = GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border;
            $max_ball = GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_FIVE)->max_border;
        }
        else{
            $minimum_limit = $model->curriculum->markingSystem->minimum_limit;
            $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject,  $model->_exam_type)->max_ball;
            $min_border = $minimum_limit * $max_ball/100;
        }

        echo Html::hiddenInput(
            'max_ball',
            $max_ball,
            [
                'class' => 'form-control',
                'id' => 'max_ball',
            ]
        );
        echo Html::hiddenInput(
            'minimum_limit',
            $minimum_limit,
            [
                'class' => 'form-control',
                'id' => 'minimum_limit',
            ]
        );
        echo Html::hiddenInput(
            'marking_system',
            ($model->curriculum->_marking_system),
            [
                'class' => 'form-control',
                'id' => 'marking_system',
            ]
        )


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
                    <th><?= __('Mark').' ['.$max_ball.']';?></th>
                    <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND):?>
                        <th>
                            <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type].' ['.(@$max_ball).']';?>
                        </th>
                    <?php endif; ?>
                    <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD):?>
                        <th>
                            <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type-1].' ['.(@$max_ball).']';?>
                        </th>
                        <th>
                            <?= FinalExamType::getFinalExamTypeIds()[$model->final_exam_type].' ['.(@$max_ball).']';?>
                        </th>
                    <?php endif; ?>
                </tr>
                <?php
                $i=1;
                foreach($rated_students as $item){?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo $item['name'];?></td>

                        <td style="width:15%">
                            <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || $model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD):?>
                                <?php echo @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST];?>
                            <?php else:?>
                                <?php if(isset($ratings_passed_list_for_view[$item['id']])):
                                    echo @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST];
                                    else:
                                ?>
                                    <?= Html::textInput(
                                            'student_id['.$item['id'].']', (
                                                    @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST]
                                            ),
                                            [
                                                'class' => 'form-control',
                                                'type' =>'number',
                                                'min'=>0,
                                                'max'=>$max_ball,
                                                'step'=>1,
                                                'required' => true,
                                                'id' => 'final_' . $item['id'],
                                            ]
                                    )
                                    ?>
                                    <?php endif;?>
                            <?php endif;?>
                        </td>
                        <?php if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND):?>
                            <td style="width:15%; text-align:center;">
                                <?php
                                @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST];
                                if(@$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST]>=$min_border):
                                    echo '-';
                                    ?>
                                <?php else:?>

                                    <?= Html::textInput(
                                        'student_id['.$item['id'].']', (
                                    @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_SECOND]
                                    ),
                                        [
                                            'class' => 'form-control',
                                            'type' =>'number',
                                            'min'=>0,
                                            'max'=>($max_ball),
                                            'min'=>@$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST],
                                            'step'=>1,
                                            'required' => true,
                                            'id' => 'final_' . $item['id'],
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
                                echo @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_SECOND];
                                ?>
                            </td>

                            <td style="width:15%; text-align:center;">
                                <?php
                                @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_SECOND];
                                if(@$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_FIRST]>=$min_border || @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_SECOND]>=$min_border):
                                    echo '-';
                                    ?>
                                <?php else:?>

                                    <?= Html::textInput(
                                        'student_id['.$item['id'].']', (
                                    @$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_THIRD]
                                    ),
                                        [
                                            'class' => 'form-control',
                                            'type' =>'number',
                                            'min'=>0,
                                            'max'=>($max_ball),
                                            'min'=>@$ball[$item['id']][FinalExamType::FINAL_EXAM_TYPE_SECOND],
                                            'step'=>1,
                                            'required' => true,
                                            'id' => 'final_' . $item['id'],
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
                    <?= $this->getResourceLink(__('Print'), ['teacher/print-rating', 'education_year' => $model->_education_year, 'semester' => $model->_semester, 'group' => $model->_group, 'subject' => $model->_subject, 'final_exam_type' => $model->final_exam_type,], ['class' => 'btn btn-success btn-flat', 'data-pjax' => '0', 'style' => 'color:white;']) ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>

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
                               $ball = ($data->group->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) ? GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_FIVE)->max_border : ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($data->_curriculum, $data->_semester, $data->_subject, $data->_exam_type)->max_ball;
                               return $data->examType ? strtoupper($data->examType->name). '<b> ['.$ball.'] </b>' : '';
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
<?php
$script = <<< JS
    $(function() {
        var minimum_limit = $("#minimum_limit").val();
        var max_ball = $("#max_ball").val();
        var marking_system = $("#marking_system").val();
        $("[id*='final_']").on('change', function () {
            var final_id = $(this).attr("id");
            var final = $(this).val();
            var id = final_id.split("_");
            
           if(marking_system != 11){
                 if(final < (minimum_limit * max_ball/100)){
                    $(this).parent().addClass('has-error');
                    $("#final_"+id).parent().addClass('has-error');
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
            var minimum_limit = $("#minimum_limit").val();
            var max_ball = $("#max_ball").val();
            var marking_system = $("#marking_system").val();
            $("[id*='final_']").each(function (i, ob) {
                var final_id = $(ob).attr("id");
                var final = $(ob).val();
                var id = final_id.split("_");
                var current = $("#limit_"+id[1]).val();
                if(marking_system != 11){
                    if(final < (minimum_limit * max_ball/100)){
                        $(ob).parent().addClass('has-error');
                        $("#final_"+id).parent().addClass('has-error');
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
<?php Pjax::end() ?>
