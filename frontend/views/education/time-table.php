<?php

use backend\widgets\GridView;
use backend\widgets\ListViewDefault;
use backend\widgets\Select2Default;
use backend\widgets\SimpleNextPrevPager;
use frontend\models\curriculum\StudentSchedule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2fullcalendar\yii2fullcalendar;

/* @var $this \frontend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $item StudentSchedule */

$this->title = $this->getControllerActionTitle();
$this->params['breadcrumbs'][] = $this->title;
$user = $this->_user();

$cookie = Yii::$app->request->cookies->get(\frontend\components\View::PARAM_WEEK);

$weeks = StudentSchedule::getStudentSemesterWeeks($user, $this->getSelectedSemester());
@$keys = $weeks['keys'];

@$week = $cookie ? $cookie->value : @$keys[0];
$selectedWeek = Yii::$app->request->get('week') ?: @$week;
@$index = array_search(@$selectedWeek, @$keys);
if ($index === false) {
    @$selectedWeek = @$keys[0];
}

@$items = StudentSchedule::searchForStudentWeekly($user, $this->getSelectedSemester(), @$selectedWeek)->getModels();


$prev = $index > 0 ? $index - 1 : 0;
$next = $index < count($keys) - 1 ? $index + 1 : count($keys) - 1;
?>
<?php Pjax::begin(['id' => 'schedule-grid', 'timeout' => false, 'options' => ['data-pjax' => 1], 'enablePushState' => false]) ?>
<?php echo $this->renderFile('@frontend/views/layouts/partials/_semesters.php') ?>
    <div class="row" id="data-grid-filters">
        <?php $form = ActiveForm::begin(); ?>
        <div class="col col-sm-2 col-md-1 col-md-offset-3 hidden-xs">
            <label>&nbsp;</label>
            <a href="<?= Url::current(['week' => @$keys[$prev]]) ?>" class="btn btn-primary btn-block btn-flat">
                <i class="fa fa-chevron-left"></i>
            </a>
        </div>
        <div class="col col-xs-12 col-sm-8 col-md-4 text-center">
            <?= $form->field($searchModel, '_week')->widget(Select2Default::classname(), [
                'data' => $weeks['options'],
                'allowClear' => false,
                'hideSearch' => false,

                'options' => [
                    'onchange' => 'selectWeek(this)',
                    'value' => $selectedWeek,
                ]
            ])->label(__('Choose Week')); ?>
        </div>
        <div class="col col-sm-2 col-md-1  hidden-xs">
            <label>&nbsp;</label>
            <a href="<?= Url::current(['week' => @$keys[$next]]) ?>" class="btn btn-primary btn-block btn-flat">
                <i class="fa fa-chevron-right"></i>
            </a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php if (count($items)): ?>

    <?php foreach (array_chunk($items, 3) as $data): ?>
        <div class="row sh-parent">
            <?php foreach ($data as $model): ?>
                <div class="col-sm-4">
                    <div class="box box-success sh">
                        <div class="box-header  with-border">
                            <h3 class="box-title display-block">
                                <?= ucfirst(\Yii::$app->formatter->asDate($model['date']->getTimestamp(), 'php:l')) ?>
                                <span class="pull-right text-muted fs16">
                                    <?= \Yii::$app->formatter->asDate($model['date']->getTimestamp(), 'php:d F, Y') ?>
                                    </span>
                            </h3>
                        </div>
                        <div class="box-footer no-padding">
                            <ul class="nav nav-stacked">
                                <?php foreach ($model['items'] as $i => $item): ?>
                                    <li>

                                        <a href="<?= linkTo(['resources', 'subject' => $item->subject->id]) ?>"
                                           data-pjax="0"
                                           title="<?= $item->employee ? $item->employee->getFullName() : '' ?>">
                                            <?= $item->lessonPair->name ?>. <?= $item->subject->name ?><br>
                                            <span class="text-center text-muted"><?= $item->auditorium->name ?></span>
                                            <span class="separator">/</span>
                                            <span class="text-center text-muted"><?= $item->trainingType->name ?></span>
                                            <? if ($item->additional != "") { ?>
                                                <span class="separator">/</span>
                                                <span class="text-center text-muted"><?= $item->additional ?></span>
                                            <? } ?>
                                            <span class="separator">/</span>
                                            <span class="text-center text-muted"><?= $item->employee->shortName ?></span>

                                            <span class="pull-right text-muted"><?= $item->lessonPair->start_time ?></span>
                                        </a>

                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty"><?= __('Ushbu davrga dars soatlari belgilanmagan') ?></div>
<?php endif ?>
    <script type="text/javascript">
        function selectWeek(e) {
            var w = $(e).val();
            $.pjax.reload('#schedule-grid', {'url': '<?=linkTo(['education/time-table'])?>?week=' + w});
        }
    </script>
<?php Pjax::end() ?>