<?php

use common\models\curriculum\ESubjectSchedule;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

$this->params['breadcrumbs'][] = ['url' => ['teacher/rating-journal']];
$this->params['breadcrumbs'][] = [
    'url' => [
        'teacher/rating-journal',
        'education_year' => $model->_education_year,
        'semester' => $model->_semester,
        'group' => $model->_group,
        'subject' => $model->_subject,
        'training_type' => $model->_training_type
    ],
    'label' => __('Rating of Subject Group')
];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>
<div class="row">

    <div class="col col-md-8 col-lg-8">
        <?php $form = \yii\widgets\ActiveForm::begin(); ?>

        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <? //php $form = ActiveForm::begin(); ?>
                    <?php /*if (!$canGrade): */ ?><!--
                        <div class="col col-md-12">
                            <div class="alert alert-warning">
                                <p>
                                    <? /*= __('You can only give grades on the day of the subject\'s schedule.') */ ?>
                                </p>
                            </div>
                        </div>
                    --><?php /*endif; */ ?>
                    <div class="col col-md-2">
                        <div class="form-group">
                            <?= '<label class="control-label">' . $model->getAttributeLabel(
                                '_subject_topic'
                            ) . '</label>' ?>
                        </div>
                    </div>
                    <div class="col col-md-10">
                        <?= Select2::widget([
                                                'model' => $model,
                                                'attribute' => '_subject_topic',
                                                'data' => ArrayHelper::map($topics, 'id', 'name'),
                                                'theme' => Select2::THEME_DEFAULT,
                                                'options' => ['placeholder' => __('Choose'), 'required' => true],
                                                'pluginOptions' => [
                                                    'allowClear' => true,
                                                ],
                                            ]);
                        ?>
                    </div>
                </div>
            </div>
            <table class="table table-striped table-bordered">
                <tr>
                    <th><?= __('№'); ?></th>
                    <th><?= __('Fullname of Student'); ?></th>
                    <th style="width: 15%"><?= __('Grade'); ?></th>
                </tr>
                <?php
                $i = 1;
                foreach ($students as $item) {
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $item->student->fullName; ?></td>

                        <td>
                            <?php
                            $disabled = isset($grades[$item->_student]);
                            echo Html::textInput(
                                'grade[' . $item->_student . ']',
                                $grades[$item->_student] ?? null,
                                [
                                    'class' => 'form-control',
                                    'type' => 'number',
                                    'min' => 1,
                                    'max' => 100,//$max_ball,
                                    'step' => 1,
                                    //'required' => true,
                                    'id' => @$item->_student . '_s',
                                    'disabled' => !$canGrade
                                ]
                            );
                            ?>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th colspan="4">
                        <?php
                        if ($canGrade/* && !$disabled*/):
                            echo Html::submitButton(
                                '<i class="fa fa-check"></i> ' . __('Save'),
                                ['class' => 'btn btn-primary btn-flat pull-right', 'name' => 'btn']
                            );
                        endif;
                        ?>
                        <?php \yii\widgets\ActiveForm::end(); ?> </th>
                </tr>
            </table>
        </div>
    </div>

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                                           'model' => $model,
                                           'attributes' => [
                                               [
                                                   'attribute' => '_subject',
                                                   'value' => function (ESubjectSchedule $data) {
                                                       return $data->subject ? $data->subject->name : '';
                                                   }
                                               ],
                                               [
                                                   'attribute' => '_training_type',
                                                   'value' => function (ESubjectSchedule $data) {
                                                       return $data->trainingType ? $data->trainingType->name : '';
                                                   }
                                               ],

                                               [
                                                   'attribute' => 'lesson_date',
                                                   'value' => function (ESubjectSchedule $data) {
                                                       return Yii::$app->formatter->asDate(
                                                           $data->lesson_date,
                                                           'dd-MM-Y'
                                                       );
                                                   }
                                               ],
                                               [
                                                   'attribute' => '_group',
                                                   'value' => function (ESubjectSchedule $data) {
                                                       return $data->group ? $data->group->name : '';
                                                   }
                                               ],
                                               [
                                                   'attribute' => '_marking_system',
                                                   'label' => __('Marking System'),
                                                   'value' => function (ESubjectSchedule $data) {
                                                       return $data->curriculum && $data->curriculum->markingSystem ? $data->curriculum->markingSystem->name : '';
                                                   }
                                               ],
                                           ],
                                       ]) ?>
            </div>
            <br/>
            <div class="row" id="data-grid-filters">
                <div class="col col-md-10">
                    <div class="form-group">
                        <? $this->getResourceLink(
                            '<i class="fa fa-list"></i> ' . __('Rating Journal'),
                            [
                                'teacher/rating-journal',
                                'education_year' => $model->_education_year,
                                'semester' => $model->_semester,
                                'group' => $model->_group,
                                'subject' => $model->_subject,
                                'training_type' => $model->_training_type
                            ],
                            ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php
$this->registerJs(
    <<<JS

    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
JS
);
$this->registerCss(
    <<<CSS
select:invalid + .select2-container > span.selection > span.select2-selection {
 border: 1px red solid;
}
.select2-hidden-accessible {
    visibility: visible !important;
    top: 30px;
    left: 5px;
}
select.select2{
    position: static !important;
    outline:none !important;
}
CSS
)
?>

<?php Pjax::end() ?>
