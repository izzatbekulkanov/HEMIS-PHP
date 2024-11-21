<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\DatePickerDefault;
use backend\widgets\Select2Default;
use common\models\employee\EEmployeeMeta;
use common\models\employee\EEmployeeProfessionalDevelopment;
use common\models\system\classifier\Qualification;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\StudentType;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\CitizenshipType;
use common\models\finance\EMinimumWage;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\system\classifier\Country;
use common\models\system\classifier\ProjectCurrency;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title = $model->isNewRecord ? __('Create Contract Price') : $model->specialty->name;
$this->params['breadcrumbs'][] = [
    'url' => ['finance/contract-price'],
    'label' => __('Contract Price'),
];
$this->params['breadcrumbs'][] = $this->title;

$user = $this->context->_user();

?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
<?php echo $form->errorSummary($model)?>
<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                            'data' => EDepartment::getFaculties(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            //  'disabled' => $faculty != null,
                            'options' => [
                                'id' => '_department',

                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $specialties = array();
                        if ($model->_department) {
                        $specialties = ESpecialty::getHigherSpecialty($model->_department);
                        }

                        ?>
                        <?= $form->field($model, '_specialty')->widget(DepDrop::classname(), [
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
                        ]); ?>


                    </div>

                </div>
                <div class="row">

                    <div class="col-md-3">
                        <?= $form->field($model, '_education_form')->widget(
                            Select2Default::classname(),
                            [
                                'data' => EducationForm::getClassifierOptions(),
                                'allowClear' => false,
                                'placeholder' => false,
                            ]
                        ); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, '_student_type')->widget(
                            Select2Default::classname(),
                            [
                                'data' => StudentType::getClassifierSpecialOptions(StudentType::STUDENT_TYPE_SUPER_CONTRACT),
                                'allowClear' => false,
                                'placeholder' => false,
                            ]
                        ); ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($model, 'coefficient')->textInput(['maxlength' => true, 'id' => 'coefficient']) ?>
                    </div>
                    <div class="col col-md-3">
                        <?= $form->field($model, 'summa')->textInput(['maxlength' => true, 'id' => 'summa']) ?>
                    </div>

                </div>
            </div>
        </div>
        <div class="box-footer text-right">
            <?= $this->getResourceLink(__('Cancel'), ['finance/contract-price'], ['class' => 'btn btn-default btn-flat']) ?>
            <?php if (!$model->isNewRecord): ?>
                <?= $this->getResourceLink(
                    __('Delete'),
                    ['finance/contract-price-edit', 'id' => $model->id, 'delete' => 1],
                    ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0],
                    'finance/contract-price-edit'
                ) ?>
            <?php endif; ?>
            <?= Html::submitButton(
                '<i class="fa fa-check"></i> ' . __('Save'),
                ['class' => 'btn btn-primary btn-flat']
            ) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>