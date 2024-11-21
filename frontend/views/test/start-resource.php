<?php
/**
 * @var $model SubjectTaskStudent
 * @var $question \common\models\curriculum\ESubjectResourceQuestion
 * @var $this \frontend\components\View
 */

use frontend\models\curriculum\SubjectTaskStudent;
use yii\helpers\Url;

$this->addBodyClass('skin-blue layout-top-nav');
$q = 0;
$vs = 'abcdefghijklmn';
$this->title = $model->getTitle()
?>
<div class="wrapper" style="height: auto; min-height: 100%;">
    <header class="main-header">
        <nav class="navbar navbar-static-top">
            <div class="container">
                <a href="#" class="navbar-brand"><?=$model->getTitle()?></a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <a href="<?= linkTo(['test/index','subject'=>$model->subjectResource->_subject]) ?>" >
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
                    <?php foreach ($questions as $i => $data): ?>
                        <?php
                        $q++;
                        $question = $data['q'];
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
                <div class="col-md-4 mt30 sticky-sidebar">
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
                                    ?>
                                    <li class="pqitem <?= isset($data['s']) && !empty($data['s']) ? 'active' : '' ?>">
                                        <a class="pq" href="#question_<?= $q ?>" id="pq_<?= $question->id ?>"
                                           data-q="<?= $q ?>"><?= $q ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="box-footer">
                            <a href="<?= Url::current(['finish' => 1]) ?>"
                               data-confirm="<?= \yii\helpers\Html::encode(__('Testni yakunlaysizmi?')) ?>"
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
</script>