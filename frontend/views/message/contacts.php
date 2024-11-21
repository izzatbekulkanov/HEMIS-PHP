<?php
/**
 * @var $searchModel \common\models\system\Contact
 * @var $this \backend\components\View
 */

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use backend\widgets\SelectizeDefault;
use common\models\OptionProvider;
use common\models\system\Contact;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

$type = Yii::$app->request->get('type');
$attribute = '_department';
$options = OptionProvider::getDepartmentOptions();
$dataProvider = $searchModel->searchForAdmin(Yii::$app->request->get(), $this->_user());

?>
<div style="margin: -15px -15px -35px">
    <?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row">
        <div class="col col-md-12 col-lg-12">
            <div class="box no-border ">
                <div class="box-header ">
                    <div class="row" id="data-grid-items-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-sm-6">
                            <?= $form->field($searchModel, $attribute)->widget(Select2Default::classname(), [
                                'data' => $options,
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false) ?>
                        </div>
                        <div class="col col-sm-6">
                            <?= $form->field($searchModel, 'search',
                                [
                                    'labelOptions' => ['class' => 'invisible'],
                                ]
                            )->textInput(['placeholder' => __('Search by Name / Department')])->label(false) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <?= GridView::widget([
                    'id' => 'data-grid-items',
                    'dataProvider' => $dataProvider,
                    'emptyText' => __('Kontaktlar mavjud emas'),
                    'layout' => $this->render('@frontend/views/message/_contact_layout.php'),
                    'columns' => [
                        [
                            'class' => 'yii\grid\RadioButtonColumn',
                            'radioOptions' => function ($model, $key, $index, $grid) {
                                return [
                                    'data-text' => $model->name,
                                    'class' => 'item',
                                    'data-value' => $model->id,
                                ];
                            }
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function (Contact $data) {
                                return $data->name;
                            },
                        ],
                        [
                            'attribute' => 'department',
                            'format' => 'raw',
                            'value' => function (Contact $data) {
                                return $data->label;
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <?php Pjax::end() ?>
</div>
<script type="text/javascript">
    function addSelected() {
        var selected = [];
        var options = [];
        $('#data-grid-items input.item[type="radio"]:checked').each(function (index, element) {
            var e = $(element);
            selected.push(e.data('value'));
            options.push({'id': e.data('value'), 'name': $(element).data('text')});
        })

        if (selected.length) {
            var selectize = $('#adminmessage-_recipients').data('selectize');
            var val = selected.join(',');
            selectize.addOption(options);
            selectize.setValue(val.split(','));
            $('#adminmessage-_recipients').val(val);
            $('#modal').modal('hide');
        }
    }
</script>
