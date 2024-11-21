<?php

use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(['id' => 'backup-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="box box-default box-solid">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'data-grid',
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data['name'], ['/system/backup', 'id' => $data['name']], ['data-pjax' => 0]);
                },
            ],

            [
                'attribute' => 'size',
            ],
            [
                'attribute' => 'time',
            ],
            [
                'attribute' => 'action',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(__('Remove'), ['/system/backup', 'rem' => $data['name']], ['class' => 'btn-delete']);
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
