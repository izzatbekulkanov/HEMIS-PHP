<?php

use backend\widgets\GridView;
use backend\widgets\ListViewDefault;
use backend\widgets\SimpleNextPrevPager;
use common\models\curriculum\ESubjectSchedule;
use frontend\models\curriculum\StudentAttendance;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\StudentCurriculumSubject;
use frontend\models\system\StudentSchedule;
use frontend\models\curriculum\SubjectTaskActivity;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\ECurriculumSubject;
use common\models\system\classifier\TrainingType;
use frontend\models\curriculum\SubjectResource;
use frontend\models\curriculum\SubjectTask;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii2fullcalendar\yii2fullcalendar;

/* @var $cSemester \frontend\models\curriculum\StudentSemester */
/* @var $this \frontend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $item \common\models\curriculum\ECurriculumSubject */

$semester = $this->getSelectedSemester();
$searchModel = new StudentCurriculumSubject();
$dataProvider = $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester());

$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>

<?php foreach ($dataProvider as $row): $cSemester = $row['semester'];
    if ($cSemester->code != $semester->code) continue ?>
    <div class="row">
        <?php foreach ($row['subjects'] as $i => $item): ?>
            <?php $subject_meta = ECurriculumSubject::getByCurriculumSemesterSubject(@$item->_curriculum, @$item->_semester, @$item->_subject); ?>
            <div class="col col-md-4">
                <!-- Widget: user widget style 1 -->
                <div class="box box-widget widget-user">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-blue">
                        <h4 class="widget-user-username"><?= @$item->subject->name ?></h4>
                        <h5 class="widget-user-desc">
                            <?= @$subject_meta->subjectType->name ?> |
                            <?= __('{hour} hour', ['hour' => @$subject_meta->total_acload]) ?>
                            | <?= __('{credit} kredit', ['credit' => @$subject_meta->credit]) ?>
                        </h5>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                            <li>
                                <a href="<?= Url::to(['resources', 'subject' => $item->_subject]) ?>"
                                   data-pjax="0">
                                    <?= __('Count of Resources'); ?>
                                    <span class="pull-right badge bg-blue">
                                            <?php
                                            $teachers = ESubjectSchedule::find()
                                                ->select(['_employee'])
                                                ->where([
                                                    'active' => ESubjectSchedule::STATUS_ENABLE,
                                                    '_group' => $item->_group,
                                                    '_curriculum' => $item->_curriculum,
                                                    '_subject' => $item->_subject,
                                                    //'_training_type' => TrainingType::TRAINING_TYPE_LECTURE,
                                                ])
                                                ->column();
                                            ?>
                                            <?= count(SubjectResource::getResourceByLanguageEmployee($item->_curriculum, $item->_semester, $item->_subject, "", $item->group->_education_lang, $teachers)); ?>
                                        </span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= Url::to(['tasks', 'subject' => $item->_subject]) ?>"
                                   data-pjax="0">
                                    <?= __('Count of Tasks'); ?>
                                    <?php
                                    $marked_prac = SubjectTaskActivity::getMarkByCurriculumSubjectTrainStudent($item->_curriculum, $item->_semester, $item->_subject, false, $this->_user(),ESubjectTask::TASK_TYPE_TASK);
                                    $all_prac = count(SubjectTask::getTaskBySubjectTrainingStudent($item->_curriculum, $item->_semester, $item->_subject, true, $this->_user(), ESubjectTask::TASK_TYPE_TASK));
                                    $class = ($all_prac > 0 && $marked_prac == $all_prac) ? "bg-success" : "bg-red";
                                    ?>
                                    <span class="pull-right badge <?= $class; ?>">
                                        <?= $marked_prac; ?>
                                        /
                                        <?= $all_prac; ?>
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="<?= Url::to(['test/index', 'subject' => $item->_subject]) ?>"
                                   data-pjax="0">
                                    <?= __('Mavzulashtirilgan testlar'); ?>
                                    <?php

                                    $marked_prac = SubjectTaskActivity::getMarkByCurriculumSubjectTrainStudent($item->_curriculum, $item->_semester, $item->_subject, false, $this->_user(),ESubjectTask::TASK_TYPE_TEST);
                                    $all_prac = count(SubjectTask::getTaskBySubjectTrainingStudent($item->_curriculum, $item->_semester, $item->_subject, false, $this->_user(), ESubjectTask::TASK_TYPE_TEST));
                                    $class = ($all_prac > 0 && $marked_prac == $all_prac) ? "bg-success" : "bg-red";
                                    ?>
                                    <span class="pull-right badge <?= $class; ?>">
                                                <?= $marked_prac; ?>
                                                /
                                                <?= $all_prac; ?>
                                            </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /.widget-user -->
            </div>
            <!-- /.col -->
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
<?php Pjax::end() ?>

