<?php
use backend\widgets\DatePickerDefault;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\curriculum\EducationYear;
use common\models\performance\EPerformance;
use backend\widgets\Select2Default;
use common\models\system\classifier\FinalExamType;
use common\models\curriculum\ESubjectExamSchedule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;
use common\models\science\EPublicationAuthorMeta;
/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-9">

                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Publication Name / Author')])->label(false) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                    <div class="col col-md-3 pull-right">
                        <div class="form-group pull-right">
                            <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Add Selected'), ['class' => 'btn btn-primary btn-flat', 'id'=>'assign']) ?>
                        </div>
                    </div>




                </div>
            </div>
            <div class="box-body no-padding">
                <?php $colors = ['bg-gray'];?>
                <?//php if(Yii::$app->request->post()) : ?>
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sticky' => '#sidebar',
                    'dataProvider' => $dataProvider,
                    'rowOptions' => function($model, $key, $index, $column) use ($list, $colors) {
                        $bool = in_array($model->id, $list);
                        $result = [];
                        if($bool) {
                            $result['style'] = 'background-color:#E5E4E2';
                        }
                        else {
                            $result = [];
                        }
                        return $result;
                    },

                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function($model, $key, $index, $column) use ($list) {
                                $bool = in_array($model->id, $list);
                                if($bool) {
                                    $onclick = "return false;";
                                    $display = "none";
                                }
                                else {
                                    $onclick = "return true;";
                                    $display = "block";
                                }
                                return ['style' => ['display' => $display]];
                                //  return ['checked' => $bool, 'disabled'=>$bool,  'style' => ['display' => $display] ];
                                //return ['style' => ['display' => 'none']];
                            }

                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            /*'value' => function ($data) {
                                return Html::a($data->name, ['science/publication-methodical-edit', 'id' => $data->id], ['data-pjax' => 0]);
                            },*/
                        ],
                        [
                            'attribute' => 'authors',
                        ],
                        [
                            'attribute' => 'issue_year',
                        ],
                        [
                            'attribute' => '_scientific_publication_type',
                            'value' => 'scientificPublicationType.name',
                        ],
                        [
                            'attribute' => '_employee',
                            'value' => 'employee.fullName',
                        ],
                        [
                            'attribute' => 'filename',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data->filename) {
                                    return Html::a(@$data->filename['name'], @$data->filename['base_url'] . '/' . @$data->filename['path'], ['target'=>'_blank', 'data-pjax'=>0]);
                                }
                            },
                        ],

                    ],
                ]); ?>
                <?//php endif;?>

            </div>
        </div>
    </div>



</div>

<script>
    var publication_type = '<?= EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC; ?>';
</script>
<?php
$script = <<< JS
	$("#assign").click(function(){
		var keys = $('#data-grid').yiiGridView('getSelectedRows');
		$.post({
           url:  '/science/to-publication',
           data: {selection: keys,  publication_type: publication_type},
           dataType:"json",
        });
	});
JS;
$this->registerJs($script);
?>

<?php Pjax::end() ?>
