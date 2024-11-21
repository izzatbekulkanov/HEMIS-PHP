<?php
/**
 * @var $this \frontend\components\View
 */

use lavrentiev\widgets\toastr\Notification;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;
use yii\widgets\Pjax;

$selected = $this->getSelectedSemester();
$user = $this->_user();
?>
<div class="info-box box-mini">
    <div class="row">
        <div class="col-sm-6">
            <span class="info-box-icon bg-green"><i class="fa fa-files-o"></i></span>
            <div class="info-box-content ">

                <span class="info-box-number">
                        <?= $user->meta->group->name ?> <span class="separator">/</span>
                    <?= Yii::$app->formatter->asDate(time(), 'php:d.m.Y') ?>
                    <?php if ($subject = Yii::$app->request->get('subject')): ?>
                        <?php if ($subject = \common\models\curriculum\ESubject::findOne($subject)): ?>
                            <span class="separator">/</span>
                            <?= $subject->name ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="info-box-content no-margin text-right text-center-sm">
                <label class="hidden visible-xs plabel"><?= __('Semester') ?></label>
                <ul class="pagination pagination-sm psemester ">
                    <li class=" hidden-xs"><a href="#" class="plabel"><?= __('Semester') ?></a></li>
                    <?php foreach ($this->_user()->getSemesters() as $code => $semester): ?>
                        <li class="<?= $selected->code == $code ? 'active' : '' ?>">
                            <a href="<?= Url::current(['semester' => $code]) ?>"><?= $semester->getSemesterNumber() ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
