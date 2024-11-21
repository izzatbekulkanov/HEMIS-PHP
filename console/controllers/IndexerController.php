<?php

namespace console\controllers;

use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\sync\DiplomaBlankUpdater;
use common\models\archive\EDiplomaBlank;
use common\models\archive\EStudentDiploma;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\report\BaseReport;
use common\models\report\ReportEmployment;
use common\models\structure\EDepartment;
use common\models\structure\EUniversity;
use common\models\student\EStudent;
use common\models\system\Admin;
use common\models\system\AdminResource;
use common\models\system\Contact;
use common\models\system\SystemClassifier;
use DateTime;
use DateTimeZone;
use Yii;
use yii\console\Controller;
use yii\console\widgets\Table;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\VarDumper;
use yii\queue\redis\Queue;

class IndexerController extends Controller
{
    public $force = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'force',
        ]);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'f' => 'force',
        ]);
    }

    /**
     * Publishes permissions to DB
     */
    public function actionResources()
    {
        $this->stdout("Starting publish...\n");
        $updated = AccessResources::parsePermissions($this->force);

        $this->stdout(sprintf("Updated %d permissions\n", $updated), Console::FG_GREY);

        if (!getenv('EXCLUDE_TRANSLATION_REINDEX')) {
            $updated = FormUploadTrans::parseTranslations();

            $this->stdout(sprintf("Updated %d translations\n", $updated), Console::FG_GREY);
        }
    }

    public function actionMin1()
    {
        /**
         * @var $queue Queue
         */
        Config::setCronLog('min1_start', time());
        $queue = Yii::$app->queueFile;
        $queue->run(false);
        Config::setCronLog('min1_end', time());
    }

    public function actionMin5()
    {
        Config::setCronLog('min5_start', time());
        HemisApi::everyFiveMinute();

        /**
         * @var $queue Queue
         */
        $queue = Yii::$app->queueFile;
        $queue->run(false);

        Config::setCronLog('min5_end', time());
    }

    public function actionHour1()
    {
        Config::setCronLog('hour1_start', time());
        HemisApi::oneInAHour();
        Config::setCronLog('hour1_end', time());
    }

    public function actionDay1()
    {
        Config::setCronLog('day1_start', time());
        self::indexSemesters();
        HemisApi::oneInADay();
        $this->actionCleanSystemLog();
        $this->actionContacts();
        Config::setCronLog('day1_end', time());
    }

    public function actionHour6()
    {
        Config::setCronLog('hour6_start', time());
        BaseReport::runAllReports();
        Config::setCronLog('hour6_end', time());
    }

    public function actionCleanSystemLog()
    {
        if ($count = Yii::$app->db->createCommand("delete from e_system_log where created_at < now() - interval '90 days'")->execute()) {
            echo "$count count rows deleted from e_system_log";
        }
    }

    public function actionContacts()
    {
        Contact::indexAdmins();
        Contact::indexStudents();
    }

    /**
     * Joriy yoki eng so'nggi semesterni aniqlaydi
     */
    public static function indexSemesters()
    {
        /**
         * @var $curriculum \common\models\curriculum\ECurriculum
         * @var $semesters [] \common\models\curriculum\Semester
         */

        $today = (new DateTime())
            ->setTimezone(new DateTimeZone("UTC"))
            ->setTime(0, 0, 0)
            ->getTimestamp();


        foreach (\common\models\curriculum\ECurriculum::find()->all() as $curriculum) {
            $semesters = Semester::find()
                ->where(['_curriculum' => $curriculum->id])
                ->orderBy(['position' => SORT_DESC])
                ->all();

            $last = null;

            foreach ($semesters as $i => $semester) {
                $start = $semester->start_date->getTimestamp();
                $end = $semester->end_date->getTimestamp();
                if ($start <= $today && $today <= $end) {
                    $last = $semester;
                }
                if ($today >= $start && $today >= $end) {
                    if ($last == null) {
                        $last = $semester;
                    }
                }
            }

            if ($last) {
                if (!$last->last) {
                    echo Semester::updateAll(['last' => false], ['_curriculum' => $curriculum->id]);
                    echo $last->updateAttributes(['last' => true]);
                }
            }
        }
    }

    public function actionClassifiers()
    {
        $migration = Yii::createObject([
            'class' => Migration::class,
            'db' => Yii::$app->db,
            'compact' => false,
        ]);

        SystemClassifier::createClassifiersTables($migration);
    }

    public function actionDiplomaBlank()
    {
        EDiplomaBlank::deleteAll(['_uid' => null]);
        $result = DiplomaBlankUpdater::importModels();
        echo $result;
        echo "\n";
    }

    public function actionChangePassword($login, $password = false)
    {
        if ($admin = Admin::findOne(['login' => $login])) {
            $password = $password ? $password : substr(md5(time() . Yii::$app->params['system.encryptKey']), 0, 16);
            $admin->setPassword($password);
            if ($admin->save(false)) {
                echo "Admin $login password changed to: $password\n";
            }
        }
    }

    public function actionSync($type = 'position')
    {
        $api = HemisApi::getApiClient();
        if ($type == 'position') {
            echo $api->syncAllModelsToApi(EEmployeeMeta::class, false);
        } elseif ($type == 'employee') {
            echo $api->syncAllModelsToApi(EEmployee::class, false);
        } elseif ($type == 'student') {
            echo $api->syncAllModelsToApi(EStudent::class, false);
        } elseif ($type == 'university') {
            echo $api->syncAllModelsToApi(EUniversity::class, false);
        } elseif ($type == 'department') {
            echo $api->syncAllModelsToApi(EDepartment::class, false);
        } elseif ($type == 'diploma') {
            echo $api->syncAllModelsToApi(EStudentDiploma::class, false);
        } else {
            echo "Unknown type, use one of following types:\nposition\nemployee\nstudent\ndiploma\ndepartment\nuniversity\n";
        }
        echo "\n";
    }

    public function actionRunReports()
    {
        ReportEmployment::runReport(false);
    }
}
