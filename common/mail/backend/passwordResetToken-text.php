<?php

/* @var $this yii\web\View */
/* @var $user common\models\Admin */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['dashboard/reset', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->fullname ?>,

Follow the link below to reset your password:

<?= $resetLink ?>
