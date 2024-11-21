<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\system\AdminResource;
use common\models\curriculum\EducationYear;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\ExamFinish;
use common\models\curriculum\ECurriculumSubjectBlock;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\RatingGrade;
use common\models\system\classifier\SubjectType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\GradeType;
use common\models\structure\EDepartment;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
$this->title = $model->subject->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/curriculum'], 'label' => __('List Curriculum')];
$this->params['breadcrumbs'][] = ['url' => ['curriculum/formation', 'id'=>$model->_curriculum], 'label' => $model->curriculum->name];
$this->params['breadcrumbs'][] = $this->title;
?>

<?//php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'action' => linkTo(['curriculum/curriculum-subject-edit', 'id' => $model->id])/* 'validateOnSubmit' => true, 'options' => ['data-pjax' => true]*/]); ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false,  'validateOnSubmit' => true, 'options' => ['data-pjax' => true]]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-info">
            <div class="box-body">
                <?php echo $form->field($model, 'count_of_weeks')->hiddenInput(['value'=>$curriculum_weeks, 'id'=>'count_of_weeks'])->label(false);?>
                <?php echo $form->field($model, 'marking_system')->hiddenInput(['value'=>$model->curriculum->_marking_system, 'id'=>'marking_system'])->label(false);?>
                <?php
                    if($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE){
                        $min_border = 0;
                        //$min_border = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_THREE)->min_border,0);
                        $max_border = round(GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border, 0);
                    }
                    else{
                        $max_border = GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE)->max_border;
                        $min_border = 0;
                    }
                ?>
                <div class="row">
                    <div class="col col-md-12">
                       <div class="row">
                           <div class="col col-md-4">
                               <?= $form->field($model, '_department')->widget(Select2Default::class, [
                                   'data' => EDepartment::getDepartments(),
                                   'hideSearch' => false,
                                   'options' => [
                                   ]
                               ]); ?>
                           </div>
                           <div class="col col-md-4">
                               <?= $form->field($model, '_curriculum_subject_block')->widget(Select2Default::class, [
                                   'data' => ArrayHelper::map(ECurriculumSubjectBlock::getBlockByCurriculum($model->_curriculum), 'subjectBlock.code', 'subjectBlock.name'),
                                   'hideSearch' => false,
                                   'options' => [
                                   ]
                               ]); ?>
                            </div>
                           <div class="col col-md-4">
                               <?= $form->field($model, '_subject_type')->widget(Select2Default::class, [
                                   'data' => SubjectType::getClassifierOptions(),
                                   'hideSearch' => false,
                               ]); ?>
                           </div>
                       </div>

                       <div class="row">
                           <div class="col col-md-4">
                               <?= $form->field($model, '_rating_grade')->widget(Select2Default::class, [
                                   'data' => RatingGrade::getOptions(),
                                   'disabled' => count(ECurriculumSubjectExamType::getExamTypeOtherByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject))>0,
                                   'hideSearch' => false,
                               ]); ?>
                           </div>
                           <div class="col col-md-4">
                               <?= $form->field($model, 'credit')->textInput(['maxlength' => true, 'id' => 'credit']) ?>
                           </div>

                           <div class="col col-md-4">
                               <?= $form->field($model, 'total_acload')->textInput(['maxlength' => true, 'readonly'=>true, 'id' => 'total_acload']) ?>
                           </div>

                       </div>

                       <div class="row">
                           <div class="col-md-4">
                               <?= $form->field($model, 'active')->widget(Select2Default::classname(), [
                                   'data' => ['1' => __('Yes'), '0' => __('No')],
                                   'allowClear' => false,
                                   'placeholder' => false,
                                   'options' => [
                                       'value' => $model->active ? 1 : 0,
                                   ],
                               ]) ?>
                           </div>
                           <div class="col col-md-4">
                               <?= $form->field($model, '_exam_finish')->radioList(ArrayHelper::map(ExamFinish::find()->where(['active'=>true])->all(), 'code', 'name'),['class'=>'custom-control custom-radio custom-control-inline']); ?>
                           </div>

                           <div class="col-md-2 check">
                               <br/>
                               <?php echo CheckBo::widget([
                                   'name'      => "ECurriculumSubject[at_semester]",
                                   'attribute' => __('at_semester'),
                                   'value'     => $model["at_semester"],
                                   'type'  => CheckBo::TYPE_CHECKBOX,
                               ]); ?>
                           </div>
                           <?//php if($model->total_acload >0) {?>
                               <div class="col-md-2 check">
                                   <br/>
                                   <?php echo CheckBo::widget([
                                       'name'      => "ECurriculumSubject[reorder]",
                                       'attribute' => __('reorder'),
                                       'value'     => $model["reorder"],
                                       'type'  => CheckBo::TYPE_CHECKBOX,
                                   ]); ?>
                               </div>
                           <?//php } ?>


                       </div>

                    </div>
                </div>
            </div>
        </div>

        <?php if($model->credit !== null):?>
        <div class="box box-default">
            <div class="box-header bg-gray ">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-6">
                        <h4><?= __('Time distribution');?>
                        </h4>
                    </div>
                    <div class="col col-md-6">
                        <div class="pull-right">
                            <?php if(!$model->curriculum->accepted && $model->credit !== null) { ?>
                                <?=
                                Html::a(__('Add Time Distribution'), '#', [
                                    'class' => 'showModalButton btn btn-flat btn-success',
                                    'modal-class' => 'modal-md',
                                    'title' => __('Add Time Distribution').' / '.$model->subject->name,
                                    'value' => Url::to(['curriculum/curriculum-subject-edit',
                                        'id' => $model->id,
                                        'detail' => 'detail',
                                        'edit' => 1,
                                    ]),
                                    'data-pjax' => 0
                                ]);
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12 col-lg-12">
                        <div class="box box-default ">
                            <div class="box-body no-padding">
                                <?//php $_curriculum = $meta->_curriculum;?>
                                <?php Pjax::begin(['id' => 'notes', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
                                <?= GridView::widget([
                                    'id' => 'data-grid',
                                    //     'toggleAttribute' => 'active',

                                    'dataProvider' => $dataProviderDetail,
                                    'columns' => [
                                        [
                                            'class' => 'yii\grid\SerialColumn',
                                            'headerOptions' => ['style' => 'max-width:10px;'],
                                        ],
                                        [
                                            'attribute' => '_training_type',
                                            'format' => 'raw',
                                            'headerOptions' => ['style' => 'width:50%;white-space: normal;'],
                                            'value' => function ($data) use ($model) {
                                                 if(!$model->curriculum->accepted) {
                                                     return Html::a($data->trainingType->name, '#', [
                                                         'class' => 'showModalButton ',
                                                         'modal-class' => 'modal-md',
                                                         'title' => $data->trainingType->name,
                                                         'value' => Url::to(['curriculum/curriculum-subject-edit',
                                                             'id' => $model->id,
                                                             'detail' => $data->id,
                                                             'edit' => 1,
                                                         ]),
                                                         'data-pjax' => 0
                                                     ]);
                                                 }
                                                 else{
                                                     return $data->trainingType->name;
                                                 }

                                            },

                                        ],
                                        [
                                            'attribute' => 'academic_load',
                                            'format' => 'raw',
                                            'headerOptions' => ['style' => 'width:45%;white-space: normal;'],
                                            'value' => function ($data)  {
                                                return sprintf("%s<p class='text-muted'> </p>", $data->academic_load);
                                            },
                                        ],
                                    ],
                                ]); ?>
                                <?php Pjax::end() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif;?>
        <?php if($model->credit !== null):?>
        <div class="box box-default">
            <div class="box-header bg-gray ">
                <div class="row" id="data-grid-filters">
                    <div class="col col-md-6">
                        <h4><?= __('Rating distribution');?>
                            (<?= $model->curriculum->markingSystem ? $model->curriculum->markingSystem->name : ''; ;?>)
                        </h4>
                    </div>
                    <div class="col col-md-6">
                        <div class="pull-right">
                            <?php if(!$model->curriculum->accepted && $model->credit !== null) { ?>
                                <?=
                                Html::a(__('Add Rating Distribution'), '#', [
                                    'class' => 'showModalButton btn btn-flat btn-success',
                                    'modal-class' => 'modal-md',
                                    'title' => __('Add Rating Distribution').' / '.$model->subject->name,
                                    'value' => Url::to(['curriculum/curriculum-subject-edit',
                                        'id' => $model->id,
                                        'exam' => 'exam',
                                        'edit' => 1,
                                    ]),
                                    'data-pjax' => 0
                                ]);
                                ?>
                            <?php } ?>
                        </div>
                    </div>


                </div>

            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12 col-lg-12">
                        <div class="box box-default ">
                            <div class="box-body no-padding">
                                <?//php $_curriculum = $meta->_curriculum;?>
                                <?php Pjax::begin(['id' => 'exams', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

                                <?= GridView::widget([
                                    'id' => 'data-grid',
                                    //     'toggleAttribute' => 'active',

                                    'dataProvider' => $dataProviderExam,
                                    'columns' => [
                                        [
                                            'class' => 'yii\grid\SerialColumn',
                                            'headerOptions' => ['style' => 'max-width:10px;'],
                                        ],
                                        [
                                            'attribute' => '_exam_type',
                                            'format' => 'raw',
                                            'headerOptions' => ['style' => 'width:50%; white-space: normal;'],
                                            'value' => function ($data) use ($model) {
                                                if(!$model->curriculum->accepted) {
                                                    if($model->_rating_grade != RatingGrade::RATING_GRADE_SUBJECT){
                                                        return Html::a($data->examType->name, '#', [
                                                            'class' => 'showModalButton ',
                                                            'modal-class' => 'modal-md',
                                                            'title' => $data->examType->name,
                                                            'value' => Url::to(['curriculum/curriculum-subject-edit',
                                                                'id' => $model->id,
                                                                'exam' => $data->id,
                                                                'edit' => 1,
                                                            ]),
                                                            'data-pjax' => 0
                                                        ]);
                                                    }
                                                    if($data->_exam_type != ExamType::EXAM_TYPE_OVERALL){
                                                        return Html::a($data->examType->name, '#', [
                                                            'class' => 'showModalButton ',
                                                            'modal-class' => 'modal-md',
                                                            'title' => $data->examType->name,
                                                            'value' => Url::to(['curriculum/curriculum-subject-edit',
                                                                'id' => $model->id,
                                                                'exam' => $data->id,
                                                                'edit' => 1,
                                                            ]),
                                                            'data-pjax' => 0
                                                        ]);
                                                    }
                                                    else{
                                                        return $data->examType->name;
                                                    }
                                                }
                                                else{
                                                    return $data->examType->name;
                                                }
                                            },
                                        ],
                                        [
                                            'attribute' => 'max_ball',
                                            'format' => 'raw',
                                            'headerOptions' => ['style' => 'width:45%;white-space: normal;'],
                                            'value' => function ($data)  {
                                                return sprintf("%s<p class='text-muted'> </p>", $data->max_ball);
                                            },
                                        ],
                                    ],
                                ]); ?>
                                <?php Pjax::end() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif;?>


        <?php if($model->_subject_type == SubjectType::SUBJECT_TYPE_SELECTION) {?>
        <div class="box box-default">
                <div class="box-header bg-gray ">
                    <div class="row" id="data-grid-filters">
                        <div class="col col-md-6">
                            <h4><?= __('Selective Subject Group Information');?>
                            </h4>
                        </div>
                        <div class="col col-md-6">
                            <div class="pull-right">

                            </div>
                        </div>


                    </div>

                </div>
           <div class="box-body">
               <div class="row">
                       <div class="col-md-12">
                           <?php $selectives = array();?>
                           <?php if(is_array($list_group)): ?>
                               <strong><i class="fa fa-table margin-r-5"></i> <?= __('Group of Subjects');?></strong>
                               <p>
                                   <?php foreach ($list_group as $item):?>
                                    <?php $selectives [] = $item->_subject;?>
                                       <span class="label label-info"><?= $item->subject->name;?></span>
                                   <?php endforeach; ?>
                               </p>
                           <?php else: ?>
                               <?php $selectives[] = $model->_subject;?>
                           <?php endif; ?>
                           <?= $form->field($model, 'in_group')->widget(Select2Default::classname(), [
                               'data' => ArrayHelper::map(ECurriculumSubject::getOtherSubjectByCurriculumSemester($model->_curriculum, $model->_semester, $selectives), '_subject','subject.name'),
                               'allowClear' => false,
                               'options' => [
                                   'multiple' => true,

                               ],
                           ]) ?>
                       </div>
               </div>
            </div>
        </div>

       <?php } ?>


            <?php if(!$model->curriculum->accepted) { ?>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/formation', 'id'=>$model->_curriculum], ['class' => 'btn btn-default btn-flat']) ?>

                    <?= $this->getResourceLink(__('Delete'), ['curriculum/curriculum-subject-edit', 'id' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete','data-pjax' => '0']) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
            <?php } ?>
        </div>
    </div>


<?php ActiveForm::end(); ?>

<?php

$message = __("Total acload, credit, count of weeks is not suitable");
$message_min = __("The minimum value is {border}", ['border'=>$min_border]);
$message_max = __("The maximum value is {border}", ['border'=>$max_border]);
$message_total_acload = __("The total load must be greater than 0");
//$script = <<< JS
 //   setDeleteButton();
    /*$(function() {
        $("form").submit(function( event ) {
            if(($("#total_acload").val() === "") || (parseInt($("#total_acload").val()) <= 0)){
                // $("#total_acload").parent().append('<div class="help-block">$message_total_acload</div>');
                $("#total_acload").parent().addClass('has-error'); 
                return true;
            }
            else{
                $("#total_acload").parent().removeClass('has-error');
               // $("#total_acload").nextAll('div').remove();
                return true;
            }
        });
     });*/
//JS;
//$this->registerJs($script);
?>

<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>




