<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\system\classifier\EducationType;
use common\models\system\AdminRole;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\Course;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationForm;
use common\models\structure\EDepartment;

//use common\models\curriculum\ECurriculum;
/* @var $this \backend\components\View */
/* @var $dataProviderReport yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">

    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row" id="data-grid-filters">

            <div class="col col-md-4">
                <div class="form-group">

                </div>
            </div>
            <div class="col col-md-2">

            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by student fullName / Pasport / PIN / Code')])->label(false) ?>
            </div>
        </div>
        <div class="row" id="data-grid-filters">


            <div class="col col-md-4">
                <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'disabled' => $faculty,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_department'
                    ],
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                    'data' => EducationType::getHighers(),
                    'hideSearch' => false,

                    'options' => [
                        'id' => '_education_type'
                    ],
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                    'data' => EducationForm::getClassifierOptions(),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_education_form'
                    ],
                ])->label(false); ?>
            </div>
            <?php
            $groups = [];
            if ($searchModel->_department && $searchModel->_education_type && $searchModel->_education_form) {
                $groups = EGroup::getOptionsByFacultyEduFormEduType($searchModel->_department, $searchModel->_education_type, $searchModel->_education_form);
            }
            ?>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => $groups,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_group'
                    ],
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_level')->widget(Select2Default::classname(), [
                    'data' => Course::getClassifierOptions(),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_level'
                    ],
                ])->label(false); ?>
            </div>




        </div>
        <?php ActiveForm::end(); ?>
    </div>


    <?= GridView::widget([
        'id' => 'data-grid',
        // 'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        /*'rowOptions' => function ($data, $key, $index, $grid) {
            if($log = $data->student->systemLog){
                $week = date('d.m.Y',strtotime("-7 days"));
                if(date('Y-m-d', strtotime($week)) >= date('Y-m-d', strtotime(Yii::$app->formatter->asDate($log->created_at)))){
                    return ['class' => 'danger'];
                }
                else
                    return ['class' => ''];
            }
            else
                return ['class' => 'danger'];


        },*/
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->student ? $data->student->fullName : '', $data->group ? $data->group->name : '');
                }
            ],
            [
                'attribute' => '_department',
                'value' => function ($data) {
                    return sprintf("%s", $data->department ? $data->department->name : '');
                }

            ],
            [
                'attribute' => '_specialty_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->specialty ? $data->specialty->code : '', $data->paymentForm ? $data->paymentForm->name : '');
                },
            ],

            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", $data->educationType ? $data->educationType->name : '', $data->educationForm ? $data->educationForm->name : '');
                },

            ],

            [
                'attribute' => 'Ip',
                'header' => __('Ip'),
                'value' => function ($data) {
                   return $data->ip ? $data->ip : '';
                },
            ],
            [
                'attribute' => 'message',
                'header' => __('Message'),
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->message ? \common\models\student\EStudentMeta::shortTitle($data->message) : '';
                },
            ],
            [
                'attribute' => 'created_at',
                'header' => __('Last Entry Date'),
                'format'=>'raw',
                'value' => function ($data) {
                    if($data->created_at){
                        $week = date('d.m.Y',strtotime("-7 days"));
                        if(date('Y-m-d', strtotime($week)) >= date('Y-m-d', strtotime(Yii::$app->formatter->asDate($data->created_at)))){
                            return '<span style="color:red">'.date('d.m.Y H:i:s', strtotime(Yii::$app->formatter->asDatetime($data->created_at))).'</span>';
                        }
                        else
                            return date('d.m.Y H:i:s', strtotime(Yii::$app->formatter->asDatetime($data->created_at)));
                    }
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
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['student/student'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>
