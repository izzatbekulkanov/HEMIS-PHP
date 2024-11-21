<?php

namespace backend\models;

use common\models\student\EStudent;
use Yii;
use yii\base\Model;
use ErrorException;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use common\models\system\SystemMessage;

class FormUploadUzasbo extends Model
{
    const ONE_MB = 1048576;
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file',
                'skipOnEmpty' => false,
                'extensions' => ['xls', 'xlsx'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 50 * self::ONE_MB,
                'tooBig' => __('The file {file} is too big. Its size cannot exceed 50 Mb.'),
            ],
        ];
    }

    public function uploadData()
    {
        $this->file = UploadedFile::getInstance($this, 'file');
        $success = 0;

        if ($this->validate()) {
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($this->file->tempName);
            $excelReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType);
            $phpexcel = $excelReader->load($this->file->tempName)->getsheet(0);
            $total_line = $phpexcel->gethighestrow();
            $data = [];

            if ($total_line > 1) {
                for ($row = 2; $row <= $total_line; $row++) {
                    $data[trim($phpexcel->getCell('B' . $row))] = trim($phpexcel->getCell('A' . $row));
                }
            }

            $students = EStudent::find()
                ->select(['id', 'passport_pin'])
                ->where([
                    'passport_pin' => array_keys($data)
                ])
                ->all();

            if (count($students)) {
                $transaction = Yii::$app->db->beginTransaction();
                foreach ($students as $student) {
                    $student->updateAttributes(['uzasbo_id_number' => $data[$student->passport_pin]]);
                }
                $transaction->commit();

                $success = count($students);
            }
        }

        return $success;
    }

}

