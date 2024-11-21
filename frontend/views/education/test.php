<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use cabinet\components\View;
use cabinet\models\SubjectProvider;
use common\models\Question;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $model SubjectProvider
 * @var $topic Topic
 * @var $question Question
 * @var $this View
 */
$this->title = __('Quiz: {name}', ['name' => $task->subjectTask->name]);

if (isset($this->params['studentTopic'])) {
    $this->registerJsVar('studentTopic', $this->params['studentTopic']->id);
    $this->registerJsVar('trackUrl', linkTo(['subject/track', 'id' => $this->params['studentTopic']->id]));
}
@$testData = \frontend\models\curriculum\SubjectTaskActivity::getActiveTopicData($task, $this->_user());
$i = 0;
?>
<div class="top-nav">
    <h4 class="sm black bold"><?= @$task->subject->name; ?></h4>
    <ul class="top-nav-list">
        <li class="next-course">
            <a href="#">
                <?= $this->_user()->fullName; ?> (<?= $this->_user()->login ?>)
            </a>
        </li>
        <li class="next-course">
            <a href="#" id="timer" data-time="<?= $testData->getActualTime() ?>">
                <?= $testData->getActualTime() ?>
            </a>
        </li>

        <li class="backpage">
            <a href="<?//= currentUrl(['test' => null]) ?>"><i class="fa fa-close"></i></a>
        </li>
    </ul>
</div>
<section id="quizz-intro-section" class="quizz-intro-section learn-section">
    <div class="container">
        <div class="title-ct">
            <div class="h3">
                <?= $this->title ?>
            </div>
        </div>
        <form id="question_data"
              data-left="<?= linkTo(['test/left', 'id' => $task->id]) ?>"
              data-finish="<?= linkTo(['test/finish', 'id' => $task->id]) ?>"
              data-message="<?= Html::encode(__('Test yakunlandi!')) ?>"
              action="<?= linkTo(['test/status', 'id' => $task->id], true) ?>"
              class="question-content-wrap">
            <div class="row">
                <div class="col-md-8">
                    <div class="question-content">
                        <?php foreach ($testData->getQuestions() as $qid => $question): $i++ ?>
                            <div class="question-item" id="<?= $qid ?>">
                                <h4 class="sm"><?= $i . '. ' . $question->question ?></h4>
                                <div class="answer">
                                    <ul class="answer-list">
                                        <?php
                                        $m = $question->isMultiple();
                                        $answers = $question->answers;
                                        ?>
                                        <?php foreach ($testData->_questionsData[$question->id]['m'] as $a => $answer): ?>
                                            <li>
                                                <input type="<?= $m ? 'checkbox' : 'radio' ?>"
                                                       value="<?= $a ?>"
                                                       name="q-<?= $qid ?>"
                                                       class="q-<?= $qid ?>"
                                                       data-id="<?= $qid ?>"
                                                    <?= $testData->hasCheckedAnswer(
                                                        $qid,
                                                        $a
                                                    ) ? 'checked="checked"' : '' ?>
                                                       id="q-<?= $qid ?>-<?= $a ?>">
                                                <label for="q-<?= $qid ?>-<?= $a ?>">
                                                    <i class="icon <?= $question->isMultiple() ? 'icon_check' : 'icon_radio' ?>"></i>
                                                    <?= $answers[$answer] ?>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-4" id="sidebar">
                    <aside class="question-sidebar text-center">
                        <div class="score-sb">
                            <div class="">
                                <ul class="pager">
                                    <?php $i = 0; ?>
                                    <?php foreach ($testData->getQuestions() as $qid => $question): $i++ ?>
                                        <li>
                                            <a href="#<?= $qid ?>" id="p-<?= $qid ?>"
                                               class="<?= $testData->hasChecked($qid) ? 'active' : '' ?>"
                                               data-id="<?= $qid ?>"><?= $i ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a class="submit mc-btn btn-style-1"
                                   onclick="return confirm('<?= Html::encode(__("Testni yakunlaysizmi?")) ?>')"
                                   href="<?= linkTo(['test/finish', 'id' => $topic->id]) ?>">
                                    <?= __('Testni yakunlash') ?>
                                </a>
                            </div>

                        </div>
                    </aside>
                </div>
            </div>
        </form>
    </div>
</section>
