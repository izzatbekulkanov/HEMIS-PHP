<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\student\ESpecialty;
use common\models\system\classifier\DoctoralStudentType;

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
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Insert Doctorate Student'),
                        ['science/doctorate-student-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_specialty_id')->widget(Select2Default::classname(), [
                    'data' => ESpecialty::getDoctorateSpecialtyList(),
                    'allowClear' => true,
                    'hideSearch' => false,
                    /* 'options' => [
                         'placeholder' => __('Choose Doctorate Specialty'),
                     ]*/
                ])->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_doctoral_student_type')->widget(Select2Default::classname(), [
                    'data' => DoctoralStudentType::getClassifierOptions(),
                    'allowClear' => true,
                    'hideSearch' => false,
                ])->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'student_id_number',
            [
                'attribute' => 'second_name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->fullName, ['science/doctorate-student', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_specialty_id',
                'value' => 'specialty.code',
                'header' => __('Doctorate Specialty'),
            ],
            [
                'attribute' => '_doctoral_student_type',
                'value' => 'doctoralStudentType.name',
                //  'header' => __('Doctorate Specialty'),
            ],
            [
                'attribute' => 'passport_number',
            ],
            [
                'attribute' => 'birth_date',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDate($data->birth_date, 'dd-MM-Y');
                },
            ],
            [
                'attribute' => 'id',
                'header' => __('Defense'),
                'format' => 'raw',
                'value' => function ($data) {
                    if (count($data->eDissertationDefenses) > 0) {
                        return Html::a('<i class="fa fa-file-o"></i>', ['science/dissertation-defense-edit', 'id' => $data->id], ['data-pjax' => 0]);
                    } else {
                        return '-';
                    }
                },
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
<?php Pjax::end() ?>
