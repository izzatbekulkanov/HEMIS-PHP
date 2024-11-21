<?php

use backend\widgets\GridView;
use common\models\system\classifier\SubjectGroup;
use yii\widgets\Pjax;


//$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => $this->title];
//$this->params['breadcrumbs'][] = $subject->curriculum->name;
//$this->params['breadcrumbs'][] = $subject->semester->name;
//$this->params['breadcrumbs'][] = $subject->subject->name;

?>
    <?php Pjax::begin(['id' => 'resources-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class='box-body no-padding'>
        <div>
            <?= GridView::widget([
                'id' => 'data-grid',
                //'layout' => '{items}',
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => '_subject',
                        'header' => __('Subject'),
                        'value' => 'subject.name',
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div class='box-footer '>
        <div class="col-md-8">
        </div>
        <div class="col-md-4  text-right">
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>
        </div>
    </div>

    <?php Pjax::end() ?>


