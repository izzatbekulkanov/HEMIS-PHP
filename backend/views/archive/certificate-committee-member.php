<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\archive\ECertificateCommittee;
use common\models\structure\EDepartment;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Manage Certificate Committee Members');
$this->params['breadcrumbs'][] = ['url' => ['archive/certificate-committee'], 'label' => __('Certificate Committee')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
$disabled = false;
if (!$model->isNewRecord) {
    $model->_faculty = $model->certificateCommittee->_faculty;
}
if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
    $disabled = true;
    $model->_faculty = $this->_user()->employee->deanFaculties->id;
    $searchModel->_faculty = $this->_user()->employee->deanFaculties->id;
} elseif ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
    $disabled = true;
    $model->_faculty = $this->_user()->employee->headDepartments->parent;
    $searchModel->_faculty = $this->_user()->employee->headDepartments->parent;
    $department = $this->_user()->employee->headDepartments->id;
}
Pjax::begin(
    ['id' => 'admin-grid', 'timeout' => false, 'enablePushState' => false]
) ?>
<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php
                    $form = ActiveForm::begin(); ?>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_faculty')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => true,
                                'options' => ['id' => 'faculty'],
                                'disabled' => $disabled,
                                'placeholder' => __('-Choose Faculty-'),
                            ]
                        )->label(false); ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, '_certificate_committee')->widget(
                            DepDrop::classname(),
                            [
                                'data' => ECertificateCommittee::getSelectOptions($searchModel->_faculty ?? "", $department ?? ""),
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true,],
                                    'theme' => Select2::THEME_DEFAULT,
                                ],
                                'options' => [
                                    'id' => 'certificate_committee',
                                    'placeholder' => __('-Choose-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['faculty'],
                                    'url' => Url::to(['/ajax/get-certificate-committee']),
                                ],
                            ]
                        )->label(false); ?>
                    </div>
                    <?php
                    ActiveForm::end(); ?>
                </div>
            </div>
            <?= GridView::widget(
                [
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['__class' => SerialColumn::class],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            //'header' => __('Specialty Name'),
                            'value' => function ($data) {
                                return Html::a(
                                    $data->name,
                                    ['archive/certificate-committee-member', 'id' => $data->id],
                                    ['data-pjax' => 0]
                                );
                            },
                        ],
                        [
                            'attribute' => '_certificate_committee',
                            'header' => __('Certificate Committee'),
                            'format' => 'raw',
                            'value' => 'certificateCommittee.name',
                        ],
                        'work_place',
                        'position',
                        'role',
                    ],
                ]
            ); ?>
        </div>
    </div>
    <div class="col col-md-4 col-lg-4" id="sidebar">
        <div class="box box-default ">
            <div class="box-body">
                <?php
                $form2 = ActiveForm::begin(); ?>
                <?= $form2->field($model, '_faculty')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Faculty-'),
                        'options' => [
                            'id' => '_faculty',
                        ],
                        'disabled' => $disabled
                    ]
                )->label(__('Faculty')); ?>
                <?= $form->field($model, '_certificate_committee')->widget(
                    DepDrop::classname(),
                    [
                        'data' => ($model->isNewRecord && $this->_user()->role->code === AdminRole::CODE_SUPER_ADMIN) ? [] : ECertificateCommittee::getSelectOptions($model->_faculty ?: "", $department ?? ""),
                        'type' => DepDrop::TYPE_SELECT2,
                        'pluginLoading' => false,
                        'select2Options' => [
                            'pluginOptions' => ['allowClear' => true,],
                            'theme' => Select2::THEME_DEFAULT,
                        ],
                        'options' => [
                            'id' => '_certificate_committee',
                            'placeholder' => __('-Choose-'),
                        ],
                        'pluginOptions' => [
                            'depends' => ['_faculty'],
                            'url' => Url::to(['/ajax/get-certificate-committee']),
                        ],
                    ]
                )->label(); ?>
                <?= $form2->field($model, 'name')->textInput() ?>
                <?= $form2->field($model, 'work_place')->textInput() ?>
                <?= $form2->field($model, 'position')->textInput() ?>
                <?= $form2->field($model, 'role')->textInput() ?>
            </div>
            <div class="box-footer text-right">
                <?php
                if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(
                        __('Cancel'),
                        ['archive/certificate-committee-member'],
                        ['class' => 'btn btn-default btn-flat', 'data-pjax' => 0]
                    ) ?>
                    <?= $this->getResourceLink(
                        __('Delete'),
                        ['archive/certificate-committee-member', 'id' => $model->id, 'delete' => 1],
                        ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]
                    ) ?>
                <?php
                endif; ?>
                <?= Html::submitButton(
                    '<i class="fa fa-check"></i> ' . __('Save'),
                    ['class' => 'btn btn-primary btn-flat']
                ) ?>
            </div>
        </div>
        <?php
        ActiveForm::end(); ?>
    </div>
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

<?php
Pjax::end() ?>
