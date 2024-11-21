<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\system\Admin;
use common\models\system\classifier\Gender;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;


/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployee */

$this->title = $model->getFullName();
$this->params['breadcrumbs'][] = ['url' => ['employee/employee'], 'label' => __('Employee Employee')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>
<div class="row">
    <div class="col col-md-6">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-edit"></i> ' . __('Change Information'),
                                ['employee/employee-edit', 'id' => $model->id],
                                ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                            ) ?>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-user"></i> ' . __($model->admin ? 'Edit Account' : 'Create Account'),
                                ['employee/account', 'id' => $model->id],
                                ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                            ) ?>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-drivers-license"></i> ' . __('Change Passport'),
                                ['employee/employee-passport-edit', 'id' => $model->id],
                                ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => 'image',
                            'format' => 'raw',
                            'value' => function (EEmployee $data) {
                                return Html::img($data->getImageUrl(null, 60));
                            }
                        ],
                        'employee_id_number',
                        'first_name',
                        'second_name',
                        'third_name',
                        [
                            'attribute' => 'birth_date',
                            'value' => function (EEmployee $data) {
                                return Yii::$app->formatter->asDate($data->birth_date, 'dd-MM-Y');
                            }
                        ],
                        'passport_number',
                        'passport_pin',

                        'specialty',
                        [
                            'attribute' => '_academic_rank',
                            'value' => function (EEmployee $data) {
                                return $data->academicRank ? $data->academicRank->name : '';
                            }
                        ],
                        [
                            'attribute' => '_academic_degree',
                            'value' => function (EEmployee $data) {
                                return $data->academicDegree ? $data->academicDegree->name : '';
                            }
                        ],
                        [
                            'attribute' => '_admin',
                            'label' => __('Role'),
                            'format' => 'raw',
                            'value' => function (\common\models\employee\EEmployee $data) {
                                return $data->getRolesLabel();
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function (EEmployee $data) {
                                return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function (EEmployee $data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            }
                        ]
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col col-md-6">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus"></i> ' . __('Direction'),
                        ['employee/direction', 'employee' => $model->id, 'edit' => 1],
                        ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                    ) ?>
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus"></i> ' . __('Teacher'),
                        ['employee/teacher', 'employee' => $model->id, 'edit' => 1],
                        ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <?= GridView::widget([
                'id' => 'data-grid',
                'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                'dataProvider' => (new EEmployeeMeta())->searchForEmployee($model),
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_department',
                        'value' => 'department.name',
                    ],
                    [
                        'attribute' => '_employment_staff',
                        'value' => 'employmentStaff.name',
                    ],
                    [
                        'attribute' => '_position',
                        'value' => 'staffPosition.name',
                    ],
                    [
                        'attribute' => '_employee_status',
                        'value' => 'employeeStatus.name',
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-md-5">
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>

    </div>
</div>


