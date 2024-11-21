<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\structure\EDepartment;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Curriculum');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-2">
                <div class="form-group">
                    <?= $this->getResourceLink(
                        '<i class="fa fa-plus-circle"></i> ' . __('Create Curriculum'),
                        ['curriculum/curriculum-edit'],
                        ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                    ) ?>

                </div>
            </div>
            <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN) { ?>
                <div class="col col-md-4">
                    <?= $form->field($searchModel, '_department')->widget(Select2Default::class, [
                        'data' => EDepartment::getFaculties(),
                        'placeholder' => __('-Choose faculty-')
                    ])->label(false) ?>
                </div>
            <?php } ?>
            <div class="col col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by curriculum Name / Code')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(sprintf('%s <p class="text-muted">%s</p>', $data->name, $data->specialty->name), ['curriculum/curriculum-edit', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => '_department',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf('%s <p class="text-muted">%s / %s</p>', $data->department->name, $data->educationType->name, $data->markingSystem->name);
                },
            ],
            [
                'header' => 'Actions',
                'format' => 'raw',
                'value' => function ($data) {
                    //return Html::a('<i class="fa fa-calendar"></i> '. __('Semester'), ['curriculum/semester', '_csrf-backend'=>Yii::$app->request->csrfToken.$data->id, 'Semester[_curriculum]' => $data->id, '_pjax' => '#admin-grid'], ['data-pjax' => 0, 'class'=>'label label-success'])
                    return Html::a('<i class="fa fa-calendar"></i> ' . __('Semester'), ['curriculum/semester', 'id' => $data->id], ['data-pjax' => 0, 'class' => 'label label-success'])
                        . ' &nbsp;&nbsp; ' . Html::a('<i class="fa fa-hourglass-2"></i> ' . __('Week'), ['curriculum/week', 'id' => $data->id], ['data-pjax' => 0, 'class' => 'label label-success'])
                        . ' &nbsp;&nbsp; ' . Html::a('<i class="fa fa-tag"></i> ' . __('Subject Block'), ['curriculum/curriculum-block', 'id' => $data->id], ['data-pjax' => 0, 'class' => 'label label-success'])
                        . ' &nbsp;&nbsp; ' . Html::a('<i class="fa fa-book"></i> ' . __('Subject'), ['curriculum/formation', 'id' => $data->id], ['data-pjax' => 0, 'class' => 'label label-success'])
                        . ' &nbsp;&nbsp; ' . Html::a('<i class="fa fa-tags"></i> ' . __('Subject Topic'), ['curriculum/subject-topic', 'id' => $data->id], ['data-pjax' => 0, 'class' => 'label label-success']);
                },

            ],
            /*[
                'header' => 'Week',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->semesterStatus($data->id) == true)
                            return Html::a('<i class="fa fa-table"></i> '. __('Week'), ['curriculum/week', 'id' => $data->id], ['data-pjax' => 0]);
                       else
                            return '<i class="fa fa-table"></i> '. __('Week');


                },
            ],
            [
                'header' => 'Topic',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('<i class="fa fa-table"></i> '. __('Subject Topic'), ['curriculum/subject-topic', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],*/
            [
                'attribute' => 'accepted',
                'format' => 'raw',
                'value' => function ($data) {
                    return CheckBo::widget([
                        'type' => 'switch',
                        'options' => [
                            'onclick' => "changeAttribute('$data->id', 'accepted')",
                        ],
                        'name' => $data->id,
                        'value' => $data->accepted
                    ]);
                },
            ],
            /*[
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                },
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                },
            ],*/
            /*s[
                'attribute' => 'active',
                'format' => 'raw',
                'value' => function ($data) {
                    return CheckBo::widget([
                        'type' => 'switch',
                        'options' => [
                            'onclick' => "changeAttribute('$data->id', 'active')",
                        ],
                        'name' => $data->id,
                        'value' => $data->active
                    ]);
                },
            ],*/
        ],
    ]); ?>
</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['curriculum/curriculum'])?>', data, function (resp) {

        })
    }
</script>
<?php Pjax::end() ?>
