<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\structure\EDepartment;
use common\models\student\EStudentMeta;
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

//use common\models\curriculum\ECurriculum;
/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'disabled' => $faculty != null,
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_student_status')->widget(Select2Default::classname(), [
                    'data' => \common\models\system\classifier\StudentStatus::getTransferStatusOptions(),
                ])->label(false); ?>
            </div>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / ID Number / Passport / Decree Number / Decree Date')])->label(false) ?>
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
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function (EStudentMeta $data) {
                    return sprintf("%s <p class='text-muted'>%s / %s / %s</p>", $data->student->getFullName(), $data->student->student_id_number, $data->student->passport_number, $data->department->name);
                },
            ],
            [
                'attribute' => '_education_type',
                'format' => 'raw',
                'value' => function (EStudentMeta $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                },
            ],
            [
                'attribute' => '_level',
                'format' => 'raw',
                'value' => function (EStudentMeta $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->level->name, $data->semester->name);
                },
            ],
            [
                'attribute' => '_group',
                'format' => 'raw',
                'value' => function (EStudentMeta $data) {
                    return sprintf('%s<p class="text-muted">%s</p>', $data->group->name, $data->educationYear->name);
                },

            ],
            [
                'attribute' => '_student_status',
                'value' => 'studentStatus.name',
            ],
            [
                'attribute' => 'order_number',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s <p class='text-muted'> %s</p>",
                        $data->order_number,
                        $data->order_date ? Yii::$app->formatter->asDate($data->order_date, 'php:d.m.Y') : '-'
                        );
                },
            ],

        ],
    ]); ?>
</div>
</script>
<?php Pjax::end() ?>
