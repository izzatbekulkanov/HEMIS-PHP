<?php

namespace console\controllers;

use common\components\AccessResources;
use common\components\hemis\HemisApi;
use common\components\hemis\sync\DiplomaBlankUpdater;
use common\models\archive\EDiplomaBlank;
use common\models\curriculum\ECurriculumWeek;
use common\models\Permission;
use common\models\Role;
use common\models\system\AdminResource;
use Sabre\DAV\Client;
use yii\console\Controller;
use yii\console\widgets\Table;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\VarDumper;
use yii\mongodb\Query;

class TestController extends Controller
{
    public function actionDate($id)
    {
        $date = ECurriculumWeek::getDateByCurriculumWeekPeriod($id, 2);
        print_r($date->format('Y-m-d'));
    }

    public function actionCloud()
    {
        $settings = array(
            'baseUri' => 'https://cloud.hemis.uz:1443/remote.php/dav',
            'userName' => 'hemis',
            'password' => 'hemis2021#'
        );

        $client = new Client($settings);

        $upload_result = $client->request('MKCOL', 'files/12332323');
        print_r($upload_result);
        // Upload a file
        $upload_result = $client->request('PUT', 'files/12332323/README.md', 'This will be written to the txt file');
        print_r($upload_result);

        // List a folder's contents
        $folder_content = $client->propFind(
            'files',
            [
                '{DAV:}getlastmodified',
                '{DAV:}getcontenttype',
            ],
            1
        );
        //  print_r($folder_content);
    }

    public function actionClassifiers()
    {
        $cf = fopen(\Yii::getAlias('@runtime/classifiers.csv'), 'r');
        $of = fopen(\Yii::getAlias('@runtime/options.csv'), 'r');

        $c = [];
        $i = 0;
        $cCols = [];
        while ($row = fgetcsv($cf)) {
            if ($i == 0) {
                $cCols = array_flip($row);
            } else {
                $item = [
                    'options' => [],
                ];
                foreach ($cCols as $col => $index) {
                    $item[$col] = trim($row[$index]);
                }
                $c[$item['code']] = $item;
            }
            $i++;
        }

        $i = 0;
        while ($row = fgetcsv($of)) {
            if ($i == 0) {
                $cCols = array_flip($row);
            } else {
                $item = [];
                foreach ($cCols as $col => $index) {
                    $item[$col] = trim($row[$index]);
                }
                $classifier = $item['classifier'];
                if ($classifier == 'h_bachelor_speciality' && strlen($item['code']) != 7) {
                    print_r($item);
                }
                unset($item['classifier']);
                $c[$classifier]['options'][] = $item;
            }
            $i++;
        }

        file_put_contents(
            \Yii::getAlias('@runtime/classifiers.json'),
            json_encode($c, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}
