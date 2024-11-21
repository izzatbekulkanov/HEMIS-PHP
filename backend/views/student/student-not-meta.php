<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use common\models\student\ESpecialty;
use common\models\system\classifier\PaymentForm;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Student');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">

            </div>
            <div class="col col-md-3">
                <? /*= $form->field($searchModel, 'year_of_enter')->widget(Select2Default::classname(), [
                    'data' => \common\models\student\EStudent::getYearOfEnterOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); */ ?>
            </div>
            <div class="col col-md-7">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false) ?>
            </div>

            <div class="col col-md-2"></div>


            <? /*<div class="col col-md-2">
                <?= $form->field($searchModel, '_payment_form')->widget(Select2Default::classname(), [
                    'data' => PaymentForm::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            */ ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'student_id_number',
                'value' => 'student.student_id_number'
            ],
            /*[
                'attribute' => 'id',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->fullName ? $data->fullName : '-', ['student/student-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],*/
            [
                'attribute' => 'id',
                'header' => __('Student'),
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->fullName ? $data->fullName : '-';
                   // return Html::a($data->fullName ? $data->fullName : '-', ['student/student-delete', 'id' => $data->id, 'trash' => 1], ['data-pjax' => 0, 'data-confirm' => __('Are you sure to delete student')]);
                },
            ],
            [
                'attribute' => 'passport_number',
                'value' => 'passport_number'
            ],
            [
                'attribute' => 'year_of_enter',
                'value' => 'year_of_enter'
            ],

            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],
            [
                'attribute' => 'Delete',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('<i class="fa fa-trash"></i>', ['student/student-delete', 'id' => $data->id, 'trash' => 1], ['data-pjax' => 0, 'data-confirm' => __('Are you sure to delete student')]);
                },
            ],
        ],
    ]); ?>
</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
