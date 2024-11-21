<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\Semester;
use common\models\curriculum\ESubjectSchedule;

use kartik\select2\Select2;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\date\DatePicker;

//$this->title = $model->name;
$this->params['breadcrumbs'][] = ['url' => $url, 'label' => __('List Schedule')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <div class="box-body no-padding">
               <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover'],
                    'columns' => [
                        [
                            'attribute' => 'position',
                            'format' => 'raw',
                            'value' => function ($data) use ($url) {
                                return $data->position;
                                //return Html::a($data->position, ['curriculum/week', 'id' => $data->_curriculum, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],
                            [
                            'attribute' => 'start_date',
                            'header' => __('Week'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDate($data->start_date).' - '.Yii::$app->formatter->asDate($data->end_date);
                                //return Html::a(Yii::$app->formatter->asDate($data->start_date).' - '.Yii::$app->formatter->asDate($data->end_date), ['curriculum/week', 'id' => $data->_curriculum, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],
                        [
                            'attribute'=>'_semester',
                            'value' => function ($data) {
                                return Semester::getByCurriculumSemester($data->_curriculum, $data->_semester)->name;
                            },
                        ],

                        [
                                'header' => __('Count lesson'),
                                'format' => 'raw',
                                'value' => function ($data) use ($group) {
                                        if($lesson = ESubjectSchedule::getWeeksByCurriculumGroup($data->id, $data->_curriculum, $data->_semester, $group->id)>0)
                                           // return Html::a(ESubjectSchedule::getWeeksByCurriculumGroup($data->id, $data->_curriculum, $data->_semester, $group->id), ['curriculum/week', 'id' => $data->_curriculum, 'code'=>$group->id], ['data-pjax' => 0]);
                                            return ESubjectSchedule::getWeeksByCurriculumGroup($data->id, $data->_curriculum, $data->_semester, $group->id);
                                        else
                                            return ' ';
                                },
                        ],
                        [
                            //'attribute' => '',
                            'header' => __('Download'),
                            'format' => 'raw',
                            'value' => function ($data) use ($group) {
                                if ($lesson = ESubjectSchedule::getWeeksByCurriculumGroup($data->id, $data->_curriculum, $data->_semester, $group->id) > 0) {
                                    return Html::a(
                                        '<i class="fa fa-download"></i>',
                                        Url::current(['id' => $data->id, 'download' => 1]),
                                        [
                                            'class' => 'btn btn-default btn-flat',
                                            'data-pjax' => 0
                                        ]);
                                }
                                else
                                    return ' ';
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}',

                            'buttons' => [
                                'delete' => function ($url, $model) use ($group) {

                                    if($lesson = ESubjectSchedule::getWeeksByCurriculumGroup($model->id, $model->_curriculum, $model->_semester, $group->id)>0) {
                                        if (Yii::$app->formatter->asDate($model->start_date, 'php:Y-m-d') > date("Y-m-d", time())) {
                                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                                'title' => __('Delete'),
                                                'data-confirm' => __('	Are you sure to delete?'),
                                                'data-pjax' => '0',
                                            ]);
                                        }
                                    }

                                }
                            ],
                            'urlCreator' => function ($action, $model, $key, $index) {
                                if ($action === 'delete') {
                                    $url = Url::current(['id' => $model->id, 'delete' => 1]);
                                    return $url;
                                }
                            }
                        ],
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                        ],
		            ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?//php $form = ActiveForm::begin(); ?>
        <?php $form = ActiveForm::begin([/*'action' => ['/curriculum/to-schedule-groups'],*/ 'enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 1,'method' => 'post']]); ?>

        <div class="box box-default ">
			<?php //echo $form->errorSummary($model)?>
            <?php echo $form->field($model, '_curriculum')->hiddenInput(['value'=>$curriculum->id, 'id'=>'_curriculum'])->label(false);?>
            <?php echo $form->field($model, '_semester')->hiddenInput(['value'=>$semester->code, 'id'=>'_semester'])->label(false);?>
            <?php echo $form->field($model, '_group')->hiddenInput(['value'=>$group->id, 'id'=>'_group'])->label(false);?>
            <?php
                $templates = array();
                foreach ($dataProvider->getModels() as $item){
                    if(ESubjectSchedule::getWeeksByCurriculumGroup($item->id, $curriculum->id, $semester->code,$group->id) > 0)
                        $templates[] = $item;
                }
            ?>
            <div class="box-body">
                <?= $form->field($model, '_week')->widget(Select2Default::classname(), [
                    'data' => ArrayHelper::map($templates, 'id', 'fullName'),
                    'allowClear' => false,
                    'options' => [
                        'id' => 'template_week',
                        'required' => true,
                    ]
                ])->label(__('Template Week')) ?>


            </div>
			<div class="box-footer text-right">
                <?= $this->getResourceLink(__('Schedule'), $url, ['class' => 'btn btn-primary btn-flat','data-pjax' => '0',]) ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Assignment'), ['class' => 'btn btn-primary btn-flat', 'id'=>'assign']) ?>
               

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
    var schedule = '<?= $url; ?>';
</script>
<?php
$script = <<< JS
	$("#assign").click(function(){
		var keys = $('#data-grid').yiiGridView('getSelectedRows');
		var _week =  $('#template_week').val();
		var _curriculum =  $('#_curriculum').val();
		var _semester =  $('#_semester').val();
		var _group =  $('#_group').val();
		$.post({
           url:   '/curriculum/to-schedule-groups',
           data: {selection: keys, week: _week,  curriculum: _curriculum, semester: _semester, group: _group},
           dataType:"json",
        });
	});
JS;
$this->registerJs($script);
?>

<?php Pjax::end() ?>

