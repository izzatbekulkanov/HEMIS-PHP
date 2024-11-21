<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use frontend\models\curriculum\StudentCurriculum;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;

/* @var $this \frontend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$user = $this->_user();


$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'attendance-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
<?php if (count($dataProvider->getModels()) || 1): ?>

    <div class="box box-default ">
        <div class="box-header bg-gray">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-md-3">

                </div>
                <div class="col col-md-3">

                </div>

                <div class="col col-md-3">
                    <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                        'data' => StudentCurriculum::getSemesterSubjects($user, $this->getSelectedSemester()),
                        'allowClear' => true,
                        'hideSearch' => false,
                    ])->label(false); ?>
                </div>
                <div class="col col-md-3">
                    <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Subject / Employee')])->label(false); ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?php
        $hours = 0;
        ?>
        <?= GridView::widget([
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'emptyText' => __('Ma\'lumotlar mavjud emas'),
            'showFooter' => true,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => '_semester',
                    'value' => 'semester.name',
                ],
                [
                    'attribute' => 'lesson_date',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return $data->getFormattedDate();
                    },
                ],

                [
                    'attribute' => '_subject',
                    'value' => 'subject.name',
                ],
                [
                    'attribute' => '_training_type',
                    'value' => 'trainingType.name',
                ],
                'absent_on:boolean',
                [
                    'attribute' => 'absent_off',
                    'header' => __('Hours'),
                    'format' => 'raw',
                    'value' => function (\frontend\models\curriculum\StudentAttendance $data) use (&$hours) {
                        $hours += $data->absent_off ? 2 : 2;
                        return $data->absent_off ? 2 : 2;
                    },
                ],


                [
                    'attribute' => '_employee',
                    'value' => 'employee.fullName',
                ],
            ],
        ]); ?>
    </div>
<?php else: ?>
    <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
<?php endif ?>
<?php Pjax::end() ?>
