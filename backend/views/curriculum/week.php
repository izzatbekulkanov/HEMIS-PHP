<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\Semester;

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\date\DatePicker;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/curriculum'], 'label' => __('List Curriculum')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php $form = ActiveForm::begin(); ?>
                <div class="row" id="data-grid-filters">
                    <div class="col col-sm-4">
                        <div class="form-group">
                            <?= $this->getResourceLink(
                                '<i class="fa fa-rotate-right"></i>&nbsp;&nbsp;' . __('Refresh Weeks'),
                                ['curriculum/week', 'id'=>$model->id, 'refresh' => 1],
                                ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_level')->widget(\backend\widgets\Select2Default::classname(), [
                            'data' => Course::getClassifierOptions(),
                            'hideSearch' => false
                        ])->label(false) ?>
                    </div>
                    <div class="col col-md-4">
                        <?= $form->field($searchModel, '_semester')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Semester::find()->where(['_curriculum' => $model->id, 'active' => true])->orderBy(['position' => SORT_ASC])->all(), 'code', 'name'),
                            'options' => [
                                'class' => 'select2',
                                'placeholder' => __('-Choose Semester-'),
                            ],
                            'theme' => Select2::THEME_DEFAULT,
                            'pluginLoading' => false,
                            'hideSearch' => false,
                            'pluginOptions' => [
                                'allowClear' => true,
                                'placeholder' => __('-Choose Semester-'),
                            ],
                        ])->label(false) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover'],
                    'rowOptions' => function ($data) use ($weekModel) {
                        $class = ['class' => ''];
                        if ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_ATTESTATION) {
                            $class = ['class' => 'success']; //12
                        } elseif ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_PRACTICUM) {
                            $class = ['class' => 'info']; //13
                        } elseif ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_GOV_ATTESTATION) {
                            $class = ['class' => 'warning']; //14
                        } elseif ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_GOV_GRADUATION) {
                            $class = ['class' => 'bg-teal']; //15
                        } elseif ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_HOLIDAY) {
                            $class = ['class' => 'danger']; //16
                        } elseif ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_CREDIT) {
                            $class = ['class' => 'bg-aqua']; //17
                        } elseif ($data->_education_week_type == EducationWeekType::EDUCATION_WEEK_TYPE_SCIENCE) {
                            $class = ['class' => 'bg-purple']; //18
                        }
                        if ($data->id == $weekModel->id) {
                            $class['class'] = $class['class'] . ' text-bold ';
                        }
                        return $class;
                    },
                    'columns' => [
                        [
                            'attribute' => 'position',
                            'header' => __('#'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->position, ['curriculum/week', 'id' => $data->_curriculum, 'code' => $data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute' => 'start_date',
                            'header' => __('Week'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a(Yii::$app->formatter->asDate($data->start_date) . ' - ' . Yii::$app->formatter->asDate($data->end_date), ['curriculum/week', 'id' => $data->_curriculum, 'code' => $data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute' => '_level',
                            'value' => 'level.name',
                        ],
                        [
                            'attribute' => '_semester',
                            'value' => 'semester.name',
                        ],
                        [
                            'attribute' => '_education_week_type',
                            'value' => 'educationWeekType.name',
                        ],

                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(); ?>
        <div class="box box-default ">
            <?php //echo $form->errorSummary($model)?>
            <?php if (!$weekModel->isNewRecord): ?>
                <div class="box-header bg-gray">
                    <h4 class="title">
                        <?php echo __('Number of week') . ': ' . '<b>' . $weekModel->position . '</b>'; ?>
                    </h4>
                </div>
            <?php endif; ?>
            <div class="box-body">
                <?php
                if (!$weekModel->isNewRecord) {
                    $weekModel->start_date = Yii::$app->formatter->asDate($weekModel->start_date, 'php:Y-m-d');
                    $weekModel->end_date = Yii::$app->formatter->asDate($weekModel->end_date, 'php:Y-m-d');
                }
                ?>
                <?= $form->field($weekModel, 'start_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => __('Enter start_date')],
                    'layout' => '{input}{picker}{remove}',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'daysOfWeekDisabled' => [0, 7],
                        'weekStart' => '1',
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]); ?>

                <?= $form->field($weekModel, 'end_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => __('Enter end_date')],
                    'layout' => '{input}{picker}{remove}',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'daysOfWeekDisabled' => [0, 7],
                        'weekStart' => '1',
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]); ?>

                <?= $form->field($weekModel, '_level')->widget(Select2::classname(), [
                    'data' => Course::getClassifierOptions(),
                    'options' => [
                        'class' => 'select2',
                        'id' => '_level',
                        'placeholder' => __('-Choose-'),
                    ],
                    'theme' => Select2::THEME_DEFAULT,
                    'pluginLoading' => false,
                    'hideSearch' => false,
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => __('-Choose-'),
                    ],
                ]); ?>
                <?= $form->field($weekModel, '_semester')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(Semester::find()->where(['_curriculum' => $model->id, 'active' => true])->orderBy(['code' => SORT_ASC])->all(), 'code', 'name'),
                    'options' => [
                        'class' => 'select2',
                        'id' => '_semester',
                        'placeholder' => __('-Choose-'),
                    ],
                    'theme' => Select2::THEME_DEFAULT,
                    'pluginLoading' => false,
                    'hideSearch' => false,
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => __('-Choose-'),
                    ],
                ]); ?>
                <?= $form->field($weekModel, '_education_week_type')->widget(Select2::classname(), [
                    'data' => EducationWeekType::getClassifierOptions(),
                    'options' => [
                        'class' => 'select2',
                        'id' => '_education_week_type',
                        'placeholder' => __('-Choose-'),
                    ],
                    'theme' => Select2::THEME_DEFAULT,
                    'pluginLoading' => false,
                    'hideSearch' => false,
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => __('-Choose-'),
                    ],
                ]); ?>

            </div>
            <div class="box-footer text-right">
                <?php if (!$weekModel->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/week', 'id' => $model->id], ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]) ?>
                    <?php if (!$model->accepted): ?>
                        <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!$model->accepted): ?>
                    <?php if (count(Semester::getSemesterByCurriculum($model->id)) == 0): ?>
                        <?= $this->getResourceLink(__('Delete'), ['curriculum/week', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
                <?php endif; ?>


            </div>
        </div>
        <?php ActiveForm::end(); ?>
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

<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['curriculum/week'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>

