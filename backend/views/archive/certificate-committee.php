<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\archive\ECertificateCommittee;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model ECertificateCommittee
 * @var $university \common\models\structure\EUniversity
 */

$this->title = __('Certificate Committee');

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
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-3 ">
                            <div class="form-group">
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-plus"></i> ' . __('Create'),
                                    ['archive/certificate-committee-edit'],
                                    ['data-pjax' => 0, 'class' => 'btn btn-success btn-flat']
                                ) ?>
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-users"></i> ' . __('Members'),
                                    ['archive/certificate-committee-member'],
                                    ['data-pjax' => 0, 'class' => 'btn btn-info btn-flat']
                                ) ?>
                            </div>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_education_type')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_education_year', 'educationYear.name'),
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
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_education_year')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => ArrayHelper::map(
                                        $dataProvider->getModels(),
                                        '_education_year',
                                        'educationYear.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_department')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map($dataProvider->getModels(), '_department', 'department.name'),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
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
                                'attribute' => 'name',
                                // 'enableSorting' => true,
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        $data->name,
                                        ['archive/certificate-committee-edit', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                },
                            ],
                            [
                                'attribute' => 'type',
                                'value' => 'typeLabel',
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
                                'attribute' => '_department',
                                'value' => 'department.name',
                            ],
                            [
                                'attribute' => '_education_year',
                                'value' => 'educationYear.name',
                            ],
                            [
                                'attribute' => 'members_count',
                                'header' => __('Members Count'),
                                'value' => 'membersCount',
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
