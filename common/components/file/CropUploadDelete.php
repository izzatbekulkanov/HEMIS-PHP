<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 10/19/16
 * Time: 11:44 AM
 */

namespace common\components\file;


use trntv\filekit\actions\DeleteAction;
use Yii;

/**
 * Class CropUpload
 * @package common\components
 *
 *
 **/
class CropUploadDelete extends DeleteAction
{
    public function run()
    {
        $path    = \Yii::$app->request->get($this->pathParam);
        $cropDir = \Yii::getAlias("@static") . DS . 'uploads' . DS;

        if (parent::run()) {
            $info  = explode('/', $path);
            $files = glob($cropDir . $info[0] . DS . '*_*_' . $info[1]);
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}