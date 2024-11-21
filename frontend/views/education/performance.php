<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\curriculum\MarkingSystem;

/* @var $this \frontend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $item \frontend\models\curriculum\StudentExam */

$user = $this->_user();
$time = null;
$timestamp = pow(10, 10);
$past = true;

$sem = $this->getSelectedSemester();

$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>

<?php if (count($dataProvider['items']) == 0): ?>
    <div class="empty"><?= __('Ma\'lumotlar mavjud emas') ?></div>
<?php endif; ?>
<?php foreach ($dataProvider['items'] as $semester): ?>
    <div class="box box-success <?= $semester['semester']->code == $sem->code ? '' : 'collapsed-box' ?>">
        <div class="box-header with-border">
            <h3 class="box-title "><?= $semester['semester']->name ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                            class="fa fa-<?= $semester['semester']->code == $sem->code ? 'minus' : 'plus' ?>"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <table class="table table-striped">
                <thead>
                <?php $count = count($dataProvider['types']); ?>
                <tr>
                    <th width="50px"></th>
                    <th width="50%"><?= __('Subject') ?></th>
                    <th><?= __('JN'); ?></th>
                    <th><?= __('ON'); ?></th>
                    <th><?= __('YN'); ?></th>
                    <th><?= __('Umumiy'); ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                $i = 0;
                ?>
                <?php foreach ($semester['subjects'] as $item): $i++ ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $item->subject->name ?></td>
                        <?php if(@$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_FIRST]->passed_status == 1) { ?>
                            <td>
                                <?//= @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?>
                                <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                    <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade && @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo  (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade
                                    ?>
                                <? else: ?>
                                    <?=
                                    @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?
                                        @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade :
                                        @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    ?>
                                <?php endif;?>
                            </td>
                            <td>
                                <?//= @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?>
                                <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                    <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade && @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    ?>
                                <? else: ?>
                                    <?=
                                    @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?
                                        @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade :
                                        @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    ?>
                                <?php endif;?>
                            </td>
                            <td><?= @$item->_results[ExamType::EXAM_TYPE_FINAL][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?></td>
                            <td><?= @$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?></td>
                        <?php } elseif(@$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_SECOND]->passed_status == 1){?>
                            <td>
                                <?//= @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade ?>

                                <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                    <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade && @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo  (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade
                                    ?>
                                <? else: ?>
                                    <?=
                                    @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade ?
                                        @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade :
                                        @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    ?>
                                <?php endif;?>

                            </td>
                            <td>
                                <?//= @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade ?>
                                <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                    <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade && @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    ?>
                                <? else: ?>
                                    <?=
                                    @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade ?
                                        @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade :
                                        @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade;
                                    ?>
                                <?php endif;?>

                            </td>
                            <td><?= @$item->_results[ExamType::EXAM_TYPE_FINAL][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade ?></td>
                            <td><?= @$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_SECOND]->grade ?></td>
                        <?php } elseif(@$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_THIRD]->passed_status == 1) { ?>
                            <td>
                                <?//= @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade ?>
                                <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                    <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade && @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo  (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade
                                    ?>
                                <? else: ?>
                                    <?=
                                    @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade ?
                                        @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade :
                                        @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    ?>
                                <?php endif;?>

                            </td>
                            <td>
                                <?//= @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade ?>
                                <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                    <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade && @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    ?>
                                <? else: ?>
                                    <?=
                                    @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade ?
                                        @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade :
                                        @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade;
                                    ?>
                                <?php endif;?>

                            </td>
                            <td><?= @$item->_results[ExamType::EXAM_TYPE_FINAL][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade ?></td>
                            <td><?= @$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_THIRD]->grade ?></td>
                        <?php } else { ?>
                        <td>
                            <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                <?php
                                    if(@$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade && @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo  (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)/2;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                    elseif (@$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                        echo @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade
                                 ?>
                                <?/*=
                                @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?
                                    @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade :
                                    (@$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)/2*/;
                                ?>
                            <? else: ?>
                            <?=
                            @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?
                                @$item->_results[ExamType::EXAM_TYPE_CURRENT][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade :
                                @$item->_results[ExamType::EXAM_TYPE_CURRENT_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_CURRENT_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                            ?>
                            <?php endif;?>
                        </td>
                        <td>
                            <?php if($sem->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE):?>
                                <?php
                                if(@$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                    echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade && @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                    echo (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)/2;
                                elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                    echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                elseif (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)
                                    echo @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                                ?>

                                <?/*=
                                @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?
                                    @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade :
                                    (@$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade)/2;
                                */?>
                            <? else: ?>
                                <?=
                            @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?
                                @$item->_results[ExamType::EXAM_TYPE_MIDTERM][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade :
                                @$item->_results[ExamType::EXAM_TYPE_MIDTERM_FIRST][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade + @$item->_results[ExamType::EXAM_TYPE_MIDTERM_SECOND][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade;
                            ?>
                            <?php endif;?>
                        </td>
                        <td><?= @$item->_results[ExamType::EXAM_TYPE_FINAL][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?></td>
                        <td><?= @$item->_results[ExamType::EXAM_TYPE_OVERALL][FinalExamType::FINAL_EXAM_TYPE_FIRST]->grade ?></td>
                        <?php } ?>

                        <? //php foreach ($dataProvider['types'] as $t => $type): ?>

                        <? //php endforeach; ?>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<?php Pjax::end() ?>
