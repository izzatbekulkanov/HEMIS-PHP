<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\Semester;
use common\models\system\classifier\TrainingType;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

/**
 * @var $this \backend\components\View
 */
$semester = Semester::getByCurriculumSemester($subject->_curriculum, $subject->_semester)->name;
$training = TrainingType::findOne($training_type)->name;
$this->title = "{$subject->subject->name} ($training | {$semester} | {$group_labels})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => __('Subject Resources')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->_user();
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>


<div class="row">

    <div class="col col-md-12 col-lg-12" id="sidebar">
        <div class="box box-default ">
            <div class="box-header with-border">
                <h3 class="box-title"><?= __('List Topics') ?></h3>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($education_lang) {
                                return Html::a($data->name, '#', [
                                    'class' => 'showModalButton ',
                                    'modal-class' => 'modal-lg',
                                    'title' => $data->name,
                                    'value' => Url::to(['teacher/subject-topic-resource',
                                        'curriculum' => $data->_curriculum,
                                        'semester' => $data->_semester,
                                        'subject' => $data->_subject,
                                        'training_type' => $data->_training_type,
                                        'education_lang' => $education_lang,
                                        'code' => $data->id
                                    ]),
                                    'data-pjax' => 0
                                ]);
                            },
                        ],
                        [
                            'attribute' => '_training_type',
                            'value' => 'trainingType.name',
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'header' => __('Count of Resources'),
                            'value' => function (ECurriculumSubjectTopic $data) use ($education_lang, $user) {
                                @$value = count($data->getEmployeeResources($user->employee, $education_lang));
                                if ($value) {
                                    return Html::a(__('{count} ta resurs', ['count' => $value]), currentTo(['download' => 1, 'code' => $data->id]), ['data-pjax' => 0]);
                                }
                                return '-';
                            },
                        ],

                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'header' => __('Add Resource'),
                            'value' => function ($data) use ($education_lang) {
                                return Html::a('<i class="fa fa-plus-square"></i> &nbsp;' . __('Add'), ['teacher/subject-topic-resource-edit', 'education_lang' => $education_lang, 'code' => $data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'header' => __('Test Questions'),
                            'value' => function (ECurriculumSubjectTopic $data) use ($education_lang, $user) {
                                if ($model = ESubjectResource::getTopicTestResource($data, $user->_employee, $education_lang)) {
                                    return $model->test_question_count;
                                }
                                return 0;
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'header' => __('Add Test'),
                            'value' => function ($data) use ($education_lang) {
                                return Html::a('<i class="fa fa-plus-square"></i> &nbsp;' . __('Add'), ['teacher/subject-topic-test', 'education_lang' => $education_lang, 'code' => $data->id], ['data-pjax' => 0]);
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
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

