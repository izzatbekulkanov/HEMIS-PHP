<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\RatingGrade;
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
$this->params['breadcrumbs'][] = ['url' => ['performance/performance']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">

    <div class="col col-md-9 col-lg-9">

        <?php $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $model->_exam_type)->max_ball;?>
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
                    <?php if($subject->_rating_grade === RatingGrade::RATING_GRADE_SUBJECT) { ?>
                        <th colspan="<?=$colspan?>"><?= __('JN'); ?></th>
                        <th colspan="<?=$colspan?>"><?= __('ON'); ?></th>
                        <th rowspan="2"><?= __('JN+ON');?></th>
                        <th colspan="<?=$colspan?>"><?= __('YN');?></th>
                        <th rowspan="2"><?= __('Umumiy');?></th>
                    <?php } else { ?>
                        <th colspan="<?=$colspan?>"><?= __('Umumiy');?></th>
                    <?php } ?>
                </tr>
                <tr>
                    <?php if($subject->_rating_grade === RatingGrade::RATING_GRADE_SUBJECT) { ?>
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
                    <?php } else { ?>
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
                    <?php } ?>

                </tr>
                <?php
                $i=1;
                foreach($rated_students as $item){?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td style="text-align: left !important;vertical-align: middle !important;"><?php echo $item['name'];?></td>
                        <?php if($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) { ?>
                        <?php if($colspan==1){?>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT];?>
                            </td>
                        <?php } elseif($colspan==2){?>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST]; ?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT];?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_CURRENT];?>
                            </td>
                        <?php } ?>

                        <?php if($colspan==1){?>
                        <td>
                            <?=
                                 @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM];
                            ?>
                        </td>
                        <?php } elseif($colspan==2){?>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM];?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_MIDTERM];?>
                            </td>
                        <?php } ?>

                        <th>
                            <?= @$ball[$item['id']][ExamType::EXAM_TYPE_LIMIT];?>
                        </th>

                        <?php if($colspan==1){?>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL];?>
                            </td>
                        <?php } elseif($colspan==2){?>
                            <td>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL];?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];?>
                            </td>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_FINAL];?>
                            </td>
                        <?php } ?>
                        <th>
                            <?= @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                        </th>
                    <?php } else { ?>
                        <?php if($colspan==1){?>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                            </td>
                        <?php } elseif($colspan==2){?>
                            <td>
                                <?php echo @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                            </td>
                        <?php } elseif($colspan==3){?>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];?>
                            </td>
                            <td>
                                <?= @$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND];?>
                            </td>
                            <td>
                                <?=@$ball[$item['id']][ExamType::EXAM_TYPE_OVERALL];?>
                            </td>
                        <?php } ?>
                    <?php } ?>
                    </tr>
                <?php } ?>
            </table>
            <div class="box-footer">
                <div class="text-right">
                    <?= $this->getResourceLink(__('Print'), ['teacher/print-rating', 'education_year' => $education_year, 'semester' => $subject->_semester, 'group' => $group_model->id, 'subject' => $subject->_subject, 'final_exam_type' => $model->final_exam_type], ['class' => 'btn btn-success btn-flat','data-pjax' => '0',]) ?>
                </div>

            </div>
        </div>
    </div>

    <div class="col col-md-3 col-lg-3" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?//= __('Information') ?></h4>
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
                                return $data->examType ? strtoupper($data->examType->name). '<b> ['.ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($data->_curriculum, $data->_semester, $data->_subject, $data->_exam_type)->max_ball.'] </b>' : '';
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

<?php Pjax::end() ?>
