<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\science\ECriteriaTemplate;
use common\models\science\EPublicationAuthorMeta;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use kartik\depdrop\DepDrop;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use common\models\system\AdminRole;

$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-body no-padding">
                    <?= GridView::widget([
                        'id' => 'data-grid',
                        //'layout' => '<div class=\'box-body no-padding\'>{items}</div>',
                        'dataProvider' => $dataProvider,
                        'showFooter' => true,
                        'footerRowOptions'=>['style'=>'font-weight:bold;',],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'work_type',
                                'value' => function ($data) {
                                    return ECriteriaTemplate::getPublicationTypeOptions()[$data['work_type']];
                                },
                                'label' => __('Publication'),
                                'footer' => __('Summary'),
                            ],
                            [
                                'attribute' => 'name',
                                'label' => __('Publication Type'),
                               // 'footer' => __('Summary'),
                            ],
                            [
                                'attribute' => 'work_name',
                                'label' => __('Work Name'),
                            ],
                            [
                                'attribute' => 'mark',
                                'label' => __('Mark'),
                                'footer' => EPublicationAuthorMeta::getTotal($dataProvider->models, 'mark'),

                            ],
                        ],
                    ]); ?>




            </div>
        </div>
    </div>



</div>

<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>

<?php Pjax::end() ?>
