<?php

use common\models\archive\EStudentDiploma;
use common\models\finance\EStudentContractType;
use common\models\finance\EStudentContract;
use common\models\curriculum\EducationYear;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $searchModel EStudentDiploma
 */
$this->title = __('Archive Diploma List');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php
            $form = ActiveForm::begin(); ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_department')->widget(
                    Select2Default::classname(),
                    [
                        'data' => $searchModel->getDepartmentItems(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Faculty-'),
                        'hideSearch' => false,
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_specialty_id')->widget(
                    Select2Default::classname(),
                    [
                        'data' => $searchModel->getSpecialtyItems(),
                        'allowClear' => true,
                        'placeholder' => __('-Choose Specialty-'),
                        'hideSearch' => false,
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-2">
                <?= $form->field($searchModel, '_group')->widget(
                    Select2Default::classname(),
                    [
                        'data' => $searchModel->getGroupItems(),
                        'allowClear' => true,
                        'hideSearch' => false,
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-3">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Student / Diploma')])->label(false) ?>
            </div>
            <div class="col col-md-1">
                <div class="form-group">
                <?= Html::a("<i class='fa fa-download'></i> ", Url::current(['download' => 1]), ['class' => 'btn btn-success', 'data-pjax' => 0]) ?>
                </div>
            </div>
            <?php
            ActiveForm::end(); ?>
        </div>
    </div>

    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'sticky' => '#sidebar',
            'toggleAttribute' => 'published',
            'toggleHeader' => __('Published'),
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => '_student',
                    // 'enableSorting' => true,
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'>%s, %s</p>", $data->student->fullName, $data->education_type_name, $data->education_form_name);
                    },
                ],
                [
                    'attribute' => 'specialty_name',
                    'format' => 'raw',
                    'value' => function (EStudentDiploma $data) {
                        return sprintf("%s<p class='text-muted'>%s</p>", $data->specialty_name, $data->specialty_code);
                    },
                ],
                [
                    'attribute' => 'diploma_number',
                    'header' => __('Diploma Number'),
                    'format' => 'raw',
                    'value' => function (EStudentDiploma $data) {
                        return sprintf(
                            "%s<p class='text-muted'>%s</p>",
                            $data->diploma_number,
                            $data->getCategoryLabel()
                        );
                    },
                ],
                [
                    'attribute' => 'register_number',
                    'header' => __('Register Number'),
                    'format' => 'raw',
                    'value' => function (EStudentDiploma $data) {
                        return sprintf(
                            "%s &nbsp;&nbsp;(%s)",
                            $data->register_number,
                            $data->register_date->format('Y-m-d')
                        );
                    },
                ],

                [
                    'attribute' => 'pdf',
                    'header' => __('Files'),
                    'format' => 'raw',
                    'value' => function (EStudentDiploma $data) {
                        $html = '';
                        if (file_exists($data->getDiplomaFilePath()))
                            $html .= Html::a(
                                    "<i class='fa fa-download'></i> " . __('Diploma'),
                                    ['diploma-list', 'diploma' => $data->id],
                                    ['data-pjax' => 0, 'class' => 'btn btn-default']
                                ) . ' ';
                        if (file_exists($data->getSupplementFilePath()))
                            $html .= Html::a(
                                "<i class='fa fa-download'></i> " . __('Supplement'),
                                ['diploma-list', 'supplement' => $data->id],
                                ['data-pjax' => 0, 'class' => 'btn btn-default']
                            );
                        return $html;
                    }
                ]
            ],
        ]
    ); ?>
</div>
<?php Pjax::end() ?>
