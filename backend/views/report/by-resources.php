<?php

use common\models\curriculum\ESubjectSchedule;
use common\models\structure\EDepartment;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\ArrayHelper;use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\Semester;
use common\models\system\classifier\TrainingType;
use common\models\curriculum\ESubject;
use common\models\employee\EEmployeeMeta;
use common\models\system\SystemLog;
/* @var $this \backend\components\View */
/* @var $dataProviderReport yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_faculty')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'disabled' => $faculty,
                        'placeholder' => __('-Choose Faculty-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-4">
                    <?php $faculty = !empty($faculty) ? $faculty : $searchModel->_faculty;?>
                    <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                        'data' => ArrayHelper::map(EDepartment::getDepartmentList($faculty), 'id', 'name'),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'options'=>['placeholder' => __('Choose Department')],
                    ])->label(false); ?>

            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                    'data' => EducationYear::getEducationYears(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options'=>['placeholder' => __('Choose Education Year')],
                ])->label(false); ?>

            </div>

            <div class="col col-md-2">
                <?= $form->field($searchModel, '_semester')->widget(\backend\widgets\Select2Default::classname(), [
                    'data' => Semester::getClassifierOptions(),
                    'hideSearch' => false
                ])->label(false) ?>
            </div>
            <? /*<div class="col col-md-4">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Employee ID')])->label(false) ?>
            </div> */?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        //'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        /*'rowOptions' => function ($data, $key, $index, $grid) {
            if($log = $data->employee->systemLog){
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
                'attribute' => '_employee',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", @$data->employee->fullName, @$data->department->name);
                }
            ],
            [
                'attribute' => 'subject_name',
                'header' => __('Subject'),
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s <p class='text-muted'> %s</p>",
                        $data->_subject ? ESubject::findOne($data->_subject)->name : '',
                        $data->_curriculum ? \common\models\curriculum\ECurriculum::findOne($data->_curriculum)->getShortName() : ''
                    );
                },
            ],
            [
                'attribute' => '_training_type',
                'header' => __('Training Type'),
                'format' => 'raw',
                'value' => function ($data) {
                    $group_labels = "";
                    $groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($data->_curriculum, $data->_semester, $data->_subject, $data->_training_type, $data->_education_lang, $data->_employee);
                    foreach ($groups as $group) {
                        $group_labels .= $group->group->name . ', ';
                    }
                    $group_labels = substr($group_labels, 0, -2);

                    return sprintf("%s <p class='text-muted'> %s</p>",
                        $data->_training_type ? TrainingType::findOne($data->_training_type)->name : '',
                        $group_labels
                    );
                },
            ],
            [
                'attribute' => 'topics_count',
                'header' => __('Topics'),
                'value' =>function (EEmployeeMeta $data) use ($searchModel) {
                    return $data->getSubjectTopics($searchModel->_semester, $data->_curriculum, $data->_subject, $data->_training_type)->count();
                },
            ],

            [
                'attribute' => 'resources_count',
                'header' => __('Resources count'),
                'value' =>function (EEmployeeMeta $data) use ($searchModel) {
                    return $data->getSubjectResources($searchModel->_education_year, $searchModel->_semester, $data->_curriculum, $data->_subject, $data->_training_type, $data->_education_lang)->count();
                },
            ],
            [
                'attribute' => 'tasks_count',
                'header' => __('Tasks count'),
                'value' =>function (EEmployeeMeta $data) use ($searchModel) {
                    return $data->getSubjectTasks($searchModel->_education_year, $searchModel->_semester, $data->_curriculum, $data->_subject, $data->_training_type, $data->_education_lang)->count();
                },
            ],

        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
