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
                    <div class="col-lg-4 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Archive Employment'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-briefcase"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Archive Employment'),
                                ['archive/employment'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>


                    <div class="col-lg-4 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Statistical By Employment'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-bar-chart"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Statistical By Employment'),
                                ['statistical/by-employment'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>


                    <div class="col-lg-4 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Admission Quota'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Admission Quota'),
                                ['student/admission-quota'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>



                </div>
                <!-- /.row -->






            </div>

        </div>
    </div>
</div>

