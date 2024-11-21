<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use yii\widgets\DetailView;
use common\models\curriculum\ESubjectSchedule;
use common\models\system\classifier\EducationType;

use common\models\system\AdminRole;

//$this->title = __('Subject Schedule');
if ($this->_user()->role->code == "teacher") {
    $this->params['breadcrumbs'][] = ['url' => ['teacher/attendance-journal']];
}
else {
    $this->params['breadcrumbs'][] = ['url' => ['attendance/attendance-journal']];
}
//$this->params['breadcrumbs'][] = ['url' => ['teacher/check-lesson', 'id'=>$model->id], 'label' => __('Attendance of Subject Group')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => true], 'enablePushState' => false]) ?>
<div class="row">


<div class="col col-md-9 col-lg-9">
    <div class="box box-default ">
        <div class="box-body no-padding">

            <?php if(isset($students)){ ?>
            <div class="table-responsive">

                    <table class="table table-bordered">
                        <tr>
                            <th rowspan="3" style="text-align:center; vertical-align:middle;"><?= __('№');?></th>
                            <th rowspan="3" style="text-align:center; vertical-align:middle;"><?= __('Fullname of Student');?></th>
                            <th rowspan="3" style="text-align:center; vertical-align:middle;">
                                <?= __('S');?>
                            </th>
                            <th rowspan="3" style="text-align:center; vertical-align:middle;">
                                <?= __('SZ');?>
                            </th>
                            <th colspan="<?=count($models)?>" style="text-align: center;"><?= $model->getAttributeLabel('lesson_date');?></th>
                        </tr>
                        <tr>
                            <?php foreach($models as $item){?>
                                <th style="text-align: center">
                                <?php //if(Yii::$app->formatter->asDate($item->lesson_date, 'php:Y-m-d') == date('Y-m-d',time())) { ?>
                                        <?//php if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT || Yii::$app->user->identity->_employee == $item->_employee) { ?>
                                <?php if (Yii::$app->user->identity->_employee == $item->_employee) { ?>
                                    <?= Html::a(Yii::$app->formatter->asDate($item->lesson_date, 'php:Y-m-d'),['teacher/check-lesson', 'id' => $item->id], ['data-pjax' => 0, 'class'=>'badge bg-green']);?>
                                        <?php } else { ?>
                                            <?= Yii::$app->formatter->asDate($item->lesson_date, 'php:Y-m-d');?>
                                        <?php } ?>

                                    <?/*= Yii::$app->formatter->asDate($item->lesson_date, 'php:d.m.Y');*/?>
                                    
                                    <?php /*} else {?>
                                    <small><?= Yii::$app->formatter->asDate($item->lesson_date, 'php:d.m.Y');?></small>
                                <?php }*/ ?>
                                </th>
                            <?php } ?>
                        </tr>
                        <tr>
                            <?php foreach($models as $item){?>
                                <td style="text-align: center">
                                    <small><small><i><?php echo $item->lessonPair->fullName;;?></i></small></small>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php
                        $i=1;
                        foreach($students as $item){?>
                            <tr>
                                <td><?php echo $i++;?></td>
                                <td><?php echo $item->student->fullName;?></td>
                                <td style="text-align: center"><?php echo @$nbs_summary[$item->_student]['s'];?></td>
                                <td style="text-align: center"><?php echo @$nbs_summary[$item->_student]['sz'];?></td>
                                <?php foreach($models as $item2){?>
                                    <td style="text-align: center">
                                        <?php
                                            $label = "";
                                            if(@$nbs[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]['s']){
                                                $label = '<span class="text text-green">'.@$nbs[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]['s'].'</span>';
                                            }
                                            elseif(@$nbs[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]['sz']){
                                                $label = '<span class="text text-red">'.@$nbs[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]['sz'].'</span>';
                                            }

                                            //$s = @$nbs[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair];
                                            //$sz = @$nbsz[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair];

                                            //$label = ($s == 2) ? $s : $sz;

                                        if ($this->_user()->role->code != AdminRole::CODE_TEACHER && $this->_user()->role->code != AdminRole::CODE_DEPARTMENT) {
                                            echo Html::a($label, '#', [
                                                'class' => 'showModalButton ',
                                                'modal-class' => 'modal-lg',
                                                'title' => $item->student->fullName,
                                                'value' => Url::to(['attendance/change-attendance',
                                                    'id' => @$nbs[$item->_student][Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]['id']
                                                ]),
                                                'data-pjax' => 0
                                            ]);
                                        }
                                        else{
                                            echo $label;
                                        }
                                        ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <?php foreach($models as $item2){?>
                                <th>
                                    <?php if(@$check[Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]) {?>
                                        <i class="fa fa-check-square-o"></i>
                                    <?php } ?>
                                </th>
                            <?php } ?>

                        </tr>
                    </table>

                </div>

                    <?= \yii\widgets\LinkPager::widget(['pagination' => $pages]);?>

                <div class="clearfix">

                </div>
                <br>

                    <div class="box box-solid">

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="box-group" id="accordion">
                                <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                                <div class="panel box box-primary">
                                    <div class="box-header with-border">
                                        <h4 class="box-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" class="collapsed">
                                                <?= __('Subject Topics'); ?>
                                            </a>

                                        </h4>

                                        <div class="box-tools pull-right">
                                            <button type="button" data-parent="#accordion" href="#collapseOne" aria-expanded="false" class="btn btn-box-tool" data-toggle="collapse"><i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                                        <div class="box-body">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th style="text-align:center; vertical-align:middle; width:10px;"><?= __('№');?></th>
                                                    <th style="text-align: center;"><?= __('Subject Topic');?></th>
                                                    <th style="text-align:center; vertical-align:middle;width:100px;"><?= __('Lesson Date');?></th>
                                                    <th style="text-align: center;;width:10px;"><?= __('Checked');?></th>
                                                </tr>
                                                <?php
                                                $i=1;
                                                foreach($lesson_dates->all() as $item2){?>
                                                    <tr>
                                                        <td style="text-align:center; vertical-align:middle;"><?= $i++;?></td>
                                                        <td style="text-align: justify;"><?= @$item2->subjectTopic->name;?></td>
                                                        <td style="text-align:center; vertical-align:middle;"><?= Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d');?></td>
                                                        <td style="text-align: center;">
                                                            <?php if(@$check[Yii::$app->formatter->asDate($item2->lesson_date, 'php:Y-m-d')][$item2->_lesson_pair]) {?>
                                                                <i class="fa fa-check-square-o"></i>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>

            <?php } ?>
        </div>

    </div>
    </div>

    <div class="col col-md-3 col-lg-3" id="sidebar">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4 class="box-title"><?= __('Information') ?></h4>
            </div>
            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_group',
                            'value' => function (ESubjectSchedule $data) {
                                return $data->group ? $data->group->name : '';
                            }
                        ],
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
                            'attribute' => '_employee',
                            'value' => function (ESubjectSchedule $data) {
                                return $data->employee ? $data->employee->fullName : '';
                            }
                        ],
                    ],
                ]) ?>
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
