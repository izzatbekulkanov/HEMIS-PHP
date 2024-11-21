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

                                <p><?= __('Structure Faculty'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-briefcase"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Structure Faculty'),
                                ['structure/faculty'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Structure Department'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-folder-open-o"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Structure Department'),
                                ['structure/department'],
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

                                <p><?= __('Structure Section'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-folder-open-o"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Structure Section'),
                                ['structure/section'],
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

                                <p><?= __('Employee Employee'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Employee Employee'),
                                ['employee/employee'],
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

                                <p><?= __('Employee Direction'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user-plus"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Employee Direction'),
                                ['employee/direction'],
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

                                <p><?= __('Employee Teacher'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user-plus"></i>
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


            </div>

        </div>
    </div>
</div>

