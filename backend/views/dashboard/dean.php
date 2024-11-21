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
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Student Special'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-list-alt"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Special'),
                                ['student/special'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Student Student'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-mortar-board"></i>
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
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Student Group'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-tags"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Student Group'),
                                ['student/group'],
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
                </div>
                <!-- /.row -->


                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Curriculum Curriculum'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-road"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Curriculum'),
                                ['curriculum/curriculum'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <?php if ($link = $this->getResourceLink(
                        '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Student Register'),
                        ['curriculum/student-register'],
                        ['class' => 'small-box-footer', 'data-pjax' => 0]
                    )): ?>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box items">
                                <div class="inner">
                                    <h3>&nbsp;</h3>

                                    <p><?= __('Curriculum Student Register'); ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-user-plus"></i>
                                </div>
                                <?= $link ?>
                            </div>
                        </div>

                    <?php endif; ?>
                    <!-- ./col -->
                    <?php if ($link = $this->getResourceLink(
                        '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Schedule Info'),
                        ['curriculum/schedule-info'],
                        ['class' => 'small-box-footer', 'data-pjax' => 0]
                    )): ?>
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box items">
                                <div class="inner">
                                    <h3>&nbsp;</h3>

                                    <p><?= __('Curriculum Schedule Info'); ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-calendar-check-o"></i>
                                </div>
                                <?= $link ?>
                            </div>
                        </div>

                    <?php endif; ?>
                    <!-- ./col -->
                    <!-- ./col -->
                    <?php if ($link = $this->getResourceLink(
                        '<i class="fa fa-arrow-circle-right"></i> ' . __('Curriculum Exam Schedule Info'),
                        ['curriculum/exam-schedule-info'],
                        ['class' => 'small-box-footer', 'data-pjax' => 0]
                    )): ?>
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box items">
                                <div class="inner">
                                    <h3>&nbsp;</h3>

                                    <p><?= __('Curriculum Exam Schedule Info'); ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-hourglass"></i>
                                </div>
                                <?= $link ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- ./col -->
                </div>
                <!-- /.row -->


            </div>

        </div>
    </div>
</div>

