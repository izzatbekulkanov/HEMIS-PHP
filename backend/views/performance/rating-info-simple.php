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

                    <th><?= __('№'); ?></th>
                    <th><?= __('Fullname of Student'); ?></th>
                    <th><?= __('Grade');?></th>
                </tr>
                <?php
                $i=1;
                foreach($rated_students as $item){?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td style="text-align: left !important;vertical-align: middle !important;"><?php echo $item['name'];?></td>
                        <td>
                            <?=@$ball[$item['id']];?>
                        </td>


                    </tr>
                <?php } ?>
            </table>
            <div class="box-footer">


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
