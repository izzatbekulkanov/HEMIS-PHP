<?php
use common\components\Config;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectExamSchedule;
/* @var $this yii\web\View */
/* @var $model common\models\Admin */


$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><?= __('Trainings') ?></h3>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">

                                <h3><?= @ESubjectSchedule::getAttendanceJournalCount()?></h3>

                                <p><?= __('Attendance Journal'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar-check-o"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Attendance Journal'),
                                ['teacher/attendance-journal'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">

                                <h3><?= @ESubjectSchedule::getAttendanceLessonCount();?></h3>

                                <p><?= __('My Timetable'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-table"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('My Timetable'),
                                ['teacher/time-table'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">

                                <h3><?= @ESubjectSchedule::getAttendanceLessonCount();?></h3>

                                <p><?= __('Training List'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Training List'),
                                ['teacher/training-list'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                </div>
                <!-- /.row -->

                <!-- =========================================================== -->


            </div>

        </div>
    </div>
</div>



<div class="row">
    <div class="col col-md-12">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><?= __('Rating Grades') ?></h3>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getMidtermExamCount();?></h3>

                                <p><?= __('Midterm Examtable'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass-2"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Midterm Examtable'),
                                ['teacher/midterm-exam-table'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getFinalExamCount();?></h3>

                                <p><?= __('Final Examtable'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass-3"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Final Examtable'),
                                ['teacher/final-exam-table'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getOverallExamCount();?></h3>

                                <p><?= __('Other Examtable'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Other Examtable'),
                                ['teacher/other-exam-table'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <!-- /.row -->

                <!-- =========================================================== -->


            </div>

        </div>
    </div>
</div>