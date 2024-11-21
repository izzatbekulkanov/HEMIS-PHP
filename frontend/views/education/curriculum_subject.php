<?php

use backend\widgets\GridView;
use backend\widgets\ListViewDefault;
use backend\widgets\SimpleNextPrevPager;
use common\models\curriculum\ECurriculumSubject;
use frontend\models\curriculum\StudentAttendance;
use frontend\models\curriculum\StudentCurriculum;
use frontend\models\system\StudentSchedule;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii2fullcalendar\yii2fullcalendar;

/* @var $model ECurriculumSubject */

$items = $model->subjectDetails;
?>
<div style="margin: 0 -15px -15px">
    <table class="table table-striped table-hover" style="margin-bottom: 0">
        <thead>
        <tr>
            <th width="70%"><?= __('Training Type') ?></th>
            <th width="30%"><?= __('Yuklama') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($model->subjectDetails as $detail): ?>
            <tr>
                <td><?= $detail->trainingType->name ?></td>
                <td><?= __('{hour} hour', ['hour' => $detail->academic_load]) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="text-bold">
            <td><?= __('Total') ?></td>
            <td><?= __('{hour} hour', ['hour' => $model->total_acload]) ?></td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <th width="70%"><?= __('Exam Type') ?></th>
            <th width="30%"><?= __('Max Ball') ?></th>
        </tr>

        <?php foreach ($model->subjectExamTypes as $detail): ?>
            <tr>
                <td><?= $detail->examType->name ?></td>
                <td><?= __('{max_ball} ball', ['max_ball' => $detail->max_ball]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

