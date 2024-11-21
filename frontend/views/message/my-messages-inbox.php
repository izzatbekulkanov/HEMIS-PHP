<?php

use backend\widgets\GridView;
use backend\widgets\MailGridView;
use common\models\system\AdminMessageItem;
use yii\bootstrap\Html;

?>

<?= MailGridView::widget([
    'dataProvider' => $dataProvider,
    'emptyText' => __('Kiruvchi xabarlar yo\'q'),
    'id' => 'data-grid',
    'columns' => [
        [
            'attribute' => '_sender',
            'format' => 'raw',
            'value' => function (AdminMessageItem $data) use ($folder) {
                return Html::a($data->message->getSenderInformation(), ['message/my-messages', 'id' => $data->id, 'folder' => $folder], ['data-pjax' => 0]);
            },
            'contentOptions' => [
                'width' => '25%',
            ],
        ],
        [
            'attribute' => 'title',
            'format' => 'raw',
            'value' => function (AdminMessageItem $data) use ($folder) {
                return Html::a($data->message->getShortTitle(), ['message/my-messages', 'id' => $data->id, 'folder' => $folder], ['data-pjax' => 0]) . '<br><div class="msg-content text-muted">' . \yii\helpers\StringHelper::truncateWords(strip_tags($data->message->message), 10) . '</div>';
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
