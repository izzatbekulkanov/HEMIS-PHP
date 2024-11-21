<?php
/**
 * @var $models \common\models\curriculum\ESubjectSchedule[]
 */
?>
<div style="margin: 0 -15px">
    <table class="table-striped table table-hover">
        <thead>
        <tr>
            <th><?= __('Subject') ?></th>
            <th><?= __('Employee') ?></th>
            <th><?= __('Group') ?></th>
            <th><?= __('Lesson Pair') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $model): ?>
            <tr>
                <td><?= $model->subject->getShortTitle(4) ?><p class="text-muted"><?= $model->trainingType->name ?></p></td>
                <td><?= $model->employee->getShortName() ?></td>
                <td><?= $model->group->name ?></td>
                <td><?= $model->lessonPair->name ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>