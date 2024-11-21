<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var $model \common\models\structure\EUniversity
 */

$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();

?>
<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <div class="col col-md-4">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-edit"></i> ' . __('Change Information'),
                        ['structure/university-update'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="box-body no-padding">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'code',
                'name',
                [
                    'attribute' => '_soato',
                    'value' => $model->soato ? $model->soato->name : '',
                ],
                'address',
                'tin',
                'contact',
                [
                    'attribute' => '_ownership',
                    'value' => $model->ownership ? $model->ownership->name : '',
                ],
                [
                    'attribute' => '_university_form',
                    'value' => $model->universityForm ? $model->universityForm->name : '',
                ],
                'mailing_address',
                'bank_details',
                'accreditation_info'
            ],
        ]) ?>
    </div>
</div>
