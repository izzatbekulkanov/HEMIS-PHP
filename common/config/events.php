<?php

use backend\widgets\NotificationDefault;
use yii\base\Event;

Event::on(\yii\web\View::className(), \yii\web\View::EVENT_AFTER_RENDER, function ($event) {
    registerNotifications();
});

Event::on(\yii\widgets\Pjax::className(), \yii\base\Widget::EVENT_BEFORE_RUN, function ($event) {
    registerNotifications();
});

function registerNotifications()
{
    if (Yii::$app->has('session')) {
        $session = \Yii::$app->session;
        $flashes = $session->getAllFlashes();

        foreach ($flashes as $type => $data) {
            $data = (array)$data;
            foreach ($data as $i => $message) {

                NotificationDefault::widget([
                    'type' => $type,
                    'message' => $message,
                    'options' => [
                        "closeButton" => false,
                        "debug" => false,
                        "newestOnTop" => false,
                        "progressBar" => false,
                        "positionClass" => NotificationDefault::POSITION_BOTTOM_RIGHT,
                        "preventDuplicates" => false,
                        "onclick" => null,
                        "showDuration" => "300",
                        "hideDuration" => "1000",
                        "timeOut" => $type == 'error' ? "100000" : "10000",
                        "extendedTimeOut" => "1000",
                        "showEasing" => "swing",
                        "hideEasing" => "linear",
                        "showMethod" => "fadeIn",
                        "hideMethod" => "fadeOut"
                    ]
                ]);
            }
            $session->removeFlash($type);
        }
    }
}

//************************************//

define('EVENT_BEFORE_TOGGLE', 'EVENT_BEFORE_TOGGLE');
define('EVENT_AFTER_TOGGLE', 'EVENT_AFTER_TOGGLE');
Event::on(\backend\controllers\DashboardController::className(), EVENT_BEFORE_TOGGLE, function (\common\components\event\ToggleEvent $event) {

});

Event::on(\backend\controllers\DashboardController::className(), EVENT_AFTER_TOGGLE, function (\common\components\event\ToggleEvent $event) {
    if ($event->attribute == 'is_checked_by_author' && $event->table == 'e_publication_author_meta') {
        if ($model = \common\models\science\EPublicationAuthorMeta::findOne($event->id)) {
            if ($model->publicationMethodical)
                $model->publicationMethodical->setAsShouldBeSynced();

            if ($model->publicationScientific)
                $model->publicationScientific->setAsShouldBeSynced();

            if ($model->publicationProperty)
                $model->publicationProperty->setAsShouldBeSynced();
        }
    }
});

