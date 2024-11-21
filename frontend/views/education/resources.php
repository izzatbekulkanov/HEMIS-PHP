<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use frontend\models\curriculum\SubjectResource;
use common\models\curriculum\ESubjectSchedule;

/* @var $this \frontend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $item \frontend\models\curriculum\StudentExam */

$user = $this->_user();
$time = null;
$timestamp = pow(10, 10);
$past = true;
$date = null;
$class = "bg-blue";
$timestamp = null;
$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
$t = 0;
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>

<?php if ($dataProvider && count($dataProvider->getModels())): ?>
    <div class="row">
        <div class="col col-md-8">
            <div class="box-group" id="accordion">
                <?php foreach ($trainings as $training): ?>
                    <?php
                    $teachers = ESubjectSchedule::find()
                        ->select(['_employee'])
                        ->where([
                            'active' => ESubjectSchedule::STATUS_ENABLE,
                            '_group' => $this->_user()->meta->_group,
                            '_curriculum' => $subject->_curriculum,
                            '_subject' => $subject->_subject,
                            '_training_type' => $training->_training_type,
                        ])
                        ->column();

                    $rCount = count(SubjectResource::getResourceByLanguageEmployee($subject->_curriculum, $subject->_semester, $subject->_subject, $training->_training_type, $this->_user()->meta->group->_education_lang, $teachers));
                    ?>
                    <?php if ($rCount): ?>
                        <div class="panel box box-primary">
                            <div class="box-header with-border">
                                <h4 class="box-title">
                                    <a data-toggle="collapse" data-parent="#accordion"
                                       class="<?= $t == 1 ? '' : 'collapsed' ?>"
                                       href="#collapse_<?= $training->_training_type; ?>"
                                       aria-expanded="<?= $t == 1 ? 'true' : 'false' ?>"
                                       class="collapsed">
                                        <?= $training->trainingType->name; ?>
                                        (<?= $rCount ?>)
                                    </a>
                                </h4>

                                <div class="box-tools pull-right">
                                    <button type="button" data-parent="#accordion"
                                            href="#collapse_<?= $training->_training_type; ?>" aria-expanded="false"
                                            class="btn btn-box-tool" data-toggle="collapse"><i
                                                class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="collapse_<?= $training->_training_type; ?>"
                                 class="panel-collapse collapse <?= $t == 1 ? 'in' : '' ?>"
                                 aria-expanded="<?= $t == 1 ? 'true' : 'false' ?>">
                                <div class="box-body">
                                    <ul class="timeline">
                                        <?php foreach ($dataProvider->getModels() as $item): ?>
                                            <?php if ($item->_training_type == $training->_training_type): ?>
                                                <?php if (@$check[@$item->id]): ?>
                                                    <?php
                                                    $past = true;
                                                    $date = @$check[@$item->id];
                                                    $class = "bg-blue";
                                                    $timestamp = @$check[@$item->id];
                                                    ?>
                                                <?php else: ?>
                                                    <?php
                                                    $past = false;
                                                    $date = __('Mashg\'ulot o\'tilmagan');
                                                    $class = "bg-red";
                                                    $timestamp = time();
                                                    ?>
                                                <?php endif; ?>

                                                <li class="time-label" id="ex_<?= $timestamp; ?>">
                                                <span class="<?= $class; ?>">
                                                    <?= $date; ?>
                                                </span>
                                                </li>

                                                <li>
                                                    <!-- timeline icon -->
                                                    <i class="fa bg-blue <?= $past ? 'fa-check' : '' ?>"></i>
                                                    <div class="timeline-item">
                                                    <span class="time"><i class="fa fa-clock-o"></i>
                                                    <? //= $item->lessonPair->start_time ?>
                                                    </span>

                                                        <h3 class="timeline-header bg-blue">
                                                            <?= @$item->name; ?>
                                                        </h3>

                                                        <div class="timeline-body">
                                                            <?php
                                                            $searchModelResources = new SubjectResource();
                                                            $dataProviderResources = $searchModelResources->searchForStudentTopic($this->_user(), @$item->semester, @$item->subject, $item);
                                                            ?>
                                                            <?php if (count($dataProviderResources->getModels())): ?>
                                                                <?php foreach ($dataProviderResources->getModels() as $key => $resource):
                                                                    /**
                                                                     * @var $resource SubjectResource
                                                                     */
                                                                    ?>
                                                                    <?php if ($resource->resource_type != SubjectResource::RESOURCE_TYPE_TEST || $resource->canStartTest()): ?>
                                                                    <h4><?= $key + 1; ?>. <?= $resource->name ?>
                                                                        <small class="pull-right"><?= $resource->employee->fullName; ?></small>
                                                                    </h4>
                                                                    <p>
                                                                        <?= $resource->comment ?>
                                                                        <?php if ($resource->canStartTest()): ?>
                                                                            <?= Html::a('<i class="fa fa-check"></i> ' . __('Start Test'), ['test/start', 'id' => $resource->id, 'resource' => 1], ['class' => 'pull-right btn btn-primary']) ?>
                                                                        <?php endif; ?>
                                                                    </p>
                                                                    <?php if ($resource->path): ?>
                                                                        <p><?= __('Manba: {name}', ['name' => Html::a($resource->path, $resource->path, ['target' => '_blank'])]); ?></p>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                                    <?php if ($resource->filename): ?>
                                                                    <div class="timeline-footer">
                                                                        <?php foreach (@$resource->filename as $i => $file): ?>
                                                                            <a class="download-item"
                                                                               href="<?= Url::current(['download' => $resource->id, 'file' => $i]); ?>"><?= htmlspecialchars($file['name']); ?>
                                                                                <span class="pull-right"><?= Yii::$app->formatter->asShortSize($file['size']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                                    <br>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <div class="empty"><?= __('Resurslar mavjud emas') ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
    <?php
    //$this->registerJs("$('#ex_$timestamp span').removeClass('bg-primary').addClass('bg-success')")
    ?>
<?php else: ?>
    <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
<?php endif; ?>

