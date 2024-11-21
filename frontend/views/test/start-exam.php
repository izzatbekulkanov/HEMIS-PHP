<?php
/**
 * @var $model \common\models\curriculum\EExamStudent
 * @var $question \common\models\curriculum\EExamQuestion
 * @var $this \frontend\components\View
 */

use frontend\models\curriculum\SubjectTaskStudent;
use yii\helpers\Url;

$this->addBodyClass('skin-blue layout-top-nav');
$q = 0;
$vs = 'abcdefghijklmn';

$this->title = $model->exam->name;
$this->registerJs('setTimeTicker();checkPreviousTest();');
$testTime = $model->getHowMuchTime();
$hasAnswers = false;
?>
<div class="wrapper" style="height: auto; min-height: 100%;padding-top: 40px">
    <header class="main-header">
        <nav class="navbar navbar-fixed-top">
             <span id="timeview">
            <span id="progress" style="width: <?= $testTime * 100 / ($model->exam->duration * 60) ?>%"></span>
        </span>
            <div class="container">
                <a href="#" class="navbar-brand" id="time_ticker"
                   style="letter-spacing: 1px;font-size: 20px"><?= sprintf('%02d:%02d:%02d', ($testTime / 3600), ($testTime / 60 % 60), $testTime % 60) ?></a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <a href="<?= linkTo(['test/exams']) ?>">
                                <i class="fa fa-close"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= Url::current(['finish' => 1]) ?>"
                               data-confirm="<?= \yii\helpers\Html::encode(__('Testni yakunlaysizmi?')) ?>"
                            >
                                <i class="fa fa-flag"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- /.container-fluid -->

        </nav>

    </header>
    <!-- Full Width Column -->
    <div class="content-wrapper" style="min-height: 498px;">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mt30">
                    <div class="well">
                        <h3><?= $model->exam->name ?></h3>
                        <p><?= $model->exam->comment ?></p>
                    </div>

                    <?php foreach ($questions as $i => $data): ?>
                        <?php
                        $q++;
                        $question = $data['q'];
                        if (!($question instanceof \common\models\curriculum\EExamQuestion)) continue;

                        $selected = isset($data['s']) ? $data['s'] : [];
                        ?>
                        <div class="box box-default question" id="question_<?= $q ?>">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= $q ?>. <?= $question->name ?></h3>
                            </div>
                            <div class="box-body checkbo">
                                <?php foreach ($data['a'] as $v => $variant): ?>
                                    <?php
                                    $variants = $question->answers;
                                    $type = $question->isMultiple() ? 'checkbox' : 'radio';
                                    $checked = isset($selected[$variant]) ? 'checked' : '';
                                    $hasAnswers = $hasAnswers || $checked;
                                    ?>
                                    <p>
                                        <label class="cb-<?= $type ?>  <?= $checked ?>"
                                               for="test_question_<?= $q ?>_<?= $v ?>"

                                        >
                                            <input type="<?= $type ?>"
                                                   onchange="setAnswer(this,<?= $question->id ?>,<?= $v ?>)"
                                                   value="<?= $v ?>"
                                                <?= $checked ?>
                                                   name="test_question_<?= $q ?>"
                                                   id="test_question_<?= $q ?>_<?= $v ?>">
                                            <span class="qv">
                                            <?= @$variants[$variant] ?>
                                            </span>
                                        </label>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-4 mt30 sticky-sidebar" data-padding="50">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= __('Javoblar') ?></h3>
                        </div>
                        <div class="box-body">
                            <ul class="pagination">
                                <?php
                                $q = 0;
                                ?>
                                <?php foreach ($questions as $data): ?>
                                    <?php
                                    $q++;
                                    $question = $data['q'];
                                    if (!($question instanceof \common\models\curriculum\EExamQuestion)) continue;
                                    ?>
                                    <li class="pqitem <?= isset($data['s']) && !empty($data['s']) ? 'active' : '' ?>">
                                        <a class="pq" href="#question_<?= $q ?>" id="pq_<?= $question->id ?>"
                                           data-q="<?= $q ?>"><?= $q ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="box-footer">
                            <a href="!#"
                               data-toggle="modal" data-target="#myModal"
                               class="btn btn-primary btn-block">
                                <?= __('Finish Test') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h4><?= \yii\helpers\Html::encode(__('Testni yakunlaysizmi?')) ?></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= __('Yo\'q') ?></button>
                <a class="btn btn-primary" href="<?= Url::current(['finish' => 1]) ?>"><?= __('Ha, yakunlayman') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmTest" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h2><?= \yii\helpers\Html::encode(__('Testni qaytadan ishlaysizmi?')) ?></h2>
                <h4 style="line-height: 26px;margin-top:30px">
                    <?= __('Siz ushbu testni avvalroq {b}{percent}{/b}% natija bilan topshirgansiz. {br}{br}Testni qaytadan topshirmoqchi bo\'lsangiz {b}Davom etish{/b} tugmasini bosing. Agar bu sahifaga tasodifan kirib qolgan bo\'lsangiz birorta ham javob belgilamay {b}Yakunlash{/b} tugmasini bosing. {br}{br}Shu holatda sizning avvalgi natijalaringiz saqlanadi.', ['percent' => round($model->percent, 1)]) ?>
                </h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"><?= __('Davom etish') ?></button>
                <a class="btn btn-default" href="<?= Url::current(['finish' => 1]) ?>"><?= __('Yakunlash') ?></a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function setAnswer(e, q, v) {
        let ch = $(e).is(':checked');
        $.get('<?=Url::current(["set" => 1])?>&q=' + q + '&v=' + v + '&s=' + (ch ? '1' : '0'), function (data) {
            for (let q in data) {
                if (data.hasOwnProperty(q)) {
                    let checked = data[q] != null;
                    if (checked) {
                        $('#pq_' + q).parent().addClass('active');
                    } else {
                        $('#pq_' + q).parent().removeClass('active');
                    }
                }
            }
        });
    }

    function checkPreviousTest() {
        <?php if($model->percent > 0 && $hasAnswers == false):?>
        $('#confirmTest').modal({backdrop: 'static', keyboard: false})
        <?php endif;?>
    }

    function setTimeTicker() {
        let view = $('#time_ticker');
        let testTime = <?=$testTime?>;
        let progress = $('#progress');
        let allTime = <?= $model->exam->duration * 60 ?>;

        let timer = setInterval(function () {
            testTime--;
            if (testTime < 0) {
                clearTimeout(timer);
                location.href = '<?=Url::current(['finish' => 1, 'auto' => 1])?>';
            } else {
                if (testTime % 5 === 0)
                    progress.css('width', testTime * 100 / allTime + '%')
                view.text(new Date(testTime * 1000).toISOString().substr(11, 8));
            }
        }, 1000);
    }
</script>