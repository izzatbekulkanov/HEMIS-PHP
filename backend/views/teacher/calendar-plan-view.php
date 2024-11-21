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
$this->title = "{$subject->subject->name} ($training | {$semester})";

$this->params['breadcrumbs'][] = ['url' => ['teacher/calendar-plan'], 'label' => __('Calendar Plan')];
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
                    'dataProvider' => $dataProviderTopic,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        [
                            'attribute' => 'name',
                            'header'=>__('Subject Topic'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->name;
                            },
                        ],
                        [
                            'attribute' => '_training_type',
                            'value' => 'trainingType.name',
                        ],
                        [
                            'attribute' => '_training_type',
                            'header'=>__('Hour'),
                            'value' => function ($data) use ($params) {
                                $result = 0;
                                foreach ($params['lesson_dates'] as $item) {
                                    if ($data->id == $item->_subject_topic) {
                                        if (@$params['check'][Yii::$app->formatter->asDate(@$item->lesson_date, 'php:Y-m-d')][@$item->_lesson_pair])
                                            $result += 2;
                                    }
                                }
                                if($result == 0) $result = '';
                                return $result;
                            }
                        ],
                        [
                            'attribute' => '_training_type',
                            'header'=>__('Lesson Date'),
                            'format' => 'raw',
                            'value' => function ($data) use ($params) {
                                $result = null;
                                foreach ($params['lesson_dates'] as $item) {
                                    if ($data->id == $item->_subject_topic) {
                                        if (@$params['check'][Yii::$app->formatter->asDate(@$item->lesson_date, 'php:Y-m-d')][@$item->_lesson_pair])
                                            $result .=  Yii::$app->formatter->asDate($item->lesson_date, 'php:d.m.Y').'<br>';
                                    }
                                }
                                return $result;
                            }
                        ],
                        [
                            'attribute' => '_training_type',
                            'header'=>__('Checked'),
                            'value' => function ($data) use ($params) {
                                $result = null;
                                foreach ($params['lesson_dates'] as $item) {
                                    if ($data->id == $item->_subject_topic) {
                                        if (@$params['check'][Yii::$app->formatter->asDate(@$item->lesson_date, 'php:Y-m-d')][@$item->_lesson_pair])
                                            $result = '+';
                                    }
                                }
                                return $result;
                            }
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

