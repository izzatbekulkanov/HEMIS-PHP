<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\science\EProject;
use common\models\science\EProjectMeta;
use common\models\science\EProjectExecutor;
use common\models\system\classifier\ProjectExecutorType;
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

$this->title =  $model->getShortTitle();
$this->params['breadcrumbs'][] = ['url' => ['science/project'], 'label' => __('Science Project')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>
<div class="row">
    <div class="col col-md-5">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-edit"></i> ' . __('Change Information'),
                                ['science/project-edit', 'id' => $model->id],
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
                        'name',
                        'project_number',
                        [
                            'attribute' => '_department',
                            'value' => function (EProject $data) {
                                return $data->department ? $data->department->name : '';
                            }
                        ],
                        [
                            'attribute' => '_project_type',
                            'value' => function (EProject $data) {
                                return $data->projectType ? $data->projectType->name : '';
                            }
                        ],
                        [
                            'attribute' => '_locality',
                            'value' => function (EProject $data) {
                                return $data->locality ? $data->locality->name : '';
                            }
                        ],
                        [
                            'attribute' => '_project_currency',
                            'value' => function (EProject $data) {
                                return $data->projectCurrency ? $data->projectCurrency->name : '';
                            }
                        ],
                        'contract_number',
                        [
                            'attribute' => 'contract_date',
                            'value' => function (EProject $data) {
                                return Yii::$app->formatter->asDate($data->contract_date, 'dd-MM-Y');
                            }
                        ],
                        [
                            'attribute' => 'start_date',
                            'value' => function (EProject $data) {
                                return Yii::$app->formatter->asDate($data->start_date, 'dd-MM-Y');
                            }
                        ],
                        [
                            'attribute' => 'end_date',
                            'value' => function (EProject $data) {
                                return Yii::$app->formatter->asDate($data->end_date, 'dd-MM-Y');
                            }
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function (EProject $data) {
                                return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function (EProject $data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            }
                        ]
                    ],
                ]) ?>
            </div>
        </div>
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
    <div class="col col-md-7">
        <div class="box box-default ">

            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-8">
                        <div class="form-group">
                            <h3 class="box-title"><?= __('Project Finance Information'); ?></h3>
                        </div>
                    </div>
                    <div class="col col-md-4">
                        <?= $this->getResourceLink(
                            '<i class="fa fa-plus"></i> ' . __('Add Project Meta'),
                            ['science/project-meta', 'project' => $model->id, 'edit' => 1],
                            ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                        ) ?>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                    'dataProvider' => (new EProjectMeta())->searchForProject($model),
                    'toggleAttribute' => 'active',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'fiscal_year',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->fiscal_year, ['science/project-meta', 'project' => $data->_project, 'id' => $data->id,], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute' => 'budget',
                        ],
                        [
                            'attribute' => 'quantity_members',
                        ],
                    ],
                ]); ?>
            </div>

            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-8">
                        <div class="form-group">
                            <h3 class="box-title"><?= __('Project Members Information'); ?></h3>
                        </div>
                    </div>
                    <div class="col col-md-4">
                        <?= $this->getResourceLink(
                            '<i class="fa fa-plus"></i> ' . __('Add Project Member'),
                            ['science/project-member', 'project' => $model->id, 'edit' => 1],
                            ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                        ) ?>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                    'dataProvider' => (new EProjectExecutor())->searchForProject($model),
                    'toggleAttribute' => 'active',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => '_project_executor_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->projectExecutorType->name, ['science/project-member', 'project' => $data->_project, 'id' => $data->id,], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute' => '_executor_type',
                            'value' => function ($data) {
                                return $data->getExecutorStatusOptions()[$data->_executor_type];
                            },
                        ],
                        [
                            'attribute' => '_id_number',
                            'value' => function ($data) {
                                if ($data->_project_executor_type == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_TEACHER) {
                                    return $data->employee->fullName;
                                } else if ($data->_project_executor_type == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_STUDENT) {
                                    return $data->student->fullName;
                                } else if ($data->_project_executor_type == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_RESEARCHER) {
                                    return $data->doctorateStudent->fullName;
                                } else {
                                    return $data->outsider;
                                }
                            },
                        ],
                        [
                            'attribute' => 'start_date',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDate($data->start_date, 'dd-MM-Y');
                            },
                        ],
                        [
                            'attribute' => 'end_date',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDate($data->end_date, 'dd-MM-Y');
                            },
                        ],
                    ],
                ]); ?>
            </div>


        </div>
    </div>
</div>


