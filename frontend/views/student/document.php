<?php
$this->title = __('Student Document');
?>
<div class="row">
    <?php foreach ($documents as $id => $document): ?>
        <div class="col col-md-4 col-lg-3">
            <div class="box box-default ">
                <div class="box-header">
                    <h3 class="box-title"> <?= $document['name'] ?></h3>
                </div>
                <table class="table table-striped">
                    <?php foreach ($document['attributes'] as $item): ?>
                        <tr>
                            <th><?= $item['label'] ?></th>
                            <td><?= $item['value'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="box-footer text-right">
                    <?= \yii\helpers\Html::a(__('Download'), currentTo(['id' => $id]), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


