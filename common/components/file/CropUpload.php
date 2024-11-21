<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 10/19/16
 * Time: 11:44 AM
 */

namespace common\components\file;


use Exception;
use Imagine\Image\ManipulatorInterface;
use trntv\filekit\actions\UploadAction;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Class CropUpload
 * @package common\components
 *
 *
 **/
class CropUpload extends UploadAction
{
    /*
    Array
    (
        [files] => Array
        (
            [0] => Array
            (
                [name] => 62cb1393c42d0d7ada74dc1f26e6b455 (1).jpg
                [type] => image/jpeg
                [size] => 5640
                [base_url] => http://static.uzvisit.lc/uploads
                [path] => 1/liTENdhTRlbCAXRmGRlPjoO_BwjTY6HC.jpg
                [url] => http://static.uzvisit.lc/uploads/1/liTENdhTRlbCAXRmGRlPjoO_BwjTY6HC.jpg
                [delete_url] => /file-storage/delete?path=1%2FliTENdhTRlbCAXRmGRlPjoO_BwjTY6HC.jpg
            )
        )
    )*/
    public $deleteRoute = 'dashboard/file-delete';

    protected $cropSizes = [
        'default' => [
            'width' => 480,
            'height' => null,
        ],
        'gallery-image' => [
            'width' => 480,
            'height' => null,
        ],
        'post-image' => [
            'width' => 480,
            'height' => null,
        ],
        'content-image' => [
            'width' => 1100,
            'height' => null,
        ],
    ];

    public function run()
    {
        $uploadedFiles = UploadedFile::getInstancesByName($this->fileparam);
        foreach ($uploadedFiles as $file) {
            $ex = mb_strtolower(pathinfo($file->name)['extension']);
            if (!in_array($ex, ['txt', 'doc', 'docx', 'zip', 'rar', 'gz', 'pdf', 'jpg', 'png', 'jpeg', 'tiff', 'ppt', 'pptx', 'xls', 'xlsx'])) {
                die;
            }
        }


        $result = parent::run();
        if (Yii::$app->request->get('type') != 'ad') {
            if (isset($result['files'])) {
                /*foreach ($result['files'] as &$data) {
                    if ($data['type'] == 'image/jpeg') {
                        $data['org_path'] = $data['path'];
                        $data['path'] = self::getCropImage($data);
                        $data['name'] = crc32($data['path']);
                    }
                }*/

                if (Yii::$app->request->get('type') == 'content-image') {
                    return ['location' => $result['files'][0]['base_url'] . '/' . $result['files'][0]['path']];
                }

            }
        }

        return $result;
    }


    public function getCropImage($img = [], $manipulation = ManipulatorInterface::THUMBNAIL_OUTBOUND, $quality = 90)
    {
        $type = Yii::$app->request->get('type', 'default');
        if (!isset($this->cropSizes[$type])) {
            $type = 'default';
        }

        $width = $this->cropSizes[$type]['width'];
        $height = $this->cropSizes[$type]['height'];

        $dir = \Yii::getAlias("@static") . DS . 'uploads' . DS;
        $cropDir = \Yii::getAlias("@static") . DS . 'uploads' . DS;

        $img = (array)$img;

        if (!empty($img) && is_array($img) && isset($img['path'])) {
            $imagePath = $dir . $img['path'];
            $info = explode('/', $img['path']);
            $cropPath = $info[0] . DS;
            $cropName = $width . '_' . $height . '_' . $info[1];
            $cropFull = $cropDir . $cropPath . $cropName;

            if (!file_exists($cropFull)) {
                if (!is_dir($cropDir . $cropPath)) {
                    FileHelper::createDirectory($cropDir . $cropPath, 0777);
                }

                if (file_exists($imagePath)) {
                    try {
                        InterlacedImage::thumbnail($imagePath, $width, $height, $manipulation)
                            ->save($cropFull, ['quality' => $quality]);
                    } catch (Exception $e) {

                    }

                }
            }

            return $cropPath . $cropName;
        } else {
            return false;
        }
    }
}