<?php

use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EExamStudent;
use common\models\system\classifier\Language;
use backend\widgets\Select2Default;
use common\models\system\classifier\TrainingType;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/**
 * @var $model \common\models\curriculum\EExamStudent
 */
$this->title = $model->student->getFullName();

$this->params['breadcrumbs'][] = ['url' => ['exam/index'], 'label' => __('Subject Tasks')];
$this->params['breadcrumbs'][] = ['url' => ['exam/edit', 'id' => $model->exam->id,], 'label' => $model->exam->name];
$this->params['breadcrumbs'][] = ['url' => ['exam/edit', 'id' => $model->exam->id, 'results' => 1], 'label' => __('Exam Results')];
$this->params['breadcrumbs'][] = $this->title;
$q = 0;
$vs = 'abcdefghijklmn';
$questions = $model->getUserQuestions(false);
?>
<?php Pjax::begin(['id' => 'test-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="containers">
    <div class="row">
        <div class="col-md-5 ">
            <div class="box box-default">
                <div class="box-header with-border hidden-print">
                    <h3 class="box-title"><?= __('Natijalar') ?></h3>
                </div>
                <div class="box-body">
                    <table class="table-striped table">
                        <tbody>
                        <tr>
                            <th><?= __('Exam') ?></th>
                            <td><?= $model->exam->name ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Student') ?></th>
                            <td><?= $model->student->getFullName() ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Group') ?></th>
                            <td><?= $model->group->name ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Started At') ?></th>
                            <td><?= $model->started_at ? Yii::$app->formatter->asDatetime($model->started_at->getTimestamp(), 'php: d.m.Y H:i') : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Finished At') ?></th>
                            <td><?= $model->finished_at ? Yii::$app->formatter->asDatetime($model->finished_at->getTimestamp(), 'php: d.m.Y H:i') : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Correct') ?></th>
                            <td><?= round($model->correct) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Percent') ?></th>
                            <td><?= $model->percent ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <button class="btn btn-primary btn-flat hidden-print"
                            onclick="window.print();"
                    ><?= __('Chop etish') ?></button>
                </div>
            </div>
        </div>

        <div class="col-md-7 ">
            <div class="box box-default question">

                <?php foreach ($questions as $i => $data): ?>
                    <?php
                    $q++;
                    $question = $data['q'];
                    if (!($question instanceof \common\models\curriculum\EExamQuestion)) continue;

                    $selected = isset($data['s']) ? $data['s'] : [];
                    ?>
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= $q ?>. <?= $question->name ?></h3>
                    </div>
                    <div class="box-body checkbo">
                        <?php foreach ($data['a'] as $v => $variant): ?>
                            <?php
                            $variants = $question->answers;
                            $type = $question->isMultiple() ? 'checkbox' : 'radio';
                            $checked = isset($selected[$variant]) ? 'selected' : '';
                            $correct = array_key_exists($variant, $selected) && in_array($variant, $question->_answer) ? 'correct' : '';
                            ?>
                            <p>
                                <label class="<?= $checked ?> <?= $correct ?>"
                                       for="test_question_<?= $q ?>_<?= $v ?>">
                                            <span class="qv">
                                                <?php if ($correct): ?>
                                                    <i class="fa fa-check marker" style=""></i>
                                                <?php elseif ($checked): ?>
                                                    <i class="fa fa-close marker" style=""></i>
                                                <?php endif; ?>
                                                <?= @$variants[$variant] ?>
                                            </span>
                                </label>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>


