<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;

/* @var $this \frontend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $item \frontend\models\curriculum\StudentExam */

$user = $this->_user();
$time = null;
$timestamp = pow(10, 10);
$past = true;


$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
<?php if (count($dataProvider->getModels())): ?>
    <div class="row">
        <div class="col col-md-8">
            <ul class="timeline">
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <?php if ($time != $item->exam_date): ?>
                        <?php
                        if (time() < $item->exam_date->getTimestamp()) {
                            if ($timestamp > $item->exam_date->getTimestamp()) {
                                $timestamp = $item->exam_date->getTimestamp();
                            }
                            $past = false;
                        } else {
                            $past = true;
                        }
                        ?>
                        <li class="time-label" id="ex_<?= $item->exam_date->getTimestamp() ?>">
                        <span class="bg-primary">
                            <?= $item->getFormattedDate() ?>
                        </span>
                        </li>
                        <?php $time = $item->exam_date ?>
                    <?php endif; ?>
                    <li>
                        <!-- timeline icon -->
                        <i class="fa bg-info <?= $past ? 'fa-check' : '' ?>"></i>
                        <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i>
                        <?= $item->lessonPair->start_time ?>
                        </span>

                            <h3 class="timeline-header">
                                <?= $item->examType->name ?>
                            </h3>

                            <div class="timeline-body">
                                <?= __('Fan: {name}', ['name' => $item->subject->name]) ?><br>
                                <?= __('O\'qituvchi: {name}', ['name' => $item->employee->getFullName()]) ?><br>
                                <?= __('Auditoriya: {name}', ['name' => $item->auditorium->name]) ?><br>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
    $this->registerJs("$('#ex_$timestamp span').removeClass('bg-primary').addClass('bg-success')")
    ?>
<?php else: ?>
    <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
<?php endif; ?>

