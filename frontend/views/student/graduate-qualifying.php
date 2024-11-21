<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\academic\EDecree;
use common\models\structure\EDepartment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this \backend\components\View */
/* @var $searchModel EDecree */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">

    </div>
    <?php $no = false;?>
    <div class="box-body no-padding">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="50px"></th>
                <th width="70%"><?= __('Subject') ?></th>
                <th width="10%"><?= __('Yuklama') ?></th>
                <th width="10%"><?= __('Rating / Ball') ?></th>
                <th width="10%"><?= __('Grade') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1;?>
            <?php foreach ($records as $k => $record): ?>

                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $record['name'] ?></td>
                        <td><?= $record['acload'] ? __('{hour} hour', ['hour' => $record['acload']]) : '' ?></td>

                        <td><?= $record['point'] ?></td>
                        <td><?= $record['grade'] ?></td>
                    </tr>
                    <?php $no = true; ?>

            <?php endforeach; ?>


            </tbody>
        </table>
        <br>

    </div>
    <?php if(!$no):?>
        <div class="box-body no-padding">
            <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
        </div>
    <?php endif; ?>
</div>
<?php Pjax::end() ?>
