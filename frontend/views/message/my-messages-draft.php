<?php

use backend\widgets\GridView;
use backend\widgets\MailGridView;
use common\models\system\AdminMessageItem;
use yii\bootstrap\Html;

?>

<?= MailGridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'data-grid',
    'emptyText' => __('Qoralama xabarlar yo\'q'),
    'columns' => [
        [
            'attribute' => '_sender',
            'format' => 'raw',
            'value' => function (AdminMessageItem $data) use ($folder) {
                return Html::a($data->message->getRecipientInformation(1), ['message/compose', 'id' => $data->_message, 'folder' => $folder], ['data-pjax' => 0]);
            },
            'contentOptions' => [
                'width' => '25%',
            ],
        ],
        [
            'attribute' => 'title',
            'format' => 'raw',
            'value' => function (AdminMessageItem $data) use ($folder) {
                return Html::a($data->message->getShortTitle(), ['message/compose', 'id' => $data->_message, 'folder' => $folder], ['data-pjax' => 0]) . '<br><div class="msg-content text-muted">' . \yii\helpers\StringHelper::truncateWords(strip_tags($data->message->message), 10) . '</div>';
            },
            'contentOptions' => [
                'width' => '60%',
            ],
        ],
        [
            'attribute' => 'send_on',
            'format' => 'raw',
            'value' => function (AdminMessageItem $data) {
                return $data->getTimeFormatted();
            },
            'contentOptions' => [
                'width' => '15%',
            ],
        ],
    ],
]); ?>
