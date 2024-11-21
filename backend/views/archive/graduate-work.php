<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\archive\ECertificateCommittee;
use common\models\archive\EGraduateQualifyingWork;
use common\models\structure\EDepartment;
use common\models\system\AdminRole;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model ECertificateCommittee
 * @var $searchModel EGraduateQualifyingWork
 */

$this->title = __('Graduate Qualifying Work');

$this->params['breadcrumbs'][] = $this->title;
$disabled = false;
if (($this->_user()->role->code === AdminRole::CODE_DEAN || $this->_user(
    )->role->code === AdminRole::CODE_DEPARTMENT)) {
    $disabled = true;
}
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
                        <?php
                        $form = ActiveForm::begin(); ?>
                        <div class="col col-md-1">
                            <div class="form-group">
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-plus"></i> ' . __('Create'),
                                    ['archive/graduate-work-edit'],
                                    ['data-pjax' => 0, 'class' => 'btn btn-success btn-flat']
                                ) ?>
                            </div>
                        </div>
                        <div class="col col-md-1">
                            <div class="form-group">
                                <?= $this->getResourceLink(
                                    '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Eksport'),
                                    ['archive/graduate-work', 'download' => 1],
                                    ['class' => 'btn btn-flat btn-success', 'data-pjax' => 0]
                                ) ?>
                            </div>
                        </div>

                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_faculty')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_semester', 'semester.name'),
                                    'data' => EDepartment::getFaculties(),
                                    'allowClear' => true,
                                    'disabled' => $disabled,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_education_type')->widget(
                                Select2Default::classname(),
                                [
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
                            <?= $form->field($searchModel, '_specialty')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        $dataProvider->query->all(),
                                        '_specialty',
                                        'specialty.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-2">
                            <?php
                            $groups = EGraduateQualifyingWork::find()->select(
                                ['_group', '_faculty', '_specialty', '_education_type']
                            );
                            if ($searchModel->_faculty) {
                                $groups->andFilterWhere(['_faculty' => $searchModel->_faculty]);
                            }
                            if ($searchModel->_education_type) {
                                $groups->andFilterWhere(['_education_type' => $searchModel->_education_type]);
                            }
                            if ($searchModel->_specialty) {
                                $groups->andFilterWhere(['_specialty' => $searchModel->_specialty]);
                            }
                            ?>
                            <?= $form->field($searchModel, '_group')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        $groups->distinct()->all(),
                                        '_group',
                                        'group.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <?php
                        ActiveForm::end(); ?>
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
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        $data->student->fullName,
                                        ['archive/graduate-work-edit', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                },
                            ],
                            [
                                'attribute' => '_education_type',
                                'value' => 'educationType.name',
                            ],
                            [
                                'attribute' => '_specialty',
                                'value' => function ($data) {
                                    return sprintf('%s (%s)', $data->specialty->code, $data->educationYear->name);
                                },
                            ],
                            [
                                'attribute' => '_group',
                                'value' => 'group.name'
                            ],
                            [
                                'attribute' => 'work_name',
                                // 'enableSorting' => true,
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(
                                        $data->work_name,
                                        ['archive/graduate-work-edit', 'id' => $data->id],
                                        ['data-pjax' => 0]
                                    );
                                },
                            ],
                            [
                                'attribute' => 'supervisor_name',
                                'value' => function ($data) {
                                    if (empty($data->advisor_name)) {
                                        return $data->supervisor_name;
                                    }
                                    return $data->supervisor_name . ' - ' . $data->advisor_name;
                                }
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
