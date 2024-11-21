<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\TrainingType;
use common\models\system\classifier\Language;
use common\models\curriculum\Semester;
/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectSchedule */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
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

    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
		'dataProvider' => $dataProvider,
        'columns' => [
			['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'_subject',
                'format' => 'raw',
                'value' => function($data){
                    return Html::a($data->subject->name,['subject-task-list',
                        'curriculum' => $data->_curriculum,
                        'semester' => $data->_semester,
                        'subject' => $data->_subject,
                        'training_type' => $data->_training_type,
                        'education_lang' => $data->_education_lang
                    ], ['data-pjax' => 0]
                    );
                },
            ],
            [
                'attribute'=>'_curriculum',
                'value' => 'curriculum.name',
            ],
            [
                'attribute'=>'_group',
                'format' => 'raw',
                'value' => function($data){
                    $groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($data->_curriculum, $data->_semester, $data->_subject, $data->_training_type, $data->_education_lang, Yii::$app->user->identity->_employee);
                    $res = "";
                    foreach ($groups as $group){
                        $res .= '<span class="badge bg-light-blue"> '.$group->group->name. '</span>';
                    }
                    return $res;
                }
            ],
            [
                'attribute'=>'_training_type',
                'value' => 'trainingType.name',
            ],
            [
                'header' => __('Tasks'),
                'value' => function (ESubjectSchedule $data) {
                    return $data->getSubjectTasks()->count();
                },
            ],
            [
                'attribute' => '_semester',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s </p>", Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name, $data->educationYear->name);
                },
            ],
            [
                'attribute'=>'_education_lang',
                'header' => __('Education Lang'),
                'value' => function($data){
                    return Language::findOne($data->_education_lang)->name;
                }
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
