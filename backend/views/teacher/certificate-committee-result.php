<?php
/*
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\archive\ECertificateCommittee;
use common\models\structure\EDepartment;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model ECertificateCommittee
 * @var $university \common\models\structure\EUniversity
 */

$this->title = __('Certificate Committee Result');

$this->params['breadcrumbs'][] = $this->title;

?>
<?php
Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php
                if ($this->_user()->role->code !== "teacher") { ?>
                    <?php
                    $form = ActiveForm::begin(); ?>
                    <div class="row" id="data-grid-filters">

                        <div class="col col-md-2">
                            <div class="form-group">
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-plus"></i> ' . __('Create'),
                                    ['teacher/certificate-committee-result-edit'],
                                    ['data-pjax' => 0, 'class' => 'btn btn-success btn-flat']
                                ) ?>
                            </div>
                        </div>
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_education_year')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => \common\models\curriculum\EducationYear::getEducationYears(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => '_education_year',
                                    ],

                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_faculty')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_education_year', 'educationYear.name'),
                                    'data' => EDepartment::getFaculties(),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'options' => [
                                        'id' => 'faculty',
                                    ],
                                    'disabled' => ($this->_user()->role->code === AdminRole::CODE_DEAN || $this->_user(
                                        )->role->code === AdminRole::CODE_DEPARTMENT)
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_department')->widget(
                                DepDrop::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        EDepartment::getDepartmentList($searchModel->_faculty ?? ""),
                                        'id',
                                        'name'
                                    ),
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'pluginLoading' => false,
                                    'select2Options' => [
                                        'pluginOptions' => ['allowClear' => true,],
                                        'theme' => Select2::THEME_DEFAULT,
                                    ],
                                    'options' => [
                                        'id' => 'department',
                                        'placeholder' => __('-Choose Department-'),
                                    ],
                                    'disabled' => $this->_user()->role->code === AdminRole::CODE_DEPARTMENT,
                                    'pluginOptions' => [
                                        'depends' => ['faculty'],
                                        'url' => Url::to(['/ajax/get-departments']),
                                    ],
                                ]
                            )->label(false); ?>
                        </div>


                    </div>

                    <div class="row" id="data-grid-filters">
                        <div class="col col-md-2">
                        </div>
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_education_type')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => ArrayHelper::map(
                                        $dataProvider->getModels(),
                                        '_education_type',
                                        'educationType.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_specialty')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        $dataProvider->getModels(),
                                        '_specialty',
                                        'specialty.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?= $form->field($searchModel, '_group')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        $dataProvider->getModels(),
                                        '_group',
                                        'group.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>

                    </div>
                        <?php
                        ActiveForm::end(); ?>
                    <?php
                } ?>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'sticky' => '#sidebar',
                        'dataProvider' => $dataProvider,
                        'toggleAttribute' => 'active',
                        'columns' => [
                            [
                                'class' => SerialColumn::class,
                            ],
                            [
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        $data->student->fullName,
                                        ['teacher/certificate-committee-result-edit', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                }
                            ],
                            [
                                'attribute' => '_group',
                                'value' => 'group.name',
                            ],
                            [
                                'attribute' => '_education_type',
                                'value' => 'educationType.name',
                            ],
                            [
                                'attribute' => '_specialty',
                                'value' => 'specialty.code',
                            ],
                            [
                                'attribute' => '_certificate_committee',
                                'value' => 'certificateCommittee.name',
                            ],
                            [
                                'attribute' => 'order_number',
                                'header' => __('Order Number & Date'),
                                'value' => function ($data) {
                                    return $data->order_number . ' - '
                                        . Yii::$app->formatter->asDate($data->order_date);
                                }
                            ],
                            [
                                'attribute' => 'grade',
                                'value' => function ($data) {
                                    return sprintf('%s (%s)', $data->grade, $data->ball);
                                }
                            ],
                            [
                                'attribute' => '_education_year',
                                'value' => 'educationYear.name',
                            ],
                        ],
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>

<?php
Pjax::end() ?>
