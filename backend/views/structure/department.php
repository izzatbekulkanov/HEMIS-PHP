<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\structure\EDepartment;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'parent')->widget(Select2Default::class, [
                            'data' => EDepartment::getFaculties(),
                            'options' => [
                                'id' => 'department_parent_search',
                            ]
                        ])->label(false) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sticky' => '#sidebar',
                    'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, Url::current(['id' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->name, Url::current(['id' => $data->id]), []);
                            },
                        ],
                        [
                            'attribute' => 'parent',
                            'value' => 'parentDepartment.name',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box box-default ">
            <div class="box-body">
                <?= $form->field($model, 'parent')->widget(Select2Default::classname(), [
                    'data' => EDepartment::getFaculties(),
                    'allowClear' => false,
                    'options' => [
                        'onchange' => 'changeDepartmentCodeMask(this.value)'
                    ]
                ]) ?>

                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'code')->widget(MaskedInputDefault::className(), [
                    'prefix' => $university->code,
                    'mask' => '-999-99',
                ]) ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['structure/department'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['structure/department', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete'], 'structure/faculty-delete') ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <?= $this->renderFile('@backend/views/system/_hemis_sync_model.php', ['model' => $model]) ?>

    </div>
</div>
<script>
    var facultyCodes = <?=json_encode(EDepartment::getFacultiesCode())?>;
    var currentValue = '<?=$model->code?>';

    function changeDepartmentCodeMask(value, setVal = false) {
        if (facultyCodes.hasOwnProperty(value)) {
            $("#edepartment-code").inputmask({
                "clearIncomplete": false,
                "escapeChar": "|",
                "greedy": true,
                "mask": '|' + facultyCodes[value].split('').join('|') + '-99'
            });
        }
        if (setVal)
            $("#edepartment-code").val(currentValue);
    }
    <?php if($model->parent):?>
    <?php $this->registerJs("changeDepartmentCodeMask('{$model->parent}',true);")?>
    <?php endif;?>
</script>
<?php Pjax::end() ?>
