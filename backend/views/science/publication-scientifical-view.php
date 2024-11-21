<?php

use backend\widgets\DatePickerDefault;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\components\Config;
use common\models\employee\EEmployee;
use common\models\science\EPublicationScientific;
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
                            'attribute' => '_scientific_publication_type',
                            'value' => function (EPublicationScientific $data) {
                                return $data->scientificPublicationType ? $data->scientificPublicationType->name : '';
                            }
                        ],
                        'keywords',
                        'author_counts',
                        'authors',
                        'source_name',
                        'doi',
                        'issue_year',
                        [
                            'attribute' => '_education_year',
                            'value' => function (EPublicationScientific $data) {
                                return $data->educationYear ? $data->educationYear->name : '';
                            }
                        ],
                        'parameter',
                        [
                            'attribute' => '_publication_database',
                            'value' => function (EPublicationScientific $data) {
                                return $data->publicationDatabase ? $data->publicationDatabase->name : '';
                            }
                        ],
                        [
                            'attribute' => 'filename',
                            'format' => 'raw',
                            'value' => function (EPublicationScientific $data) {
                                if ($data->filename) {
                                    return Html::a($data->filename['name'], $data->filename['base_url'] . '/' . $data->filename['path'], ['target'=>'_blank', 'data-pjax'=>0]);
                                }
                            },
                        ],
                        /*[
                            'attribute' => 'is_checked',
                            'header' => __('Is Checked By Author'),
                            'format' => 'raw',
                            'value' => function (EPublicationScientific $data) {
                                return '<span class="text text-red">'.__('AUTHOR IS NOT APPROVED').'</span>';
                            },
                        ],*/
                        [
                            'attribute' => 'created_at',
                            'value' => function (EPublicationScientific $data) {
                                return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function (EPublicationScientific $data) {
                                return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                            }
                        ]
                    ],
                ]) ?>
            </div>
        </div>
    </div>

</div>


