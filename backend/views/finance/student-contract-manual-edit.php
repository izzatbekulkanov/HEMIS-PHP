<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\ContractSummaType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\grid\SerialColumn;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\curriculum\Semester;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EStudentSubject;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\ContractType;
use common\models\structure\EDepartment;
use common\models\finance\EStudentContractType;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['url' => ['finance/student-contract-manual'], 'label' => __('Finance Student Contract Manual')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row">
        <div class="col col-md-12 col-lg-12">
            <div class="box box-primary ">
                <div class="box-header bg-gray with-border">
                    <h3 class="box-title"><?= __('Students in Group') ?></h3>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>

                        <div class="col col-md-6">
                            <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                                'data' => EDepartment::getFaculties(),
                                'allowClear' => true,
                                'hideSearch' => false,
                                'disabled' => $faculty != null,
                                'options' => [
                                    'id' => '_department',

                                ],
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?php
                            $specialties = array();
                            if ($searchModel->_department) {
                                $specialties = ESpecialty::getHigherSpecialty($searchModel->_department);
                            }
                            if ($faculty) {
                                $specialties = ESpecialty::getHigherSpecialty($faculty);
                            }
                            ?>
                            <?= $form->field($searchModel, '_specialty_id')->widget(DepDrop::classname(), [
                                'data' => $specialties,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'id' => '_specialty',
                                    'placeholder' => __('-Choose Specialty-'),
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_department'],
                                    'url' => Url::to(['/ajax/get_specialty']),
                                    'placeholder' => __('-Choose Specialty-'),
                                ],
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-3">
                            <?= $form->field($searchModel, '_education_form')->widget(Select2Default::class, [
                                'data' => EducationForm::getClassifierOptions(),
                                'hideSearch' => false,
                                'options' => [
                                    'id' => '_education_form',
                                    'required' => true,
                                ]
                            ])->label(false);; ?>
                        </div>
                        <div class="col col-md-3">
                            <?php
                            $groups = array();
                            if ($searchModel->_department && $searchModel->_specialty_id && $searchModel->_education_form) {
                                $groups = EGroup::getOptionsByFacultyEduForm($searchModel->_department, $searchModel->_specialty_id, $searchModel->_education_form);
                            }
                            ?>
                            <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                                'data' => $groups,
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginLoading' => false,
                                'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                                'options' => [
                                    'id' => '_group',
                                    'placeholder' => __('-Choose Group-'),
                                    'required' => true
                                ],
                                'pluginOptions' => [
                                    'depends' => ['_department', '_specialty', '_education_form'],
                                    'url' => Url::to(['/ajax/get-group-by-specialty-edu-form']),
                                    'required' => true
                                ],
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by student fullName / Pasport / PIN / Code')])->label(false) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>


                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        /*[
                            '__class' => 'yii\grid\CheckboxColumn',
                        ],*/
                        ['class' => 'yii\grid\SerialColumn'],

                        [
                            'attribute' => '_student',
                            'value' => 'student.fullName',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->student->fullName;
                            },
                        ],
                        [
                            'attribute' => '_education_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                            },
                        ],
                        [
                            'attribute' => '_specialty_id',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return sprintf("%s<p class='text-muted'> %s</p>", $data->specialty->code, $data->paymentForm->name);
                            },
                        ],
                        [
                            'attribute' => '_group',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return sprintf("%s", $data->group->name);
                            },
                        ],
                        [
                            'attribute' => 'student._citizenship',
                            'value' => 'student.citizenship.name',
                        ],
                        [
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a(__('Add Contract Manual'), linkTo(['student-contract-manual-edit', 'student' => $data->id]), ['class' => 'btn btn-default btn-block', 'data-pjax' => 0]);
                            },
                        ],



                    ],
                ]); ?>
            </div>


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
<?php Pjax::end() ?>