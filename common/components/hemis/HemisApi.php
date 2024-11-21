<?php

namespace common\components\hemis;


use common\components\Config;
use common\components\hemis\models\SyncJob;
use common\models\report\BaseReport;
use common\models\report\ReportEmployment;
use common\models\structure\EUniversity;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use common\models\system\classifier\University;
use common\models\system\SystemClassifier;
use Exception;
use yii\base\BaseObject;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\httpclient\Client;
use yii\httpclient\Response;
use yii\queue\Queue;

class HemisApi extends Component
{
    protected $_endpointUrl;
    /**
     * @var SyncModel
     */
    protected $_syncModels;
    protected $_syncModelsMap = [];
    /**
     * @var Client
     */
    protected $_client;
    protected $_token;

    const ERROR_INVALID_TOKEN = 'invalid_token';
    const ERROR_INVALID_GRANT = 'invalid_grant';

    public function init()
    {
        if (HEMIS_INTEGRATION) {
            $this->_client = new Client(['baseUrl' => $this->_endpointUrl]);
            $this->_token = Config::get(Config::CONFIG_SYS_HEMIS_TOKEN);
            foreach ($this->_syncModels as $item) {
                $this->_syncModelsMap[$item['class']] = $item['name'];

                $object = \Yii::createObject(['class' => $item['class']]);
                if (!$object instanceof HemisApiSyncModel) {
                    throw new InvalidConfigException(__('Model class {class} does not implement HemisApiSyncModel', ['class' => $item['class']]));
                }
            }
        }

        parent::init();
    }

    public function setSyncModels($data)
    {
        $this->_syncModels = $data;
    }

    public function getSyncModels()
    {
        return $this->_syncModels;
    }

    public function setEndpointUrl($url)
    {
        $this->_endpointUrl = $url;
    }

    public function getModelTitle($class)
    {
        return isset($this->_syncModelsMap[$class]) ? __($this->_syncModelsMap[$class]) : $class;
    }

    public function getSyncModelsStatus()
    {
        /**
         * @var $object _BaseModel
         */
        $result = [];
        foreach ($this->_syncModels as &$item) {
            $class = $item['class'];
            if (is_subclass_of($class, BaseReport::class)) {
                $item['success'] = $class::find()->where(['_sync' => true])->sum('qty') ?: 0;
                $item['fail'] = $class::find()->where(['_sync' => false])->sum('qty') ?: 0;
            } else {
                $item['success'] = $class::find()->where(['_sync' => true])->count();
                $item['fail'] = $class::find()->where(['_sync' => false])->count();
            }

            $item['actual'] = $class::find()->where(['_sync_status' => HemisApiSyncModel::STATUS_ACTUAL])->count();
            $item['different'] = $class::find()->where(['_sync_status' => HemisApiSyncModel::STATUS_DIFFERENT])->count();
            $item['not_checked'] = $class::find()->where(['_sync_status' => HemisApiSyncModel::STATUS_NOT_CHECKED])->count();
            $item['error'] = $class::find()->where(['_sync_status' => HemisApiSyncModel::STATUS_ERROR])->count();
            $item['not_found'] = $class::find()->where(['_sync_status' => HemisApiSyncModel::STATUS_NOT_FOUND])->count();
            $result[] = new SyncModel($item);
        }

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => ['name', 'count', 'info']
            ]
        ]);
    }


    public function syncAllModelsToApi($class, $onlyFailed = true)
    {
        /**
         * @var $item HemisApiSyncModel
         * @var $class HemisApiSyncModel
         * @var $queue Queue
         */
        $results = 0;

        if (SyncModel::isSyncEnabled($class)) {
            $queue = \Yii::$app->queue;
            if (isset($this->_syncModelsMap[$class])) {

                $attributes = ['id', '_qid', '_sync_status'];

                if ((new $class)->hasAttribute('code'))
                    $attributes[] = 'code';

                if ((new $class)->hasAttribute('classifier'))
                    $attributes[] = 'classifier';

                if ((new $class)->hasAttribute('number'))
                    $attributes[] = 'number';

                $items = $class::find()->select($attributes);

                if ($onlyFailed) {
                    $items->where(['_sync' => false]);
                }

                if ($class == ReportEmployment::class) {
                    $items->limit(1);
                }

                foreach ($items->all() as $item) {
                    $qid = $item->_qid;
                    if ($qid == null || !($queue->isWaiting($qid) || $queue->isReserved($qid))) {
                        if ($qid = $queue->push(new SyncJob([
                            'class' => $class,
                            'id' => $item->getIdForSync(),
                            'limit' => 1,
                        ]))) {
                            $results++;
                            $item->updateAttributes(['_qid' => $qid]);
                        }
                    }
                }
            }
        }

        return $results;
    }


    public function checkAllModelsToApi($class, $status = false)
    {
        /**
         * @var $item HemisApiSyncModel
         * @var $class HemisApiSyncModel
         * @var $queue Queue
         */
        $results = 0;

        if (SyncModel::isSyncEnabled($class)) {
            $queue = \Yii::$app->queue;
            if (isset($this->_syncModelsMap[$class])) {
                $attributes = ['id', '_qid', '_sync_status'];

                //department
                if ((new $class)->hasAttribute('code'))
                    $attributes[] = 'code';

                //classifier
                if ((new $class)->hasAttribute('classifier'))
                    $attributes[] = 'classifier';

                if ((new $class)->hasAttribute('number'))
                    $attributes[] = 'number';

                $items = $class::find()->select($attributes);

                if ($status) {
                    $items->where(['_sync_status' => $status]);
                }

                foreach ($items->all() as $item) {
                    $qid = $item->_qid;
                    if ($qid == null || !($queue->isWaiting($qid) || $queue->isReserved($qid))) {
                        if ($qid = $queue->push(new SyncJob([
                            'class' => $class,
                            'id' => $item->getIdForSync(),
                            'limit' => 1,
                            'check' => true,
                        ]))) {
                            $results++;
                            $item->updateAttributes(['_qid' => $qid]);
                        }
                    }
                }
            }
        }

        return $results;
    }


    protected function syncAllFailedModels()
    {
        /**
         * @var $model HemisApiSyncModel | _BaseModel
         * @var $queue Queue
         */
        $queue = \Yii::$app->queue;
        foreach ($this->_syncModels as $item) {
            $class = $item['class'];
            if (!SyncModel::isSyncEnabled($class)) continue;
            if ($class == SystemClassifier::class) continue;

            $failLimit = 12;
            $models = $class::find()
                ->where(new Expression("
                        _sync=false and id::varchar(32) NOT IN (select max(model_id)
                        from e_system_sync_log
                        where model=:class and delete=false
                        group by concat(model, model_id) having count(*) >:count)",
                    ['class' => $class, 'count' => $failLimit]))
                ->limit(100)
                ->all();

            foreach ($models as $model) {
                $qid = $model->_qid;
                if ($qid == null || !($queue->isWaiting($qid) || $queue->isReserved($qid))) {
                    if ($qid = $queue->push(new SyncJob([
                        'class' => $class,
                        'id' => $model->getIdForSync(),
                        'limit' => 2,
                        'retryAfter' => 30,
                    ]))) {
                        $model->updateAttributes(['_qid' => $qid]);
                    }
                }
            }
        }
    }

    /**
     * @return self
     * @throws \yii\base\InvalidConfigException
     */
    public static function getApiClient()
    {
        return \Yii::$app->get('hemisApi');
    }

    public function getClassifiersInfo()
    {
        if (!SyncModel::isSyncEnabled(SystemClassifier::class)) return;

        $url = 'v2/services/classifiers/info';
        $response = $this->_client
            ->get($url, [], $this->getHeaders())
            ->send();
        /**
         * @var $classifiers SystemClassifier[]
         * @var $queue Queue
         */
        $queue = \Yii::$app->queue;
        $classifiers = SystemClassifier::find()
            ->indexBy('classifier')
            ->all();
        if ($result = new HemisResponseClassifiersInfo($this->processResponse($response))) {
            if ($result->success) {
                foreach ($result->classifiers as $i => $item) {
                    $code = array_keys($item)[0];
                    if (isset($classifiers[$code]) && $item[$code]['version'] != $classifiers[$code]->version) {
                        $classifier = $classifiers[$code];
                        if ($classifier->_qid == null || !($queue->isWaiting($classifier->_qid) || $queue->isReserved($classifier->_qid))) {
                            if ($qid = $queue->delay($i * 5)->push(new SyncJob([
                                'class' => get_class($classifier),
                                'id' => $classifier->getIdForSync(),
                                'limit' => 1,
                            ]))) {
                                echo $code . PHP_EOL;
                                $classifier->updateAttributes(['_qid' => $qid]);
                            }
                        }
                    }
                }
            }
        }
    }

    public function updateClassifier(SystemClassifier $classifier)
    {
        $key = 'CLASSIFIERS_LIST';
        if ($data = \Yii::$app->cache->get($key)) {
            $result = new HemisResponseClassifiers($data);
        }

        if (HemisResponseClassifiers::$CLASSIFIERS_LIST === null) {
            $url = 'v2/services/classifiers/allItems';
            $response = $this->_client
                ->get($url, [], $this->getHeaders())
                ->send();

            if ($result = new HemisResponseClassifiers($this->processResponse($response))) {
                \Yii::$app->cache->set($key, $response->getData(), 1200);
            }
        }

        if (isset(HemisResponseClassifiers::$CLASSIFIERS_LIST[$classifier->classifier])) {
            $data = HemisResponseClassifiers::$CLASSIFIERS_LIST[$classifier->classifier];

            return $classifier->updateClassifierData($data);
        }

        throw new Exception(__('Classifier {classifier} not found in HEMIS API', ['classifier' => $classifier->classifier]));
    }

    public function generateStudentId(EStudent $student, EStudentMeta $meta)
    {
        /**
         * @var $hasStudent EStudent
         */
        $response = $this->_client
            ->post('v2/services/student/id', json_encode([
                'data' => [
                    'citizenship' => $student->citizenship->code,
                    'pinfl' => $student->passport_pin,
                    'serial' => $student->passport_number,
                    'year' => $student->year_of_enter,
                    'education_type' => $meta->educationType->code,
                ],
            ]), $this->getHeaders())
            ->send();

        $result = new HemisResponseGenerateStudentId($this->processResponse($response));
        if ($result->success == false) {
            if ($result->is_active) {
                throw new HemisApiError(__('Talaba {name} ushbu {university}ning {specialty} mutaxassisligiga biriktirilgan.', [
                    'specialty' => $result->student['commonSpecialityName'],
                    'university' => $result->student['university']['name'],
                    'name' => $result->student['fullname'],
                ]), 100);
            }
            throw new HemisApiError($result->message);
        }

        if ($result->unique_id) {
            if ($hasStudent = EStudent::findOne(['_uid' => $result->student['id']])) {
                if ($hasStudent->id != $student->id) {
                    throw new HemisApiError(__('Ushbu passport {serial} bilan boshqa {name} ismli talabaga ID raqam berilgan', ['serial' => $student->passport_number, 'name' => $hasStudent->getFullName()]));
                }
            }

            $student->updateAttributes([
                'student_id_number' => $result->unique_id,
                '_uid' => $result->student['id'],
            ]);
        }


        return $result;
    }

    public function updateStudent(EStudent $student, $delete = false)
    {
        if ($student->meta == null) {
            throw new HemisApiError(__('Student does not have meta'));
        }

        $itemUrl = 'v2/entities/hemishe_EStudent/';

        if ($delete) {
            if ($student->_uid) {
                $response = $this->_client
                    ->delete($itemUrl . $student->_uid, null, $this->getHeaders())
                    ->send();
                if ($response->isOk) {
                    return true;
                } elseif ($response->statusCode == '404') {
                    return true;
                } else {
                    throw new HemisApiError($response->getData()['error']);
                }
            }
            return true;
        } else {

            if ($student->_uid == null || $student->student_id_number == null) {
                if ($result = $this->generateStudentId($student, $student->meta)) {

                }
            }

            $response = $this->_client
                ->put($itemUrl . $student->_uid, json_encode($this->getStudentData($student)), $this->getHeaders())
                ->send();

            if ($response->statusCode == 404) {
                $student->updateAttributes(['_uid' => null, 'student_id_number' => null]);
                if ($result = $this->generateStudentId($student, $student->meta)) {

                }
                $response = $this->_client
                    ->put($itemUrl . $student->_uid, json_encode($this->getStudentData($student)), $this->getHeaders())
                    ->send();
            }

            if ($result = new HemisResponseStudent($this->processResponse($response))) {
                $student->updateAttributes(['_sync_status' => HemisApiSyncModel::STATUS_ACTUAL, '_sync_date' => new \DateTime()]);

                return $result;
            }


            throw new HemisApiError($result->message);
        }
    }

    private function getStudentData(EStudent $student)
    {
        $data = [
            'firstname' => $student->getTranslation('first_name', Config::LANGUAGE_UZBEK),
            'lastname' => $student->getTranslation('second_name', Config::LANGUAGE_UZBEK),
            'fathername' => $student->third_name,
            //'fullname' => $student->getFullName(),
            'serialNumber' => $student->passport_number,
            'pinfl' => $student->passport_pin,

            'birthday' => $student->birth_date instanceof \DateTime ? $student->birth_date->format('Y-m-d') : $student->birth_date,


            'code' => $student->student_id_number,
            'id' => $student->_uid,

            'gender' => [
                'code' => $student->gender->code
            ],

            'educationYear' => [
                'code' => $student->year_of_enter
            ],

            'university' => [
                'code' => EUniversity::findCurrentUniversity()->code
            ],

            'country' => [
                'code' => $student->country ? $student->country->code : null
            ],

            'citizenship' => [
                'code' => $student->citizenship ? $student->citizenship->code : null
            ],

            'address' => $student->home_address,
            'currentAddress' => $student->current_address,

            'nationality' => [
                'code' => $student->nationality->code
            ],
            'accomodation' => [
                'code' => $student->accommodation->code
            ],
            'phone' => $student->phone,
            'responsiblePersonPhone' => $student->person_phone,
            'parentPhone' => $student->parent_phone,
            'email' => $student->email,
            'geoAddress' => $student->geo_location,
            'roommateCount' => $student->roommate_count,
            'roommateType' => $student->studentRoommateType ? [
                'code' => $student->studentRoommateType->code
            ] : null,
            'livingStatus' => $student->studentLivingStatus ? [
                'code' => $student->studentLivingStatus->code
            ] : null,


            'tag' => 'v1'
            // 'studentSuccess' => [],
        ];
        //@todo doctoralStudentType and studentSuccess
        if ($student->district) {
            $data['soato'] = [
                'code' => $student->district->code
            ];
        }
        if ($student->_current_district && $student->currentDistrict) {
            $data['currentSoato'] = [
                'code' => $student->currentDistrict->code
            ];
        }


        if ($student->meta->paymentForm) {
            $data['paymentForm'] = [
                'code' => $student->meta->paymentForm->code
            ];
        }

        if ($student->meta->educationForm) {
            $data['educationForm'] = [
                'code' => $student->meta->educationForm->code
            ];
        }

        if ($student->meta->educationType) {
            $data['educationType'] = [
                'code' => $student->meta->educationType->code
            ];
        }

        if ($student->meta->group) {
            if ($student->meta->group->educationLang) {
                $data['language'] = [
                    'code' => $student->meta->group->educationLang->code
                ];
            }
        }
        if ($student->meta->level) {
            $data['course'] = [
                'code' => $student->meta->level->code
            ];
        }
        /*
                if ($student->meta->specialty && $student->meta->specialty->mainSpecialty) {
                    $data['speciality'] = $student->meta->specialty->mainSpecialty->id;
                }*/

        if ($student->meta->specialty && $student->meta->specialty->bachelorSpecialty) {
            $data['specialityBachelor'] = [
                'id' => $student->meta->specialty->bachelorSpecialty->id
            ];
        }
        if ($student->meta->specialty && $student->meta->specialty->masterSpecialty) {
            $data['specialityMaster'] = [
                'id' => $student->meta->specialty->masterSpecialty->id
            ];
        }

        if ($student->meta->department) {
            $data['faculty'] = [
                'code' => $student->meta->department->code
            ];
        }

        if ($student->socialCategory) {
            $data['socialCategory'] = [
                'code' => $student->socialCategory->code
            ];
        }

        if ($student->meta->studentStatus) {
            $data['studentStatus'] = [
                'code' => $student->meta->studentStatus->code
            ];
        }

        return $data;
    }

    public function checkStudentData(EStudent $student, $update = true)
    {

        $itemUrl = 'v2/entities/hemishe_EStudent/';

        try {
            if ($student->meta == null) {
                throw new HemisApiError(__('Student does not have meta'));
            }
            if ($student->_uid == null || $student->student_id_number == null) {
                if ($result = $this->generateStudentId($student, $student->meta)) {

                }
            }
            $response = $this->_client
                ->get($itemUrl . $student->_uid, ['dynamicAttributes' => true, 'returnNulls' => true, 'view' => 'eStudent-view'], $this->getHeaders())
                ->send();

            if ($response->getIsOk()) {
                $result = $response->getData();
                $data = $this->getStudentData($student);


                if ($result['code'] != $data['code']) {
                    if ($student->updateAttributes(['student_id_number' => $result['code']])) {
                        $data['code'] = $result['code'];
                    }
                }

                $diff = $this->getDiffData($data, $result);

                $student->updateAttributes([
                    '_sync_date' => new \DateTime(),
                    '_sync_diff' => $diff,
                    '_sync_status' => count($diff) ? HemisApiSyncModel::STATUS_DIFFERENT : HemisApiSyncModel::STATUS_ACTUAL,
                ]);

                if (count($diff) && $update) {
                    $this->updateStudent($student);
                    $this->checkStudentData($student, false);
                }

                return $diff;
            } else {
                if ($response->getStatusCode() == 404) {
                    $student->updateAttributes([
                        '_sync_date' => new \DateTime(),
                        '_sync_status' => HemisApiSyncModel::STATUS_NOT_FOUND,
                    ]);
                } else {
                    $student->updateAttributes([
                        '_sync_date' => new \DateTime(),
                        '_sync_diff' => $response->getData(),
                        '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
                    ]);
                }
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            $student->updateAttributes([
                '_sync_diff' => $e->getMessage(),
                '_sync_date' => new \DateTime(),
                '_sync_status' => HemisApiSyncModel::STATUS_ERROR,
            ]);
        }

        return false;
    }

    protected function getDiffData($data, $result)
    {
        $diff = [];
        foreach ($data as $attribute => $value) {
            if (array_key_exists($attribute, $result)) {
                if (is_array($value)) {
                    if (isset($value['id']) && $result[$attribute]['id']) {
                        if ($value['id'] != $result[$attribute]['id']) {
                            $diff[$attribute] = [
                                's' => $value['id'],
                                'a' => $result[$attribute]['id'],
                            ];
                        }
                    } else if (isset($result[$attribute]['code'])) {
                        if ($value['code'] != $result[$attribute]['code']) {
                            $diff[$attribute] = [
                                's' => $value['code'],
                                'a' => $result[$attribute]['code'],
                            ];
                        }
                    } else {
                        if ($value['code'] != $result[$attribute]['id']) {
                            $diff[$attribute] = [
                                's' => $value['code'],
                                'a' => $result[$attribute]['id'],
                            ];
                        }
                    }
                } else {
                    if ($value != $result[$attribute]) {
                        $diff[$attribute] = [
                            's' => $value,
                            'a' => $result[$attribute],
                        ];
                    }
                }
            }
        }

        return $diff;
    }

    public function validateStudentData($pin)
    {
        $response = $this->_client
            ->get('v2/services/student/validate', [
                'data' => $pin,
            ], $this->getHeaders())
            ->send();

        $data = new HemisResponseStudentValidate(
            $this->processResponse($response)
        );

        if ($data->code == 'active' && $data->data['university']['code'] != EUniversity::findCurrentUniversity()->code) {
            throw new HemisApiError(__('Talaba {name} ushbu {university}ning {specialty} mutaxassisligiga biriktirilgan.', [
                'specialty' => $data->data['commonSpecialityName'],
                'university' => $data->data['university']['name'],
                'name' => $data->data['fullname'],
            ]), 100);
        }

        return $data;
    }

    public function getPassportData($serial, $pin)
    {
        $response = $this->_client
            ->get('v2/services/personal-data/getData', [
                'pinfl' => $pin,
                'serial' => $serial,
            ], $this->getHeaders())
            ->send();

        $data = new HemisResponsePassport(
            $this->processResponse($response)
        );

        return $data;
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => "Bearer {$this->_token}",
            'Content-Type' => 'application/json'
        ];
    }

    public function apiLogin($username, $password)
    {
        $response = $this->_client
            ->post('v2/oauth/token', [
                'grant_type' => 'password',
                'username' => $username,
                'password' => $password
            ], [
                'Authorization' => 'Basic Y2xpZW50OnNlY3JldA==',
                'Content-Type' => 'application/json'
            ])
            ->send();

        $data = new HemisResponseToken($this->processResponse($response));

        if ($thisUniversity = EUniversity::findCurrentUniversity()) {

            $response = $this->_client
                ->get($this->_endpointUrl . 'user/info', [], [
                    'Authorization' => "Bearer {$data->access_token}",
                    'Content-Type' => 'application/json'
                ])
                ->send();

            $user = new HemisResponseUserInfo($this->processResponse($response));

            if ($user->university != $thisUniversity->code) {
                throw new HemisApiError(__('University credential is not suitable for the university {name}', ['name' => $thisUniversity->university->name]));
            }
        }

        Config::set(Config::CONFIG_SYS_HEMIS_REFRESH_TOKEN, $data->refresh_token);
        Config::set(Config::CONFIG_SYS_HEMIS_TOKEN, $data->access_token);
        Config::set(Config::CONFIG_SYS_HEMIS_PASSWORD, $password);
        Config::set(Config::CONFIG_SYS_HEMIS_LOGIN, $username);

        return true;
    }

    protected function processResponse(Response $response)
    {
        if ($response->getIsOk()) {
            return $response->getData();
        } else {
            if ($data = $response->getData()) {
                if (isset($data['error'])) {
                    if ($data['error'] == self::ERROR_INVALID_TOKEN) {
                        self::cronLogin();
                    }
                    throw new HemisApiError(isset($data['details']) && $data['details'] ? $data['details'] : $data['error']);
                }

                if (isset($data['error_description'])) {

                    throw new HemisApiError($data['error_description']);
                }
            }
        }

        return false;
    }


    /**
     * =================CRON FUNCTIONS==================
     */
    public static function oneInADay()
    {
        if (HEMIS_INTEGRATION) {
            self::cronLogin();
        }
    }

    public static function oneInAHour()
    {
        if (HEMIS_INTEGRATION) {
            $client = self::getApiClient();
            $client->getClassifiersInfo();
        }
    }

    public static function everyFiveMinute()
    {
        if (HEMIS_INTEGRATION) {
            $client = self::getApiClient();
            $client->syncAllFailedModels();
        }
    }


    protected static function cronLogin()
    {
        if ($login = Config::get(Config::CONFIG_SYS_HEMIS_LOGIN)) {
            if ($password = Config::get(Config::CONFIG_SYS_HEMIS_PASSWORD)) {
                return self::getApiClient()
                    ->apiLogin($login, $password);
            }
        }
    }

}

class HemisApiError extends \Exception
{

}

class HemisResponse extends BaseObject
{
    public $livestatus;
    public $_rawData;
    public $success;
    public $message;


    public function __set($name, $value)
    {
        $this->_rawData[$name] = $value;
    }


}

class HemisReportResponse extends BaseObject
{
    public $_rawData;
    public $code;
    public $success;
    public $data;


    public function __set($name, $value)
    {
        $this->_rawData[$name] = $value;
    }


}

class HemisResponseUserInfo extends HemisResponse
{
    public $id;
    public $login;
    public $name;
    public $university;
    public $locale;
}

class HemisResponseProject extends HemisResponse
{
    public $id;
}

class HemisResponseProjectExecutor extends HemisResponse
{
    public $id;
}

class HemisResponseProjectMeta extends HemisResponse
{
    public $id;
}

class HemisResponseModelId extends HemisResponse
{
    public $id;
}

class HemisResponseToken extends HemisResponse
{
    public $access_token;
    public $token_type;
    public $refresh_token;
    public $expires_in;
    public $scope;
    public $university_code;


    public function init()
    {
        parent::init();
        if (!$this->success) {
            // throw new HemisApiError($this->message);
        }
    }
}

class HemisResponseStudentValidate extends HemisResponse
{
    public $code;
    public $data;
}

class HemisResponsePassport extends HemisResponse
{

    public $code;
    public $birth_date;
    public $document;
    public $sex;
    public $name_latin;
    public $surname_latin;
    public $patronym_latin;
    public $birth_place;
    public $name_engl;
    public $surname_engl;

    public const INCORRECT_DATA = 'incorrect_data';
    public const SERVICE_NOT_AVAILABLE = 'service_not_available';

    public function init()
    {
        parent::init();
        if (!$this->success) {
            if ($this->code == self::SERVICE_NOT_AVAILABLE) {
                throw new \Exception($this->message);
            }
            throw new HemisApiError($this->message);
        }
    }
}

class HemisResponseGenerateStudentId extends HemisResponse
{
    public $unique_id;
    public $student;
    public $is_active;
    public $personal_data;
}

class HemisResponseGenerateEmployeeId extends HemisResponse
{
    public $unique_id;
    public $teacher;
    public $personal_data;
}

class HemisResponseStudent extends HemisResponse
{
    public $id;
    public $pinfl;
    public $birthday;
    public $country;
    public $firstname;
    public $code;

    public $paymentForm;
    public $gender;
    public $educationType;
    public $university;
    public $soato;
    public $language;
    public $socialCategory;
    public $educationYear;
    public $educationForm;
    public $faculty;
    public $speciality;
    public $expelReason;
    public $studentSuccess;
    public $stipendRate;
    public $course;
    public $studentStatus;
    public $serialNumber;
    public $address;
    public $citizenship;
    public $lastname;
    public $fathername;
    public $nationality;
    public $accomodation;
    public $specialityBachelor;
    public $doctoralStudentType;

    public $status;
}

class HemisResponseEmployee extends HemisResponse
{
    public $code;
    public $id;
    public $status;
}

class HemisResponseEmployeePosition extends HemisResponse
{
    public $code;
    public $id;
    public $status;
}

class HemisResponseDiploma extends HemisResponse
{
    public $id;
    public $status;
}

class HemisResponseClassifiersInfo extends HemisResponse
{
    public $classifiers;
    public $hash;

    public function init()
    {
        $this->hash = md5(json_encode($this->classifiers));
        parent::init(); // TODO: Change the autogenerated stub
    }


}

class HemisResponseClassifiers extends HemisResponse
{
    public static $CLASSIFIERS_LIST = null;
    public $classifiers;

    public function init()
    {
        if (self::$CLASSIFIERS_LIST == null) {

            $items = [];
            foreach ($this->classifiers as $classifier) {
                $key = array_key_first($classifier);
                $items[$key] = $classifier[$key];
            }

            self::$CLASSIFIERS_LIST = $items;
        }

        parent::init();
    }
}

class HemisUniversity extends HemisResponse
{
    public $id;
}

class HemisDepartment extends HemisResponse
{
    public $id;
}

class SyncModel extends BaseObject
{
    public $id;
    public $success;
    public $syncCheck;
    public $fail;
    public $name;
    public $info;
    public $class;
    public $actual;
    public $error;
    public $different;
    public $not_checked;
    public $not_found;
    public $enabled;

    public function init()
    {
        $this->enabled = !(boolean)Config::get("disable_sync_model_{$this->class}");
        $this->id = str_replace('\\', '-', $this->class);
        parent::init();
    }

    public function primaryKey()
    {
        return ['id'];
    }

    public static function isSyncEnabled($class)
    {
        return !(boolean)Config::get("disable_sync_model_$class");
    }
}
