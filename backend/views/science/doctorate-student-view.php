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
use common\models\science\EDoctorateStudent;
use common\models\science\EDissertationDefense;
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
$this->params['breadcrumbs'][] = ['url' => ['science/doctorate-student'], 'label' => __('Doctorate Student')];
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
                                ['science/doctorate-student-edit', 'id' => $model->id],
                                ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                            ) ?>
                            <?= $this->getResourceLink(
                                '<i class="fa fa-drivers-license"></i> ' . __('Change Passport'),
                                ['science/doctorate-student-passport-edit', 'id' => $model->id],
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
                            'value' => function (EDoctorateStudent $data) {
                                return Html::img($data->getImageUrl(null, 60));
                            }
                        ],
                        'student_id_number',
                        'first_name',
                        'second_name',
                        'third_name',
                        [
                            'attribute' => 'birth_date',
                            'value' => function (EDoctorateStudent $data) {
                                return Yii::$app->formatter->asDate($data->birth_date, 'dd-MM-Y');
                            }
                        ],
                        'passport_number',
                        'passport_pin',

                        [
                            'attribute' => '_specialty_id',
                            'header' => __('Doctorate Specialty'),
                            'value' => function (EDoctorateStudent $data) {
                                return $data->specialty ? $data->specialty->name : '';
                            }
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function (EDoctorateStudent $data) {
                                return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function (EDoctorateStudent $data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            }
                        ]
                    ],
                ]) ?>
            </div>
        </div>
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>
    </div>
    <div class="col col-md-6">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus"></i> ' . __('Dissertation Defense'),
                        ['science/dissertation-defense-edit', 'id' => $model->id],
                        ['class' => 'btn btn-flat btn-default ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?php if ($defense !== null) : ?>
                    <?= DetailView::widget([
                        'model' => $defense,
                        'attributes' => [
                            'defense_place',
                            [
                                'attribute' => 'defense_date',
                                'value' => function (EDissertationDefense $data) {
                                    return Yii::$app->formatter->asDate($data->defense_date, 'dd-MM-Y');
                                }
                            ],
                            'diploma_number',
                            [
                                'attribute' => 'approved_date',
                                'value' => function (EDissertationDefense $data) {
                                    return Yii::$app->formatter->asDate($data->approved_date, 'dd-MM-Y');
                                }
                            ],

                            'register_number',
                            'scientific_council',
                            'diploma_given_by_whom',

                            [
                                'attribute' => 'created_at',
                                'value' => function (EDissertationDefense $data) {
                                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                                }
                            ],
                            [
                                'attribute' => 'updated_at',
                                'value' => function (EDissertationDefense $data) {
                                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                                }
                            ]
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>


        </div>
    </div>
</div>