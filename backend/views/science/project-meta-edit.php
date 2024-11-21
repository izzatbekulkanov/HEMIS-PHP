<?php

use backend\widgets\DatePickerDefault;
use common\components\Config;
use common\models\science\EProjectMeta;
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
/* @var $project \common\models\science\EProject */

$this->title = __('Project Finance Information');
$this->params['breadcrumbs'][] = ['url' => ["science/project"], 'label' => __('Science Project')];
$this->params['breadcrumbs'][] = ['url' => ["science/project", 'id' => $project->id], 'label' => $project->getShortTitle()];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

    <div class="row">
        <div class="col col-md-8">
            <div class="box box-default ">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'fiscal_year')->widget(Select2Default::classname(), [
                                'data' => EProjectMeta::getYearOptions(),
                                'allowClear' => false,
                                'hideSearch' => false,
                                'options' => [

                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'budget')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'quantity_members')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?php if (!$model->isNewRecord): ?>
                        <?= $this->getResourceLink(__('Delete'), ["science/project-meta", 'project' => $model->_project, 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>