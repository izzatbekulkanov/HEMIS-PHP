<?php

use backend\widgets\GridView;
use common\models\curriculum\EExam;
use common\models\system\AdminRole;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div style="margin: 15px">
    <div class='box-body no-padding'>
        <div>
           <h4>
            <?php if($count_task == 0):?>
                <?php echo __('There is no data available to pass the mark given tasks');?>
            <?php else:?>
                <?php echo __('Number of students who passed the assignment {mark}. Would you like to transfer them for evaluation?', ['mark'=>$count_task]);?>
            <?php endif;?>
           </h4>
        </div>
    </div>
    <div class='box-footer '>
        <div class="text-right">
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>

            <?php
            if ($count_task > 0) {
                echo $this->getResourceLink(__('Send Marks'), ['teacher/check-rating',
                    'id' => $model->id,
                    'fill_task' => 1,
                ],
                    [
                        'class' => 'btn btn-success btn-flat',
                        'data-pjax' => 0
                    ]
                );
            }
            ?>
        </div>
    </div>
</div>
