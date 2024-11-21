<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\performance\EStudentGpa;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\student\EGroup;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\Course;

/* @var $this \backend\components\View */
/* @var $model \common\models\performance\EStudentGpa */
/* @var $dataProvider yii\data\ActiveDataProvider */
$semester = false;
?>
<div style="margin: 0 -15px">
    <div id="data-grid" class="grid-view">
        <table class="table table-responsive table-striped table-hover ">
            <thead>
            <tr>
                <th>#</th>
                <th><?= __('Subject') ?></th>
                <th><?= __('Total Acload') ?></th>
                <th><?= __('Credit') ?></th>
                <th><?= __('Grade') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($model->data as $i => $item): ?>
                <?php if ($item['_semester'] != $semester): ?>
                    <tr>
                        <td colspan="5" class="text-center text-bold">
                            <?php
                            if ($model = \common\models\system\classifier\Semester::findOne($item['_semester'])) {
                                echo $model->name;
                            }
                            $semester = $item['_semester'];
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?= ($i + 1) ?>
                    </td>
                    <td>
                        <?php
                        if ($model = \common\models\curriculum\ESubject::findOne($item['_subject'])) {
                            echo $model->name;
                        }
                        ?>
                    </td>
                    <td><?= $item['total_acload'] ?></td>
                    <td><?= $item['credit'] ?></td>
                    <td><?= $item['grade'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

