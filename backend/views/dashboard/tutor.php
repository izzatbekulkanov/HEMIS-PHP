<?php
/* @var $this yii\web\View */

$this->params['breadcrumbs'][] = $this->title;

$links = [
    [

    ]
];

?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Student Student Contingent'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Student Contingent'),
                                ['student/student-contingent'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Curriculum Schedule Info View'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar-check-o"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Schedule Info View'),
                                ['curriculum/schedule-info-view'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>
                                <p><?= __('Attendance Report'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-list-alt"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Attendance Report'),
                                ['attendance/report'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>
                                <p><?= __('Attendance Lessons'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-mortar-board"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Attendance Lessons'),
                                ['attendance/lessons'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <!-- ./col -->
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Performance Performance'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-tags"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Performance Performance'),
                                ['performance/performance'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Performance Summary'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-road"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Performance Summary'),
                                ['performance/summary'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

