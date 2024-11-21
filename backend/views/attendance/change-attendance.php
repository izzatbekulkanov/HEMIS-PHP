<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\filekit\Upload;
use backend\widgets\GridView;
use backend\widgets\UploadDefault;
use common\models\attendance\EAttendanceActivity;
use common\models\attendance\EAttendance;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\date\DatePickerAsset;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

//$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => $this->title];
//$this->params['breadcrumbs'][] = $subject->curriculum->name;
//$this->params['breadcrumbs'][] = $subject->semester->name;
//$this->params['breadcrumbs'][] = $subject->subject->name;

?>
<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>

        <div class="box-body">
            <?= GridView::widget([
                'id' => 'data-grid',
                'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                'dataProvider' => (new EAttendanceActivity())->searchForAttendance($model),
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'reason',
                        'format' => 'raw',
                        'contentOptions' => [
                            'class' => 'nowrap'
                        ],
                    ],
                    [
                        'attribute' => 'absent_on',
                        'value' => function ($data) {
                            return $data->absent_on > 0 ? $data->absent_on : '';
                        },
                    ],
                    [
                        'attribute' => 'absent_off',
                        'value' => function ($data) {
                            return $data->absent_off > 0 ? $data->absent_off : '';
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                        },
                    ],
                    [
                        'attribute' => 'file',
                        'header' => __('Asoslovchi fayl'),
                        'format' => 'raw',
                        'value' => function ($data) {
                            return $data->file ? Html::a(__('Yuklab olish'), currentTo(['download' => $data->id]), ['data-pjax' => 0]) : '';
                        },
                    ],
                ],
            ]); ?>
        </div>

        <div class='box-body'>
            <div class="row">
                <div class="col col-md-12">
                    <? /*<strong> <?= $model->absent_on==2 ? '<i class="fa fa-calendar-check-o margin-r-5"></i>'.__('Sababli') : '<i class="fa fa-calendar-minus-o margin-r-5"></i>'.__('Sababsiz'); ?></strong>*/ ?>
                    <?php $activity->status = $model->absent_on == 2 ? '11' : '12'; ?>
                    <?= $form->field($activity, 'status')->radioList(EAttendance::getValueOptions(), ['class' => 'custom-control custom-radio custom-control-inline']); ?>
                    <?= $form->field($activity, 'reason')->textarea(['maxlength' => true])->label() ?>
                    <?= $form->field($activity, 'file')
                        ->widget(UploadDefault::className(), [
                            'url' => ['dashboard/file-upload', 'type' => 'ad'],
                            'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png|pdf)$/i'),
                            'sortable' => true,
                            'accept' => 'application/pdf,image/*',
                            'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                            'maxNumberOfFiles' => 1,
                            'multiple' => false,
                            'useCaption' => false,
                            'clientOptions' => [],
                        ])->label(__('Asoslovchi fayl')); ?>
                </div>
            </div>
        </div>

        <div class='box-footer text-right'>

            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>

        </div>
        <?php ActiveForm::end(); ?>

    </div>

<?php Pjax::end() ?>