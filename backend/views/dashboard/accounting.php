<?php
use common\components\Config;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\finance\EStudentContract;
/* @var $this yii\web\View */
/* @var $model common\models\Admin */


$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-info">
            <div class="box-header bg-gray">
                <div class="row">

                    <div class="col col-md-6">
                        <div class="form-group">
                            <?= __('Contract Price Calculation');?>:
                            <span class="badge badge-success"><?= EStudentContract::getContractCalculationTypeOptions()[Config::get(Config::CONFIG_COMMON_CONTRACT_CALCULATION)]; ?></span>
                        </div>
                    </div>
                    <div class="col col-md-6">

                    </div>



                </div>
            </div>
            <div class="box-body">
                <div class="row">


                    <div class="col-lg-4 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Finance Student Contract'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-id-card"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Finance Student Contract'),
                                ['finance/student-contract'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>
                    <div class="col-lg-4 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Finance Payment Monitoring'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Finance Payment Monitoring'),
                                ['finance/payment-monitoring'],
                                ['class' => 'small-box-footer', 'data-pjax' => 0]
                            ) ?>


                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-4 col-xs-6">
                        <!-- small box -->
                        <div class="small-box items">
                            <div class="inner">
                                <h3>&nbsp;</h3>

                                <p><?= __('Finance Contract Invoice'); ?></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-suitcase"></i>
                            </div>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-arrow-circle-right"></i> ' . __('Finance Contract Invoice'),
                                ['finance/contract-invoice'],
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

