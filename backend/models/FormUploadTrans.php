<?php

namespace backend\models;

use common\models\system\SystemMessage;
use ErrorException;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class FormUploadTrans extends Model
{
    const ONE_MB = 1048576;
    /**
     * @var UploadedFile
     */
    public $file;


    public function rules()
    {
        return [
            [['file'], 'file',
                'extensions' => ['csv'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 50 * self::ONE_MB,
                'tooBig' => __('The file {file} is too big. Its size cannot exceed 50 Mb.'),
            ],
        ];
    }

    public function uploadData()
    {
        $this->file = UploadedFile::getInstance($this, 'file');

        if ($this->validate()) {
            $cols = false;

            return self::importFromFile($this->file->tempName);
        }
        return false;
    }

    public static function parseTranslations()
    {
        $file = Yii::getAlias('@common/data/translations.csv');
        if (file_exists($file)) {
            return self::importFromFile($file);
        }
    }

    protected static function importFromFile($file)
    {
        $handle = fopen($file, 'r');
        $i = 0;
        $data = [];
        while ($row = fgetcsv($handle)) {

            $i++;
            if ($i == 1) {
                $cols = array_flip($row);
                continue;
            }
            if (count($row) == count($cols)) {
                $attributes = [];

                foreach ($cols as $name => $index) {
                    $attributes[$name] = trim($row[$index]);
                }

                if (!isset($attributes['category']) || !$attributes['category']) {
                    $attributes['category'] = 'app';
                }

                $data[] = $attributes;
            }
        }

        return SystemMessage::addTranslations($data);
    }

}