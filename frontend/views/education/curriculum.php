<?php

use backend\widgets\GridView;
use backend\widgets\ListViewDefault;
use backend\widgets\SimpleNextPrevPager;
use frontend\models\curriculum\StudentAttendance;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\system\StudentSchedule;
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
$searchModel = new StudentCurriculum();
$dataProvider = $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester());

$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>

<?php foreach ($dataProvider as $row): $cSemester = $row['semester']; ?>
    <div class="box box-success <?= $cSemester->code == $semester->code ? '' : 'collapsed-box' ?> ">
        <div class="box-header with-border">
            <h3 class="box-title ">
                <?= $cSemester->name ?>
                &nbsp;
                &nbsp;
                <span class="text-muted fs-14 ">
                    (
                    <?= Yii::$app->formatter->asDate($cSemester->start_date->getTimestamp(), 'php:d F, Y') ?>
                    <span class="separator">/</span>
                    <?= Yii::$app->formatter->asDate($cSemester->end_date->getTimestamp(), 'php:d F, Y') ?>
                    )
                </span>
            </h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                            class="fa fa-<?= $cSemester->code == $semester->code ? 'minus' : 'plus' ?>"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th width="50px"></th>
                    <th width="50%"><?= __('Subject') ?></th>
                    <th width="20%"><?= __('Subject Type') ?></th>
                    <th width="20%"><?= __('Yuklama') ?></th>
                    <th width="10%"><?= __('Kredit') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($row['subjects'] as $i => $item): ?>
                    <tr>
                        <td><?= $item->position + 1 ?></td>
                        <td>
                            <a class="showModalButton"
                               modal-class="modal-sm"
                               title="<?= @$item->subject->name ?>"
                               value="<?= Url::current(['subject' => $item->id]) ?>">
                                <?= @$item->subject->name ?>
                            </a>
                        </td>
                        <td><?= @$item->subjectType->name ?></td>
                        <td><?= __('{hour} hour', ['hour' => $item->total_acload]) ?></td>
                        <td><?= $item->credit ?></td>
                        <td>
                            <a class="showModalButton"
                               modal-class="modal-sm"
                               title="<?= @$item->subject->name ?>"
                               value="<?= Url::current(['subject' => $item->id]) ?>">
                                <?= __('Details') ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
<?php Pjax::end() ?>

