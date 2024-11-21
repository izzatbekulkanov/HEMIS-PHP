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

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectSchedule */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

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

                <?php ActiveForm::end(); ?>
            </div>

    </div>
    <?= GridView::widget([
                             'id' => 'data-grid',
                             'dataProvider' => $dataProvider,
                             'columns' => [
                                 ['class' => 'yii\grid\SerialColumn'],
                                 [
                                     'attribute' => '_group',
                                     'format' => 'raw',
                                     'value' => function ($data) {
                                         return Html::a(
                                             $data->group->name,
                                             [
                                                 'rating-journal',
                                                 'education_year' => $data->_education_year,
                                                 'semester' => $data->_semester,
                                                 'group' => $data->_group,
                                                 'subject' => $data->_subject,
                                                 'training_type' => $data->_training_type
                                             ],
                                             ['data-pjax' => 0]
                                         );
                                     },
                                 ],
                                 [
                                     'attribute' => '_subject',
                                     'format' => 'raw',
                                     'value' => function ($data) {
                                         return Html::a(
                                             $data->subject->name,
                                             [
                                                 'rating-journal',
                                                 'education_year' => $data->_education_year,
                                                 'semester' => $data->_semester,
                                                 'group' => $data->_group,
                                                 'subject' => $data->_subject,
                                                 'training_type' => $data->_training_type
                                             ],
                                             ['data-pjax' => 0]
                                         );
                                     },
                                 ],
                                 [
                                     'attribute' => '_training_type',
                                     'value' => 'trainingType.name',

                                 ],
                                 [
                                     'attribute' => '_education_year',
                                     'value' => 'educationYear.name',
                                 ],
                                 [
                                     'attribute' => '_semester',
                                     'value' => function ($data) {
                                         return Semester::getByCurriculumSemester(
                                             $data->group->_curriculum,
                                             $data->_semester
                                         )->name;
                                     },
                                 ],
                                 /* [
                                      'attribute'=>'_employee',
                                      'value' => 'employee.fullName',
                                  ],*/
                             ],
                         ]); ?>
</div>
<?php
$this->registerJs(
    '
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
'
)
?>

<?php Pjax::end() ?>
