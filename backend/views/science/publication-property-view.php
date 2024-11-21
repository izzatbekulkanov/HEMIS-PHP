<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\employee\EEmployee;
use common\models\science\EPublicationProperty;
use common\models\system\Admin;
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


$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">
                        <div class="form-group">

                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'template' => '<tr><th>{label}</th><td style="width:75%;">{value}</td></tr>',
                    'model' => $model,
                    'attributes' => [
                        'name',
                        [
                            'attribute' => '_patient_type',
                            'value' => function (EPublicationProperty $data) {
                                return $data->patientType ? $data->patientType->name : '';
                            }
                        ],
                        'numbers',
                        'author_counts',
                        'authors',
                        [
                            'attribute' => 'property_date',
                            'value' => function (EPublicationProperty $data) {
                                return Yii::$app->formatter->asDate($data->property_date, 'dd-MM-Y');
                            }
                        ],

                        [
                            'attribute' => '_education_year',
                            'value' => function (EPublicationProperty $data) {
                                return $data->educationYear ? $data->educationYear->name : '';
                            }
                        ],
                        'parameter',

                        [
                            'attribute' => 'filename',
                            'format' => 'raw',
                            'value' => function (EPublicationProperty $data) {
                                if ($data->filename) {
                                    return Html::a($data->filename['name'], $data->filename['base_url'] . '/' . $data->filename['path'], ['target'=>'_blank', 'data-pjax'=>0]);
                                }
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function (EPublicationProperty $data) {
                                return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function (EPublicationProperty $data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            }
                        ]
                    ],
                ]) ?>
            </div>
        </div>
    </div>

</div>


