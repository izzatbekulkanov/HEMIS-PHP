<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\curriculum\ESubjectScheduleTeacherMap */
/* @var $dataProviderReport yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
$weeks = $searchModel->getWeekOptions();
$weekValues = array_keys($weeks);
$index = array_search($searchModel->week, $weekValues);
$nextVal = false;
$prevVal = false;
if ($index !== false) {
    if (isset($weekValues[$index + 1])) {
        $nextVal = $weekValues[$index + 1];
    }
    if (isset($weekValues[$index - 1])) {
        $prevVal = $weekValues[$index - 1];
    }
}
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row" id="data-grid-filters">
            <div class="col col-md-4">
                <div class="row">
                    <div class="col-xs-2" style="padding-right: 1px">
                        <div class="form-group">
                            <?= \yii\helpers\Html::button('<i class="fa fa-chevron-left"></i>', [
                                'class' => 'btn btn-default btn-block btn-flat',
                                'onclick' => $prevVal ? new \yii\web\JsExpression("$('#esubjectscheduleteachermap-week').val('$prevVal').trigger('change')") : ''
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-xs-8" style="padding-right: 0;padding-left: 0;text-align: center">
                        <?= $form->field($searchModel, 'week')->widget(Select2Default::classname(), [
                            'data' => $weeks,
                            'hideSearch' => false,
                            'allowClear' => false,
                        ])->label(false); ?>
                    </div>
                    <div class="col-xs-2" style="padding-left: 1px">
                        <div class="form-group">
                            <?= \yii\helpers\Html::button('<i class="fa fa-chevron-right"></i>', [
                                'class' => 'btn btn-default btn-block btn-flat',
                                'onclick' => $nextVal ? new \yii\web\JsExpression("$('#esubjectscheduleteachermap-week').val('$nextVal').trigger('change')") : ''
                            ]) ?>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), ['data' => $searchModel->getFacultyOptions(),
                    'hideSearch' => false,
                    'allowClear' => true,
                ])->label(false); ?>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_cathedra')->widget(Select2Default::classname(), ['data' => $searchModel->getCathedraOptions(),
                    'hideSearch' => false,
                    'allowClear' => true,
                    'placeholder' => __('Choose Department'),
                ])->label(false); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="hidden">
        <?= GridView::widget(['id' => 'data-grid',
            'dataProvider' => new \yii\data\ArrayDataProvider(['models' => []]),
            'columns' => [['class' => 'yii\grid\SerialColumn'],],]); ?>
    </div>
    <?php if (isset($data['days'])): ?>
        <div style="overflow-y: auto">
            <table class="table table-striped table-hover table-bordered map-table">
                <thead>
                <tr>
                    <th class="text-center" rowspan="2" colspan="2"><?= __('Employee') ?></th>
                    <?php foreach ($data['days'] as $day): ?>
                        <th class="text-center"
                            colspan="<?= count($data['pairs']) ?>"><?= str_replace(',', '<br>', $day['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($data['days'] as $day): ?>
                        <?php foreach ($data['pairs'] as $pair): ?>
                            <th class="text-center"><?= $pair['label'] ?></th>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php $i = 0;
                foreach ($data['employees'] as $room): $i++ ?>
                    <tr class="text-left">
                        <td width="50"><?= $i ?></td>
                        <td><?= $room['employee']->getShortName() ?></td>
                        <?php foreach ($room['days'] as $date => $day): ?>
                            <?php foreach ($day['pairs'] as $pair): ?>
                                <td class="cell" style="background: <?= $pair['count'] ? '#ff9696' : '' ?>">
                                    <?php if ($pair['count']): ?>
                                        <a class="showModalButton"
                                           title="<?= $room['employee']->getShortName() ?>"
                                           value="<?= currentTo(['employee' => $room['employee']->id, 'date' => $date]) ?>"
                                           modal-class="modal-md">
                                            <?= $pair['count'] ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php Pjax::end() ?>
