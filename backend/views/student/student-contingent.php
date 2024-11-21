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

//use common\models\curriculum\ECurriculum;
/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">

    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row" id="data-grid-filters">

            <div class="col col-md-4">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Import Students'),
                        ['student/student-contingent', 'download' => 1],
                        ['class' => 'btn btn-flat btn-success', 'data-pjax' => 0]
                    ) ?>

                    <?= $this->getResourceLink(
                        '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Export Students'),
                        ['student/student-contingent', 'contingent-download' => 1],
                        ['class' => 'btn btn-flat btn-success', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
            <div class="col col-md-2">

            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by student fullName / Pasport / PIN / Code')])->label(false) ?>
            </div>
        </div>
        <div class="row" id="data-grid-filters">


            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                    'data' => EducationForm::getClassifierOptions(),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_education_form'
                    ],
                ])->label(false); ?>
            </div>
            <?php
            $curriculums = ECurriculum::getOptions($faculty);
            if ($searchModel->_education_form) {
                $curriculums = ECurriculum::getOptionsByEduForm($searchModel->_education_form, $faculty);
            }
            ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                    'data' => $curriculums,
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>

                <? /*= $form->field($searchModel, '_specialty')->widget(Select2Default::classname(), [
                    'data' => ESpecialty::getHigherSpecialty($faculty),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); */ ?>
            </div>
            <?php
            $semesters = array();
            if ($searchModel->_curriculum) {
                $semesters = Semester::getSemesterByCurriculum($searchModel->_curriculum);
            }
            ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map($semesters,'code', 'name'),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_semestr'
                    ],
                ])->label(false); ?>
            </div>
            <?php
            $groups = array();
            if ($searchModel->_curriculum) {
                $groups = EGroup::getOptions($searchModel->_curriculum);
            }
            ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                    'data' => $groups,
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_group'
                    ],
                ])->label(false); ?>
            </div>
            <? /*<div class="col col-md-2">
                <?= $form->field($searchModel, '_level')->widget(Select2Default::classname(), [
                    'data' => Course::getClassifierOptions(),
                    'hideSearch' => false,
                    'options' => [
                        'id' => '_level'
                    ],
                ])->label(false); ?>
            </div> */?>




        </div>
        <?php ActiveForm::end(); ?>
    </div>


    <?= GridView::widget([
        'id' => 'data-grid',
       // 'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'student_id_number',
                'value' => 'student.student_id_number'
            ],
            [
                'attribute' => 'student.passport_number',
                'value' => 'student.passport_number'
            ],
            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->student ? $data->student->fullName : '-', ['student/student-contingent-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],

            [
                'attribute' => '_specialty_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", @$data->specialty->code, @$data->paymentForm->name);
                },
            ],
            [
                'attribute' => '_education_year',
                'value' => 'educationYear.name',
            ],
            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", @$data->educationType->name, @$data->educationForm->name);
                },
            ],
            [
                'attribute' => '_semestr',
                'format' => 'raw',
                'value' => function ($data) {
                    if(Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr) != null)
                        $semester  = Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr)->name;
                    elseif($data->semester)
                        $semester =  $data->semester->name;
                    else
                        $semester = \common\models\system\classifier\Semester::findOne($data->_semestr)->name;

                    return sprintf("%s<p class='text-muted'> %s</p>", $semester, @$data->level->name);
                },
            ],
            [
                'attribute' => '_group',
                'value' => 'group.name',
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
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
