<?php

use backend\widgets\checkbo\CheckBo;
use common\models\curriculum\EExamStudentMetaResult;
use yii\widgets\Pjax;

/**
 * @var $model \common\models\curriculum\EExam
 */
?>
<div style="margin:0 -15px -25px;position: relative">
    <?php Pjax::begin(['id' => 'test-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <?= \backend\widgets\GridView::widget([
        'id' => 'data-grid',
        'mobile' => true,
        'layout' => '{items}<div class="box-footer">
        {summary}
        <div class="row">
        <div class="col-sm-8">{pager}</div>
        <div class="col-sm-4 text-right">
        <button style="margin:15px" type="button" class="btn btn-flat btn-default" data-dismiss="modal">' . __('Close') . '</button>
        
        </div></div></div>
        
        ',
        'dataProvider' => $dataProvider,
        'emptyText' => __('Ma\'lumotlar mavjud emas'),
        'columns' => [
            [
                'format' => 'raw',
                'header' => CheckBo::widget(
                    [
                        'type' => 'switch',
                        'options' => [
                            'onclick' => 'toggleAllRows(this)',
                        ],
                        'name' => 'sd',
                        'value' => 1,
                    ]
                ),
                'value' => function (EExamStudentMetaResult $data, $index, $i) {
                    $link = currentTo(['exclude' => $data->_student]);
                    return CheckBo::widget(
                        [
                            'type' => 'switch',
                            'labelClass' => 'switch switch-xs switch-row',
                            'options' => [
                                'onclick' => "$.get('$link')",
                                'data-id' => $data->_student,
                            ],
                            'name' => 'toggle_student',
                            'value' => !$data->excluded,
                        ]
                    );
                }
            ],
            [
                'attribute' => '_student',
                'format' => 'raw',
                'value' => function (EExamStudentMetaResult $data, $index, $i) {
                    return $data->student->getFullName();
                },
            ],
            [
                'attribute' => 'attempts',
                'format' => 'raw',
                'value' => function (EExamStudentMetaResult $data, $index, $i) {
                    return $data->attempts ?: '';
                },
            ],
            [
                'attribute' => 'correct',
                'format' => 'raw',
                'value' => function (EExamStudentMetaResult $data, $index, $i) {
                    return $data->correct ?: '';
                },
            ],
            [
                'attribute' => 'percent',
                'format' => 'raw',
                'value' => function (EExamStudentMetaResult $data, $index, $i) {
                    return $data->attempts ? round($data->percent, 1) . '%' : '';
                },
            ],
            [
                'attribute' => 'finished_at',
                'format' => 'raw',
                'value' => function (EExamStudentMetaResult $data) {
                    return $data->finished_at ?: '';
                },
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    <p class="text-right">

    </p>
</div>
<script>
    function toggleAllRows(element) {
        var checked = $(element).is(':checked');
        $('.switch-row input').prop('checked', checked);
        var items = [];
        $('.switch-row input').each(function () {
            items.push($(this).data('id'))
        });
        $.post('<?=currentTo(['batch' => 1])?>', {items: items, state: checked ? 1 : 0});
    }
</script>

