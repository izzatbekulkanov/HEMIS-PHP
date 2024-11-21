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

                                <p><?= __('Science Specialty'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-list-alt"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Science Specialty'),
                                ['science/specialty'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Science Doctorate Student'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-mortar-board"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Science Doctorate Student'),
                                ['science/doctorate-student'],
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

