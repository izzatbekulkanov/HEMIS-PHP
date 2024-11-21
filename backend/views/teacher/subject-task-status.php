<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\TrainingType;
use common\models\system\classifier\Language;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\system\classifier\FinalExamType;
use yii\widgets\DetailView;
/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$training = $subject_task->trainingType->name;
$semester = Semester::getByCurriculumSemester($subject_task->_curriculum, $subject_task->_semester)->name;
$label = "{$subject_task->subject->name} ($training | {$semester} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-tasks'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => $prev_url, 'label' => $label];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">

    <div class="col col-md-8 col-lg-8">

            <div class="box box-default ">
                <div class="box-header bg-gray">
                    <?php if ($this->_user()->role->code !== \common\models\system\AdminRole::CODE_DEPARTMENT){ ?>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                                'data' => ArrayHelper::map($groups, '_group', 'group.name'),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>

                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_task_status')->widget(Select2Default::classname(), [
                                'data' => ESubjectTaskStudent::getTaskStatusOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_final_exam_type')->widget(Select2Default::classname(), [
                                'data' => FinalExamType::getClassifierOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <? /*<div class="col col-md-3">
                            <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                                'data' => ArrayHelper::map($dataProvider->getModels(), '_subject', 'subject.name'),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
 */?>
                        <?php ActiveForm::end(); ?>
                    </div>
                    <?php } ?>
                </div>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute'=>'_group',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data->group->name;
                            },
                        ],
                        [
                            'attribute'=>'_student',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data->student->fullName;
                            },
                        ],

                        /*[
                            'attribute'=>'_subject',
                            'format' => 'raw',
                            'value' => function($data){
                                return Html::a($data->subject->name,['subject-task-list',
                                    'curriculum' => $data->_curriculum,
                                    'semester' => $data->_semester,
                                    'subject' => $data->_subject,
                                    'training_type' => $data->_training_type,
                                ], ['data-pjax' => 0]
                                );
                            },
                        ],*/

                        [
                            'attribute'=>'_task_status',
                            'format' => 'raw',
                            'value' => function($data){
                                if($data->_task_status == ESubjectTaskStudent::TASK_STATUS_GIVEN) {
                                    return "<span style='color:red'>" . $data->taskStatusOptions[$data->_task_status] . "</span>";
                                }
                                else {
                                    $color = $data->_task_status == ESubjectTaskStudent::TASK_STATUS_PASSED ? "color:blue" : "color:green";
                                    return Html::a($data->taskStatusOptions[$data->_task_status], ['answer-list',
                                        'subject_task' => $data->_subject_task,
                                        'student' => $data->_student,
                                    ], ['data-pjax' => 0, 'style'=>$color]
                                    );
                                }
                            },
                        ],
                        [
                            'attribute'=>'_final_exam_type',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data->finalExamType->name;
                            },
                        ],
                        [
                            'attribute' => '_final_exam_type',
                            'label' => __('Mark'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data->_task_type == \common\models\curriculum\ESubjectTask::TASK_TYPE_TASK) {
                                    if ($data->_task_status == \common\models\curriculum\ESubjectTaskStudent::TASK_STATUS_RATED) {
                                        @$activity = \common\models\curriculum\EStudentTaskActivity::getMarkBySubjectTask($data->id);
                                        if (!empty($activity->mark))
                                            return $activity->mark;
                                        else
                                            return '-';
                                    }
                                    else
                                        return '-';
                                } elseif ($data->_task_type == \common\models\curriculum\ESubjectTask::TASK_TYPE_TEST) {
                                    if ($data->_task_status == \common\models\curriculum\ESubjectTaskStudent::TASK_STATUS_PASSED) {
                                        @$activity = \common\models\curriculum\EStudentTaskActivity::getLastBySubjectTask($data->id);
                                        if (!empty($activity->mark))
                                            return $activity->mark;
                                        else
                                            return '-';
                                    }
                                    else
                                        return '-';
                                }
                            }
                        ],

                        [
                            'attribute' => 'created_at',
                            'label' => __('Date'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                if($data->_task_status == \common\models\curriculum\ESubjectTaskStudent::TASK_STATUS_GIVEN) {
                                    if(!empty($data->created_at))
                                        return Yii::$app->formatter->asDate(@$data->created_at->getTimestamp(), 'php:d.m.Y H:i:s');
                                }
                                elseif($data->_task_status == \common\models\curriculum\ESubjectTaskStudent::TASK_STATUS_PASSED) {
                                    @$activity = \common\models\curriculum\EStudentTaskActivity::getLastBySubjectTask($data->id);
                                    if(!empty($activity->send_date))
                                        return Yii::$app->formatter->asDate(@$activity->send_date->getTimestamp(), 'php:d.m.Y H:i:s');
                                }
                                elseif($data->_task_status == \common\models\curriculum\ESubjectTaskStudent::TASK_STATUS_RATED) {
                                    @$activity = \common\models\curriculum\EStudentTaskActivity::getMarkBySubjectTask($data->id);
                                    if(!empty($activity->marked_date))
                                        return Yii::$app->formatter->asDate(@$activity->marked_date->getTimestamp(), 'php:d.m.Y H:i:s');
                                }

                            }
                        ],

                  ],
                ]); ?>
            </div>
    </div>

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $subject_task,
                    'attributes' => [
                        [
                            'attribute' => '_curriculum',
                            'label' => __('Curriculum Curriculum'),
                            'value' => function ($data) {
                                return $data->curriculum ? $data->curriculum->name : '';
                            }
                        ],
                        [
                            'attribute' => '_education_year',
                            'value' => function ($data) {
                                return $data->educationYear ? $data->educationYear->name : '';
                            }
                        ],
                        [
                            'attribute' => '_semester',
                            'value' => function ($data) {
                                return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                            },
                        ],
                        [
                            'attribute' => '_subject',
                            'value' => function ($data) {
                                return $data->subject ? $data->subject->name : '';
                            }
                        ],
                        [
                            'attribute' => '_training_type',
                            'value' => function ($data) {
                                return $data->trainingType ? $data->trainingType->name : '';
                            }
                        ],
                        [
                            'attribute' => 'id',
                            'label' => __('Name of Topic'),
                            'value' => function ($data) {
                                return $data->name;
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

<?php Pjax::end() ?>
