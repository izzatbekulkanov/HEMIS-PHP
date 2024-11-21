<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\EducationType;
use common\models\curriculum\SubjectGroup;
use common\models\curriculum\ECurriculumSubject;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\sortable\Sortable;
use kartik\sortinput\SortableInput;
use yii\web\JsExpression;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/curriculum'], 'label' => __('List Curriculum')];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>


        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
					<?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_subject_group')->widget(Select2Default::classname(), [
                            'data' => ArrayHelper::map(SubjectGroup::getOptions(), 'code', 'name'),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'options' => [
                            ]
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by subject Name')])->label(false) ?>
                    </div>


                    <?php ActiveForm::end(); ?>
				</div>
			</div>
			
            <div class="box-body no-padding">
                
				<?= GridView::widget([
                    'id' => 'data-grid',
				    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table subject'],
                    'columns' => [
                        [
                            'attribute'=>'name',
                            'format'=>'raw',
                            'value'=>function($data){
                                $content = array();
                                $content[$data->id] = ['content'=>$data->getfullName()];
                                return SortableInput::widget([
                                    'name'=>'subject',
                                    'items'=>$content,
                                    'pluginEvents' => [
                                      //  'sortupdate' => 'function() { log("sortupdate"); }',
                                    ],
                                    'hideInput' => true,
                                    'sortableOptions' => [
                                        'connected'=>true,
                                    ],
                                    'options' => ['class'=>'form-control', 'readonly'=>true]
                                ]);
                           }
                        ],
		            ],
                ]); ?>

            </div>
			
        </div>
        <?php Pjax::end() ?>
    </div>
    <div class="col col-md-8">
        <div class="box box-default">
            <div class="box-body box-profile">
                <p class="text-muted text-center"><?=$model->name;?></p>
                <?php foreach($semesters as $semester): ?>
                    <?php if(@$semester_subjects[$semester->code]['total_acload'] > 0):?>
                    <?php
                        @$all_acload += @$semester_subjects[$semester->code]['total_acload'];
                        @$all_credit += @$semester_subjects[$semester->code]['credit'];
                        ?>
                    <?php endif;?>
                <?php endforeach;?>
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><?=__('All Main')?></b> <a class="pull-right"><?= @$all_acload.' / '.@$all_credit; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b><?=__('All Additional')?></b> <a class="pull-right"><?= @$additional_subjects['total_acload'].' / '.@$additional_subjects['credit']; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b><?=__('Summary')?></b> <a class="pull-right"><?= (@$all_acload+@$additional_subjects['total_acload']).' / '.(@$all_credit+@$additional_subjects['credit']); ?></a>
                    </li>
                    <li class="list-group-item">
                        <div class="row" id="data-grid-filters">
                            <div class="col col-md-9">
                            </div>
                            <div class="col col-md-3">
                                <?= $this->getResourceLink(
                                '<i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Export Subjects'),
                                ['curriculum/formation', 'id'=>$model->id, 'download' => 1],
                                ['class' => 'btn btn-flat btn-success  btn-block', 'data-pjax' => 0]
                                ) ?>
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
            <!-- /.box-body -->
        </div>
        <?php $form = ActiveForm::begin(); ?>
        <?//php $form = ActiveForm::begin(['action' => ['/curriculum/formation'], 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1,'method' => 'post']]); ?>
        <?= Html::hiddenInput('curriculum', $model->id, ['id'=>'curriculum']);?>
        <div class="box box-default ">
			<?php //echo $form->errorSummary($model)?>
            <div class="box-body">
                <?php $i=0;?>
                <div class="row">

                <?php foreach($semesters as $semester) { ?>
                    <?php $i++;?>

                        <div class="col col-md-12">
                            <?php Pjax::begin([
                                'id' => $semester->code,
                                'timeout' => false,
                            ]) ?>
                        <div class="box box-primary box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= $semester->name; ?></h3>
                                <div class="box-tools pull-right">
                                    <?php if(@$semester_subjects[$semester->code]['total_acload'] > 0) { ?>
                                        <span class="badge badge-pill"> <?= (@$semester_subjects[$semester->code]['total_acload']). '&nbsp;'. __('soat');?>/<?= (@$semester_subjects[$semester->code]['credit']).'&nbsp;'.__('kredit')?></span>
                                    <?php } ?>
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                    </button>

                                </div>

                            </div>
                            <div class="box-body" style="">

                                <?php
                                if (is_array(@$subjects[$semester->code]))
                                    $items = @$subjects[$semester->code];
                                else
                                    $items = array();
                                    echo SortableInput::widget([
                                        'name' => 'semester[' . $semester->code . ']',
                                        'items' => $items,

                                        'hideInput' => true,
                                        // 'delimiter' => '~',
                                        'sortableOptions' => [
                                            'connected' => true,
                                            'disabled'=>$model->accepted,
                                            'showHandle'=>true,
                                            'type'=>'list',
                                            'pluginEvents' => [
                                            //    'sortupdate' => 'function() { log("sortupdate"); }',
                                            ],
                                        ],
                                         'options' => ['class'=>'form-control semester-'.$semester->code, 'readonly'=>true, 'data-id'=>$semester->code,  'id'=>'semester_'.$semester->code,],
                                    ]);
                               // echo '<div class="clearfix"></div>';
                                ?>




							</div>
							
						</div>
                            <?php Pjax::end() ?>
					</div>
                    <?php /*if($i % 2 !=0){  ?>
                        <div class="clearfix">
                    <?php } else { ?>
                        </div>
                    <?php }*/ ?>
                <?php } ?>
            </div>


                <?php

                ?>
            </div>
			<div class="box-footer text-right">
                <?//= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
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


    <script>
        var base_url = '<?= \Yii::$app->request->hostInfo; ?>';
    </script>
<?php
$script = <<< JS
    $("input[class*='semester']").trigger('change');
    $("input[class*='semester']").change(function(){
        var curriculum = $("#curriculum").val();
        var semester = $(this).attr('data-id');
        var id = $(this).val();
      //  console.log(num.search('_'));
       // if (id.search('_') == -1) {
        $.ajax({
            url: '/ajax/semester-subject-edit',
            type:"POST",
            data: {curriculum: curriculum, semester: semester, id: id },
            dataType:"json",
            success: function(data) {
                /*if (data.message === 'Ok') {
                  $.pjax.reload({container:'#'+semester, timeout: '5000'});
                }*/
            },
           
          
        });
        //}
    });
JS;
$this->registerJs($script);
?>
<?php
$this->registerJs(
    "$('body').on('dragend', 'li', function(){
$('#semester_11-sortable').trigger('sortupdate'); //check the name of your ul list in the generated html
});"
);?>
