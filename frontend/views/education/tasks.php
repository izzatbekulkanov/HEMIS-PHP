<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use frontend\models\curriculum\SubjectTaskActivity;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use frontend\models\curriculum\SubjectResource;
use frontend\models\curriculum\SubjectTask;
use common\models\curriculum\ESubjectTask;

/* @var $this \frontend\components\View */
/* @var $searchModel SubjectTask */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $item \frontend\models\curriculum\SubjectTaskStudent */

$user = $this->_user();
$time = null;
$timestamp = pow(10, 10);
$past = true;


$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
$types = \common\models\system\classifier\TrainingType::getClassifierOptions();
$t = 0;
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'enablePushState' => true]) ?>

<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
<div class="row">
    <div class="col col-md-8">
        <div class="box-group" id="accordion">
            <?php foreach ($types as $type => $label): ?>
                <?php
                $dataProvider = $searchModel->searchForStudent($this->_user(), $semester, $subject, $type);
                ?>
                <?php if (count($dataProvider->getModels())): ?>
                    <div class="panel box box-primary">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                <a data-toggle="collapse" data-parent="#accordion"
                                   href="#collapse_<?= $type ?>" aria-expanded="<?= $t == 1 ? 'true' : 'false' ?>"
                                   class="<?= $t == 1 ? '' : 'collapsed' ?>">
                                    <?= $label ?> (<?= count($dataProvider->getModels()) ?>)
                                </a>
                            </h4>

                            <div class="box-tools pull-right">
                                <button type="button" data-parent="#accordion"
                                        href="#collapse_<?= $type ?>" aria-expanded="false"
                                        class="btn btn-box-tool" data-toggle="collapse"><i
                                            class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div id="collapse_<?= $type; ?>" class="panel-collapse collapse <?= $t == 1 ? 'in' : '' ?>"
                             aria-expanded="<?= $t == 1 ? 'true' : 'false' ?>">
                            <div class="box-body">
                                <?php if (count($dataProvider->getModels())): ?>
                                    <ul class="timeline">
                                        <?php foreach ($dataProvider->getModels() as $item): ?>
                                            <?php
                                            $activity = $item->allTaskStudentActivity;

                                            @$mark = "";
                                            @$marked_comment = "";
                                            foreach ($activity as $m) {
                                                if ($m->active == \common\models\curriculum\EStudentTaskActivity::STATUS_ENABLE)
                                                    @$mark = $m->mark;
                                                @$marked_comment = $m->marked_comment;
                                            }

                                            if ($item->canSubmitTask()) {
                                                $past = false;
                                                $class = "bg-green";
                                            } else {
                                                $past = true;
                                                $class = "bg-red";
                                            }
                                            ?>
                                            <li class="time-label"
                                                id="ex_<?= $item->deadline->getTimestamp() ?>">
                                            <span class="<?= $class; ?>">
                                                <?= Yii::$app->formatter->asDate($item->deadline->getTimestamp(), 'php:d.m.Y H:i'); ?>
                                            </span>
                                            </li>
                                            <?php $time = $item->deadline ?>
                                            <li>
                                                <!-- timeline icon -->
                                                <i class="fa bg-blue <?= $activity ? 'fa-check' : '' ?>"></i>
                                                <div class="timeline-item">
                                                <span class="time"><i class="fa fa-star"></i>

                                                    <span title="<?= $marked_comment; ?>"><?php
                                                        echo @$mark = (@$mark == "") ? 0 : @$mark;
                                                        ?></span>
                                                    /
                                                    <?= $item->subjectTask->max_ball; ?>
                                                </span>

                                                    <h3 class="timeline-header bg-info">
                                                        <?php
                                                        if ($item->_task_status == SubjectTask::TASK_STATUS_PASSED)
                                                            $labelStatus = '<span style="color:blue">' . $item->getTaskStatusLabel() . '</span>';
                                                        elseif ($item->_task_status == SubjectTask::TASK_STATUS_RATED)
                                                            $labelStatus = '<span style="color:green">' . $item->getTaskStatusLabel() . '</span>';
                                                        else
                                                            $labelStatus = '<span style="color:red">' . $item->getTaskStatusLabel() . '</span>';

                                                        ?>
                                                        <?= $item->trainingType->name . ' | ' . $item->subjectTask->getTaskTypeLabel() . ' | ' . $item->finalExamType->name . ' | ' . $labelStatus; ?>
                                                    </h3>

                                                    <div class="timeline-body">
                                                        <? //= __('Fan: {name}', ['name' => $item->subject->name]) ?>
                                                        <h4>
                                                            <?= $item->subjectTask->name ?>
                                                        </h4>
                                                        <p>
                                                            <?= $item->subjectTask->comment ?><br>
                                                            <?php if ($item->subjectTask->isTestTask()): ?>
                                                                <?= __('Savollar soni: {b}{count} ta{/b}{br}Ajratilgan vaqt: {b}{min} daqiqa{/b}{br}Urinishlar soni: {b}{attempted} / {attempt} marta{/b}', [
                                                                    'count' => $item->subjectTask->question_count,
                                                                    'min' => $item->subjectTask->test_duration,
                                                                    'attempt' => $item->subjectTask->attempt_count,
                                                                    'attempted' => intval($item->attempt_count),
                                                                ]) ?>
                                                            <?php endif; ?>
                                                        </p>
                                                        <?php if ($item->subjectTask->filename): ?>
                                                            <div class="timeline-footer mt30">
                                                                <p class="text-muted">
                                                                    <?= __('Topshiriqqa oid fayllar:') ?>
                                                                </p>
                                                                <?php foreach ($item->subjectTask->filename as $i => $file): ?>
                                                                    <a class="download-item"
                                                                       href="<?= Url::current(['download' => $i, 'task' => $item->id]) ?>">
                                                                        <i class="fa fa-paperclip "></i> <?= htmlspecialchars($file['name']); ?>
                                                                        <span
                                                                                class="pull-right"><?= Yii::$app->formatter->asShortSize($file['size']) ?></span>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!$past): ?>
                                                            <p class="text-right">
                                                                <?php if ($item->subjectTask->isTestTask()) : ?>
                                                                    <?php if ($studentTask = $item->taskStudentActivity) : ?>
                                                                        <a class="btn btn-default"
                                                                           href="<?= Url::to(['test/result', 'id' => $studentTask->id]); ?>">
                                                                            <?= __('Natijalar') ?>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>

                                                                <?php if ($item->subjectTask->isRegularTask()): ?>
                                                                    <a class="btn btn-default"
                                                                       href="<?= Url::to(['tasks', 'task' => $item->subjectTask->id]); ?>">
                                                                        <i class="fa fa-reply"></i> <?= __('Send Answer') ?>
                                                                    </a>
                                                                <?php elseif ($item->subjectTask->isTestTask() && $item->canStartTest()): ?>
                                                                    <a class="btn btn-primary"
                                                                       href="<?= Url::to(['test/start', 'id' => $item->id]); ?>">
                                                                        <i class="fa fa-check"></i> <?= __('Start Test') ?>
                                                                    </a>
                                                                <?php endif; ?>

                                                            </p>
                                                        <?php else:?>
                                                        <p class="text-right">
                                                            <?php if ($item->subjectTask->isTestTask()) : ?>
                                                                <?php if ($studentTask = $item->taskStudentActivity) : ?>
                                                                    <a class="btn btn-default"
                                                                       href="<?= Url::to(['test/result', 'id' => $studentTask->id]); ?>">
                                                                        <?= __('Natijalar') ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </p>
                                                        <?php endif;?>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

