<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use kartik\select2\Select2;
use common\models\curriculum\ESubjectSchedule;

$this->params['breadcrumbs'][] = ['url' => ['teacher/training-list']];
$this->params['breadcrumbs'][] = $this->title;
\kartik\select2\Select2Asset::registerBundle($this, '3.x');

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="row">

    <div class="col col-md-8 col-lg-8">
        <?=Html::beginForm();?>

        <div class="box box-default ">
            <div class="box-header bg-gray">

                <div class="row" id="data-grid-filters">
                    <div class="col col-md-12">
                        <h4 class="text text-primary pull pull-right">
                            <?= __('Students who do not attend lessons will be checked') ?>

                        </h4>
                    </div>
                </div>

                <div class="row" id="data-grid-filters">
                    <?//php $form = ActiveForm::begin(); ?>
                    <div class="col col-md-2">
                        <div class="form-group">
                            <?= '<label class="control-label">'.$model->getAttributeLabel('_subject_topic').'</label>'?>
                        </div>
                    </div>
                    <div class="col col-md-10">
                        <?php
                            $topic_list = [];
                            foreach ($topics as $key=>$item){
                                $topic_list [$item->id] = ++$key.'. '.$item->name;
                            }
                        ?>
                        <?= Select2::widget([
                            'model' => $model,
                            'attribute' => '_subject_topic',
                            'data' =>  $topic_list,
                            //'data' =>  ArrayHelper::map($topics, 'id','fullName'),
                            'theme' => Select2::THEME_DEFAULT,
                            'options' => ['placeholder' => __('Choose'), 'required'=>true],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
            <table class="table table-striped table-bordered">
                <tr>
                    <th><?= __('№');?></th>
                    <th><?= __('Fullname of Student');?></th>
                    <th><?= __('Payment Form');?></th>
                    <th><?= __('Check');?></th>
                </tr>
                <?php
                $i=1;
                foreach($students as $item){?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo $item->student->fullName;?></td>
                        <td><?php echo @$item->studentMeta->paymentForm ? @$item->studentMeta->paymentForm->name : '';?></td>

                        <td>
                            <?php
                            $disabled = isset($nbsz[$item->_student]) ? true : false;
                            echo Html::checkbox('sz[]', @$nbsz[$item->_student], ['value' => @$item->_student, 'class' => 'radio', 'id'=>@$item->_student.'_s', 'disabled'=> $disabled]);
                            /*echo CheckBo::widget([
                                'name' => "sz[$item->_student]",
                               // 'attribute' => $item->_student,
                                'value' => $nbsz[$item->_student],
                                'type' => CheckBo::TYPE_CHECKBOX,
                            //    'id' => 'training_check_' . $item->code,
                            ]);*/
                            ?>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th colspan="4">
                        <p class="pull pull-right">
                            <?php
                            if ($canCheck/* && !$disabled*/):
                                echo Html::submitButton(
                                    '<i class="fa fa-check"></i> ' . __('Save'),
                                    ['class' => 'btn btn-primary btn-flat pull-right', 'name' => 'btn']
                                );
                            endif;
                            ?>

                            <?//= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat', 'name'=>'btn']) ?>
                        </p>
                        <?= Html::endForm();?> </th>
                </tr>
            </table>
        </div>
    </div>

    <div class="col col-md-4 col-lg-4" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_subject',
                            'value' => function (ESubjectSchedule $data) {
                                return $data->subject ? $data->subject->name : '';
                            }
                        ],
                        [
                            'attribute' => '_training_type',
                            'value' => function (ESubjectSchedule $data) {
                                return $data->trainingType ? $data->trainingType->name : '';
                            }
                        ],

                        [
                            'attribute' => 'lesson_date',
                            'value' => function (ESubjectSchedule $data) {
                                return Yii::$app->formatter->asDate($data->lesson_date, 'dd-MM-Y');
                            }
                        ],
                        [
                            'attribute' => '_group',
                            'value' => function (ESubjectSchedule $data) {
                                return $data->group ? $data->group->name : '';
                            }
                        ],
                    ],
                ]) ?>
            </div>
            <br/>
            <div class="row" id="data-grid-filters">
                <div class="col col-md-10">
                    <div class="form-group">
                        <?/*= $this->getResourceLink(
                            '<i class="fa fa-list"></i> ' . __('Attendance Journal'),
                            ['teacher/attendance-journal', 'education_year' => $model->_education_year, 'semester' => $model->_semester, 'group' => $model->_group, 'subject' => $model->_subject, 'training_type' => $model->_training_type],
                            ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                        ) */?>
                    </div>
                </div>
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
