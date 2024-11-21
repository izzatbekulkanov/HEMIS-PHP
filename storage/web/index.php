<?php
// Composer

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

require(__DIR__ . '/../vendor/autoload.php');


define('DS', DIRECTORY_SEPARATOR);
define('ONE_MB', 1024 * 1024);
ini_set('display_errors', 'on');

class Storage
{
    public $fileParam = 'file';
    public $baseDir = __DIR__ . DS . 'files';
    private $hemisApi = 'http://ministry.hemis.uz/app/rest/';
    private $university;
    private $limit = 20 * ONE_MB;
    private $types = ['pdf', 'txt', 'docx', 'doc', 'zip', 'rar', 'tgz'];


    public function __construct()
    {die;
        $client = new Client(['base_uri' => $this->hemisApi, 'http_errors' => false]);
        $token = @$_POST['token'];
        $request = new Request('GET', 'user/info', [
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json'
        ]);
        $result = $client->send($request);
        $data = json_decode($result->getBody()->getContents(), true);

        if ($result->getStatusCode() == 200) {
            if (isset($data['university'])) {
                $this->university = $data['university'];
                $this->upload();
            }
        } else {
            if (isset($data['error_description'])) {
                $this->result($data['error_description'], [], $result->getStatusCode());
            }
        }
    }

    private function upload()
    {
        $dirs = @$_POST['dir'];
        $meta = @$_POST['meta'];
        if (is_array($dirs) && count($dirs)) {
            array_unshift($dirs, $this->university);

            $dirs = array_filter($dirs, function ($item) {
                return strpos($item, '.') === false;
            });

            $path = strtolower(implode(DS, $dirs));
            $fullPath = $this->baseDir . DS . $path;
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0755, true);
            }

            if (is_dir($fullPath)) {
                if (count($_FILES)) {
                    if ($file = @$_FILES[$this->fileParam]) {
                        if ($file['size'] <= $this->limit) {
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, $this->types)) {
                                $id = md5($path);
                                $fileName = $id . '.' . $ext;
                                if (file_exists($fullPath . DS . $fileName) && md5_file($fullPath . DS . $fileName) == md5_file($file['tmp_name'])) {
                                    if ($meta)
                                        file_put_contents($fullPath . DS . $id . '.json', $meta);

                                    $this->result('File uploaded before', [
                                        'path' => $path . DS . $fileName,
                                        'name' => $fileName,
                                        'id' => $id,
                                    ]);
                                } else {
                                    if (move_uploaded_file($file['tmp_name'], $fullPath . DS . $fileName)) {
                                        if ($meta)
                                            file_put_contents($fullPath . DS . $id . '.json', $meta);

                                        $this->result('File uploaded successfully', [
                                            'path' => $path . DS . $fileName,
                                            'name' => $fileName,
                                            'id' => $id,
                                        ]);
                                    } else {
                                        $this->result('Could not upload the file', [
                                        ], 401);
                                    }
                                }
                            } else {
                                $this->result('This file type is not allowed', [
                                ], 401);
                            }
                        } else {
                            $this->result(sprintf('File size is over limit of [%s MB]', $this->limit / ONE_MB), [
                            ], 401);
                        }
                    } else {
                        $this->result(sprintf('Upload file with field [%s]', $this->fileParam), [
                        ], 401);
                    }
                } else {
                    $this->result(sprintf('Upload file with field [%s]', $this->fileParam), [
                    ], 401);
                }
            } else {
                $this->result(sprintf('Could not create directory [%s]', $path), [
                ], 401);
            }
        } else {
            $this->result(sprintf('Provide directory array'), [
            ], 401);
        }
    }

    private function result($message, $data = [], $status = 200)
    {
        header('Content-Type: application/json', true, $status);
        echo json_encode([
            'message' => $message,
            'result' => $data,
            'status' => $status,
        ]);
    }
}

$app = new Storage();

