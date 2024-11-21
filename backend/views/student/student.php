<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\hemis\HemisApiSyncModel;
use common\models\student\EStudent;
use common\models\system\classifier\StudentStatus;
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
            <div class="col col-sm-5">
                <div class="form-group">
                    <div class="btn-group ">
                        <?= $this->getResourceLink(
                            '<i class="fa fa-plus-circle"></i> ' . __('Create Student'),
                            ['student/student-edit'],
                            ['class' => 'btn btn-flat  btn-success ', 'data-pjax' => 0]
                        ) ?>
                        <button type="button" class="btn btn-success btn-flat dropdown-toggle"
                                data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a data-pjax="0"
                                   href="<?= Url::current(['problems' => 1]) ?>">
                                    <?= __('Students with technic problems') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col col-sm-7">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function (EStudent $data) {
                    $title = "";
                    $sign = 'fa-exclamation-triangle';
                    if ($data->meta == null) {
                        $title = Html::encode(__('Talabaning o\'quv malumotlari mavjud emas'));
                    } else if (HEMIS_INTEGRATION && ($data->_uid == null || $data->student_id_number == null)) {
                        $title = Html::encode(__('Talaba HEMIS API bilan sinxronizatsiya qilinmagan'));
                        $sign = 'fa-refresh';
                    } else if (HEMIS_INTEGRATION && ($data->_sync_status != HemisApiSyncModel::STATUS_ACTUAL || $data->_sync == false)) {
                        $title = Html::encode(__('Sinxronizatsiya statusi: {status}', ['status' => $data->getSyncStatusLabel()]));
                        $sign = 'fa-refresh';
                    } else
                        if ($data->meta->_student_status == StudentStatus::STUDENT_TYPE_APPLIED) {
                            $title = Html::encode(__('Talaba guruhga biriktirilmagan'));
                            $sign = 'fa-users';
                        } else {
                            return "<i class='sign fa fa-check text-success'></i>";
                        }

                    return "<i class='sign fa $sign text-danger' title='$title' data-toggle='tooltip'></i>";
                },
            ],

            [
                'attribute' => 'second_name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(sprintf("%s<p class='text-muted'> %s</p>", $data->getFullName(), $data->student_id_number), ['student/student-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'passport_number',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->passport_number, $data->passport_pin);
                },
            ],
            [
                'attribute' => 'year_of_enter',
                'format' => 'raw',
                 'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->year_of_enter, $data->meta && $data->meta->studentStatus ? $data->meta->studentStatus->name : '');
                },
            ],
            [
                'header' => __('Education Type'),
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->meta && $data->meta->educationType ? $data->meta->educationType->name : '', $data->meta && $data->meta->educationForm ? $data->meta->educationForm->name : '');
                },
            ],
            [
                'header' => __('Payment Form'),
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> </p>", $data->meta && $data->meta->paymentForm ? $data->meta->paymentForm->name : '');
                },
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                },
            ],
            [
                'attribute' => 'password',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('<i class="fa fa-key"></i>', ['student/student-edit', 'id' => $data->id, 'reset' => 1], ['data-pjax' => 0, 'data-confirm' => __('Are you sure to reset password')]);
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
