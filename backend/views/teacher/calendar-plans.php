<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\TrainingType;
use common\models\system\classifier\Language;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectSchedule */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <? //php if ($this->_user()->role->code !== \common\models\system\AdminRole::CODE_TEACHER && $this->_user()->role->code !== \common\models\system\AdminRole::CODE_DEPARTMENT){ ?>
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeEducationYearItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_semester')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeSemesterItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeGroupItems(),

                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_subject')->widget(Select2Default::classname(), [
                    'data' => $searchModel->getEmployeeSubjectItems(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <? //php } ?>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => '_subject',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->subject->name, ['calendar-plan',
                        'curriculum' => $data->_curriculum,
                        'semester' => $data->_semester,
                        'educationYear' => $data->_education_year,
                        'subject' => $data->_subject,
                        'group' => $data->_group,
                        'training_type' => $data->_training_type,
                        'education_lang' => $data->_education_lang
                    ], ['data-pjax' => 0]
                    );
                },
            ],

            [
                'attribute' => '_curriculum',
                'value' => 'curriculum.name',
            ],
            [
                'attribute' => '_group',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s </p>", $data->group ? $data->group->name : '', Language::findOne($data->_education_lang)->name);
                }
            ],
            [
                'attribute' => '_training_type',
                'format' => 'raw',
                 'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s </p>",
                        $data->trainingType ? $data->trainingType->name : '',
                        $data->employee->shortName
                    );
                }
            ],
            [
                'attribute' => '_semester',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s </p>", Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name, $data->educationYear->name);
                },
            ],
            [
                'attribute' => 'filename',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('<i class="fa fa-download"></i>', ['calendar-plan',
                        'curriculum' => $data->_curriculum,
                        'semester' => $data->_semester,
                        'educationYear' => $data->_education_year,
                        'subject' => $data->_subject,
                        'group' => $data->_group,
                        'training_type' => $data->_training_type,
                        'education_lang' => $data->_education_lang,
                        'download' => 1,

                    ], ['class' => 'btn btn-default btn-flat','data-pjax' => 0]
                    );
                },
            ]
            /* [
                 'attribute'=>'_group',
                 'format' => 'raw',
                 'value' => function($data){
                     return Html::a($data->group->name,['subject-topic-resource',
                         'semester' => $data->_semester,
                         'group' => $data->_group,
                         'subject' => $data->_subject,
                         'training_type' => TrainingType::TRAINING_TYPE_LECTURE], ['data-pjax' => 0]
                     );
                 },
             ],*/

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
