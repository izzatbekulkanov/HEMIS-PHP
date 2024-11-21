<?php

use backend\widgets\GridView;
use frontend\models\curriculum\SubjectTask;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

?>
<div class="row">
    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">
            <?php if (count($dataProviderActivity->getModels())): ?>
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('List Answers') ?></h3>
                </div>

                <?= GridView::widget([
                    'id' => 'data-grid',
                    'layout' => "<div class='box-body no-padding'>{items}</div>",
                    'dataProvider' => $dataProviderActivity,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'headerOptions' => [
                                'style' => 'width:3%',
                            ],
                        ],
                        [
                            'attribute' => 'comment',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return htmlentities($data->comment);
                            },
                        ],
                        [
                            'attribute' => 'filename',
                            'format' => 'raw',
                            /*'value' => function ($data) {
                                if (!empty($data->filename)) {
                                    return Html::a($data->filename['name'], \yii\helpers\Url::current(['file' => $data->id]));
                                }
                            },*/
                            'value' => function ($data) {
                                @$link = "";
                                if (!empty($data->filename)) {
                                    if(is_array($data->filename)){
                                        if(isset($data->filename['name'])){
                                            return Html::a($data->filename['name'], \yii\helpers\Url::current(['file' => $data->id]));
                                        }
                                        else{
                                            foreach (@$data->filename as $file) {
                                                @$link .= Html::a (@$file['name'], @$file['base_url'] . '/' . @$file['path'], ['data-pjax' => 0]). '; ';
                                            }
                                            return @$link;
                                        }
                                     }
                                }
                            },
                        ],
                        [
                            'attribute' => 'send_date',
                            'format' => 'raw',

                            'value' => function ($data) {
                                return Yii::$app->formatter->asDateTime($data->send_date->getTimestamp());
                            },
                        ],
                        [
                            'attribute' => 'mark',
                        ],
                        [
                            'attribute' => 'active',
                            'label' => __('Status'),

                            'value' => function ($data) {
                                if($data->mark >0 )
                                    return $data->statusOptions[$data->active];
                            }
                        ],
                    ],
                ]); ?>
            <?php endif; ?>
        </div>

        <?php if ($task->attempt_count < $task->subjectTask->attempt_count /*&& $task->_task_status != SubjectTask::TASK_STATUS_RATED*/ && $task->canSubmitTask() && ($marked < $min_border)): ?>
            <div class="box box-default ">
                <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 0]]); ?>
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Send Answer') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col col-md-12">
                            <?= $form->field($model, 'comment')->textArea(['maxlength' => true, 'rows' => 8]) ?>
                            <?= $form->field($model, 'filename')->widget(
                                \backend\widgets\UploadDefault::class,
                                [
                                    'url' => ['dashboard/file-upload', 'type' => 'attachment'],
                                    'acceptFileTypes' => new JsExpression(
                                        '/(\.|\/)(xlsx?|docx?|pdf|pptx?|zip|rar)$/i'
                                    ),
                                    'maxFileSize' => \common\components\Config::getUploadMaxSize(),
                                    'multiple' => true,
                                    'sortable' => true,
                                    'maxNumberOfFiles' => 3,
                                    'accept' => '.pdf, .docx, .doc, .ppt, .pptx, .xls, .xlsx, .zip, .rar',
                                    'options' => ['class' => 'file'],
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Send'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $task,
                    'attributes' => [
                        [
                            'attribute' => '_curriculum',
                            'label' => __('Curriculum Curriculum'),
                            'value' => function ($data) {
                                return $data->curriculum ? $data->curriculum->name : '';
                            }
                        ],
                        [
                            'attribute' => '_semester',
                            'value' => function ($data) {
                                return $data->semester ? $data->semester->name : '';
                            }
                        ],
                        [
                            'attribute' => '_subject',
                            'value' => function ($data) {
                                return $data->subject ? $data->subject->name : '';
                            }
                        ],
                        [
                            'attribute' => 'id',
                            'label' => __('Name of Task'),
                            'value' => function ($data) {
                                return $data->subjectTask ? $data->subjectTask->name : '';
                            }
                        ],
                        [
                            'attribute' => 'max_ball',
                            'label' => __('Max Ball'),
                            'value' => function ($data) {
                                return $data->subjectTask->max_ball;
                            }
                        ],
                        [
                            'attribute' => 'deadline',
                            'label' => __('Deadline'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return '<span class="badge bg-red"><b>' . Yii::$app->formatter->asDate($data->deadline->getTimestamp(), 'php:d.m.Y') . '</b></span>';
                            }
                        ],
                        [
                            'attribute' => 'attempt_count',
                            'label' => __('Attempt Count'),
                            'format' => 'raw',
                            'value' => function ($data) {
                                return ($data->attempt_count == null ? 0 : $data->attempt_count) . '/' . $data->subjectTask->attempt_count;
                            }
                        ],
                        [
                            'attribute' => '_final_exam_type',
                            'label' => __('Final Exam Type'),
                            'value' => function ($data) {
                                return $data->finalExamType ? $data->finalExamType->name : '';
                            }
                        ],


                    ],
                ]) ?>
            </div>
            <br/>

        </div>
    </div>


</div>
