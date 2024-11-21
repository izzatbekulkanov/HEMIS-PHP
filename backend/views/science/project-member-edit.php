<?php

use backend\widgets\DatePickerDefault;
use common\components\Config;
use common\models\science\EDoctorateStudent;
use common\models\science\EProjectMeta;
use common\models\system\classifier\ProjectExecutorType;
use common\models\employee\EEmployee;
use common\models\student\EStudent;
use common\models\science\EProjectExecutor;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\depdrop\DepDrop;
use kartik\date\DatePicker;

/* @var $this \backend\components\View */
/* @var $model \common\models\employee\EEmployeeMeta */

$this->title = __('Project Members Information');
$this->params['breadcrumbs'][] = ['url' => ["science/project"], 'label' => __('Science Project')];
$this->params['breadcrumbs'][] = ['url' => ["science/project", 'id' => $project->id], 'label' => $project->getShortTitle()];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
$this->registerJs("initMemberForm()");
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <?= $form->field($model, '_project_executor_type')->widget(Select2Default::classname(), [
                            'data' => ProjectExecutorType::getClassifierOptions(),
                            'allowClear' => false,
                            'placeholder' => false,
                            'options' => [
                                'id' => '_project_executor_type',
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $members = array();
                        if ($model->_project_executor_type == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_TEACHER) {
                            $members = EEmployee::getEmployees();
                        } elseif ($model->_project_executor_type == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_STUDENT) {
                            $members = EStudent::getStudents();
                        } elseif ($model->_project_executor_type == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_RESEARCHER) {
                            $members = EDoctorateStudent::getDoctorates();
                        }
                        ?>
                        <?= $form->field($model, '_id_number')->widget(DepDrop::classname(), [
                            'data' => $members,
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_id_number',
                                'placeholder' => __('-Choose-'),
                            ],
                            'pluginOptions' => [
                                'depends' => ['_project_executor_type'],
                                'url' => Url::to(['/ajax/get-project-members']),
                            ],
                        ]) ?>

                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'outsider')->textInput(['maxlength' => true, 'id' => 'outsider']) ?>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-2">
                        <?= $form->field($model, '_executor_type')->widget(Select2Default::classname(), [
                            'data' => EProjectExecutor::getExecutorStatusOptions(),
                            'allowClear' => false,
                            'placeholder' => false,
                            'options' => [
                                'id' => '_executor_type',
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'start_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'id' => 'start_date',
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'end_date')->widget(DatePickerDefault::classname(), [
                            'options' => [
                                'placeholder' => __('YYYY-MM-DD'),
                                'id' => 'end_date',
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ["science/project-member", 'project' => $model->_project, 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<script>


    function initMemberForm() {
        $('#_project_executor_type').change(function () {
            initMemberType();
        });
        initMemberType();
    }

    function initMemberType() {
        var id = $('#_project_executor_type').val();
        if (id === '10') {
            $("#outsider").removeAttr('disabled');
            $("#outsider").attr('required', true);

        } else {
            $("#outsider").attr('disabled', 'disabled');
            $("#outsider").attr('required', false);
            $("#_id_number").removeAttr('disabled');
            $("#_id_number").attr('required', true);
            //$("#_id_number").attr('disabled', true);
        }
    }

</script>

