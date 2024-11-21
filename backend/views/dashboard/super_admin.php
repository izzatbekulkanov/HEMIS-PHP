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

            <div class="box-body">

                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-blue">
                            <div class="inner">

                                <h3><?= ESubjectSchedule::getAttendanceJournalCount()?></h3>

                                <p><?= __('Structure Faculty'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar-check-o"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Structure Faculty'),
                                ['structure/faculty'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-blue">
                            <div class="inner">

                                <h3><?= ESubjectSchedule::getAttendanceLessonCount();?></h3>

                                <p><?= __('Structure Department'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-table"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Structure Department'),
                                ['structure/department'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-blue">
                            <div class="inner">

                                <h3><?= ESubjectSchedule::getAttendanceLessonCount();?></h3>

                                <p><?= __('Employee Direction'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Employee Direction'),
                                ['employee/direction'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-blue">
                            <div class="inner">

                                <h3><?= ESubjectSchedule::getAttendanceLessonCount();?></h3>

                                <p><?= __('Employee Teacher'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-list"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Employee Teacher'),
                                ['employee/teacher'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <!-- ./col -->
                </div>
                <!-- /.row -->

                <!-- =========================================================== -->


                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getMidtermExamCount();?></h3>

                                <p><?= __('Student Special'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass-2"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Special'),
                                ['student/special'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getFinalExamCount();?></h3>

                                <p><?= __('Student Group'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass-3"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Group'),
                                ['student/group'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getOverallExamCount();?></h3>

                                <p><?= __('Student Student'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Student'),
                                ['student/student'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getOverallExamCount();?></h3>

                                <p><?= __('Contingent'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Student'),
                                ['student/student'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <!-- /.row -->




                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getMidtermExamCount();?></h3>

                                <p><?= __('Curriculum'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass-2"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum'),
                                ['curriculum/curriculum'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getFinalExamCount();?></h3>

                                <p><?= __('Curriculum Subject'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass-3"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Subject'),
                                ['curriculum/subject'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getOverallExamCount();?></h3>

                                <p><?= __('Curriculum Schedule Info'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Schedule Info'),
                                ['curriculum/schedule-info'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?= ESubjectExamSchedule::getOverallExamCount();?></h3>

                                <p><?= __('Curriculum Exam Schedule Info'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-hourglass"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Exam Schedule Info'),
                                ['curriculum/exam-schedule-info'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <!-- /.row -->


            </div>

        </div>
    </div>
</div>

