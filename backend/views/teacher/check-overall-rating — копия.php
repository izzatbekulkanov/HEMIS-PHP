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

$this->params['breadcrumbs'][] = ['url' => ['teacher/final-exam-table']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">
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
                                return $data->examType ? strtoupper($data->examType->name). '<b> ['.ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($data->_curriculum, $data->_semester, $data->_subject, $data->_exam_type)->max_ball.'] </b>' : '';
                            }
                        ],
                        [
                            'attribute' => 'exam_name',
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
    <div class="col col-md-8 col-lg-8">
        <?= Html::beginForm();?>
        <?php $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $model->_exam_type)->max_ball;?>
        <?= Html::hiddenInput(
            'max_ball',
            $max_ball,
            [
                'class' => 'form-control'
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
                    <?php foreach($examTypes as $exam){?>
                        <th><?= strtoupper($exam->examType->name).' ['.ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $exam->_exam_type)->max_ball.']';?></th>
                    <?php } ?>
                </tr>
                <?php
                $i=1;
                foreach($students as $item){?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo $item->student->fullName;?></td>
                        <?php foreach($examTypes as $exam){?>
                            <td style="width:15%">
                                <?php if($exam->_exam_type != ExamType::EXAM_TYPE_FINAL) {?>
                                    <?= Html::textInput(
                                        'student_id['.$item->_student.']['.$exam->_exam_type.']', (
                                                @$ball[$item->_student][$exam->_exam_type] = @$ball[$item->_student][$exam->_exam_type]!=0 ? @$ball[$item->_student][$exam->_exam_type] : ''
                                        ),
                                        [
                                            'class' => 'form-control',
                                           // 'type' =>'number',
                                            'readonly'=>true,
                                           // 'min'=>0,
                                            'max'=>$max_ball,
                                            'step'=>0.1,
                                        ]
                                    )
                                    ?>
                                <?php } else { ?>
                                    <?= Html::textInput(
                                        'student_id['.$item->_student.']['.$exam->_exam_type.']', (
                                            @$ball[$item->_student][$exam->_exam_type] = @$ball[$item->_student][$exam->_exam_type]!=0 ? @$ball[$item->_student][$exam->_exam_type] : ''
                                            ),
                                                [
                                                    'class' => 'form-control',
                                                    'type' =>'number',
                                                    'min'=>0,
                                                    'max'=>$max_ball,
                                                    'step'=>0.1,
                                                ]
                                        )
                                    ?>
                                <?php } ?>
                            </td>
                        <?php } ?>

                    </tr>
                <?php } ?>
                <tr>
                    <th colspan="4">
                        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
                        <?= Html::endForm();?> </th>
                </tr>
            </table>
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
