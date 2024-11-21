<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = __('System Error');
$un = \common\models\structure\EUniversity::findCurrentUniversity();
$params = [
    'title' => 'Error: ' . ($un ? $un->code . '-' . $un->name : ''),
    'subject' => 'Error: ' . ($un ? $un->code . '-' . $un->name : ''),
    'body' => $exception->getMessage() . "\n\n" . $exception->getTraceAsString(),
]
?>
<div class="login-box box-wide">
    <div class="text-center">
        <h1><?= nl2br(Html::encode($exception->getMessage())) ?></h1>

        <p>
            <?= __('The above error occurred while the Web server was processing your request') ?>
        </p>

        <p>
            <?= __('Please contact us if you think this is a server error. Thank you.') ?>
        </p>
        <p>
            <a class="btn btn-primary btn-md" href="<?= linkTo(['/']) ?>"><?= __('Back to Home') ?></a>
            <a class="btn btn-default btn-md"
               href="mailto:dev@hemis.uz?<?= http_build_query($params) ?>"><?= __('Report Error') ?></a>
        </p>
    </div>
</div>

