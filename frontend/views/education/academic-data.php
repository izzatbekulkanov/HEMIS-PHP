<?php

use backend\widgets\GridView;
use backend\widgets\ListViewDefault;
use backend\widgets\SimpleNextPrevPager;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\curriculum\CurriculumSubject;
use frontend\models\archive\AcademicRecord;
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
//$searchModel = new AcademicRecord();
//$dataProvider = $searchModel->searchForStudent($this->_user(), $this->getSelectedSemester());

$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
$no = false;
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
<?php foreach ($this->_user()->getSemesters() as $code => $semesterData): ?>
<?php if($code == $semester->code): ?>
<div class="box box-success <?= $code == $semester->code ? '' : 'collapsed-box' ?> ">
    <div class="box-header with-border">
        <h3 class="box-title ">
            <?= @$semesterData->name ?>
            &nbsp;
            &nbsp;
            <span class="text-muted fs-14 ">
                    (
                    <?= $semesterData ? Yii::$app->formatter->asDate($semesterData->start_date->getTimestamp(), 'php:d F, Y') : ''; ?>
                    <span class="separator">/</span>
                    <?= $semesterData ? Yii::$app->formatter->asDate($semesterData->end_date->getTimestamp(), 'php:d F, Y') : '';?>
                    )
                </span>
        </h3>
        <div class="box-tools pull-right">

        </div>
    </div>

        <?php $no = false;?>
        <div class="box-body no-padding">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th width="50px"></th>
                    <th width="50%"><?= __('Subject') ?></th>
                    <th width="10%"><?= __('Subject Type') ?></th>
                    <th width="10%"><?= __('Yuklama') ?></th>
                    <th width="10%"><?= __('Kredit') ?></th>
                    <th width="10%"><?= __('Rating / Ball') ?></th>
                    <th width="10%"><?= __('Grade') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1;?>
                <?php foreach ($records as $k => $record): ?>
                <?php if($record['semester'] == @$semester->code): ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $record['name'] ?></td>
                        <td><?= $record['subject_type'] ?></td>
                        <td><?= __('{hour} hour', ['hour' => $record['acload']]) ?></td>
                        <td><?= $record['credit'] ?></td>
                        <td><?= $record['point'] ?></td>
                        <td><?= $record['grade'] ?></td>
                    </tr>
                    <?php $no = true; ?>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?//php $i = 1;?>
                <?/*php foreach ($row['subjects'] as $i => $item): ?>
                    <tr>
                        <td><?= ++$i; ?></td>

                        <td><?= $item->subject->name ?></td>
                        <td><?= __('{hour} hour', ['hour' => $item->total_acload]) ?></td>
                        <td><?= $item->credit ?></td>
                        <td><?= $item->total_point ?></td>
                        <td><?= $item->grade ?></td>

                    </tr>
                <?php endforeach; */?>
                </tbody>
            </table>
            <br>

        </div>



    <?php if(!$no):?>
        <div class="box-body no-padding">
            <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
        </div>
    <?php endif; ?>
</div>

    <?php endif; ?>
<?php endforeach; ?>

<?php Pjax::end() ?>

