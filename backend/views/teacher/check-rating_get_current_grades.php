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
<div style="margin:0 -15px -25px;position: relative">
    <div class='box-body no-padding'>


                <?php if(@$grade_count == 0):?>
                    <h4>
                        <?php echo __('There is no data available to pass the mark given current grades');?>
                    </h4>
                <?php else:?>

                         <table class="table table-striped table-bordered">
                            <tr>
                                <th style="width:14px; text-align:center;"><?= __('№');?></th>
                                <th><?= __('Fullname of Student');?></th>
                                <th style="width:15%; text-align:center;"><?= __('Current Marks');?></th>
                                <th style="width:15%; text-align:center;"><?= __('Current Procent');?></th>
                                <th style="width:15%; text-align:center;"><?= __('Current Grade');?></th>
                            </tr>
                            <?php
                            $i=1;
                            foreach($rated_students as $item){?>
                                <tr>
                                    <td><?php echo $i++;?></td>
                                    <td><?php echo $item['name'];?></td>

                                    <td style="width:15%; text-align:center;">
                                        <?php
                                         $label = "";
                                         echo @$label = @$ball[$item['id']][100]!=0 ? @$ball[$item['id']][100] .' / '.@$ball[$item['id']][103] : '';
                                        ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php
                                            echo @$ball[$item['id']][101]!=0 ? @$ball[$item['id']][101] : '';
                                        ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php
                                        echo @$ball[$item['id']][102]!=0 ? @$ball[$item['id']][102] : '';
                                        ?>
                                    </td>



                                </tr>
                            <?php } ?>
                        </table>


        <?php endif;?>


    </div>
    <div class='box-footer '>
        <div class="text-right">
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>

            <?php
            if ($grade_count > 0) {
                echo $this->getResourceLink(__('Send Grades'), ['teacher/check-rating',
                    'id' => $model->id,
                    'fill_grades' => 1,
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
